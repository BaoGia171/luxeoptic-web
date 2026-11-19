"""TV2 HTTP regression checks on a disposable MySQL instance (standard library only).

Usage: python scripts/test-tv2.py --php /path/php --mysqld /path/mysqld
Never connects to the project's existing database. Keeps artifacts in a temp folder.
"""
import argparse
import http.cookiejar
import json
import os
from pathlib import Path
import re
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import secrets


def free_port():
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        return sock.getsockname()[1]


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


class Client:
    def __init__(self, base):
        self.base = base
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(self.jar), NoRedirect())

    def request(self, path, data=None):
        encoded = None if data is None else urllib.parse.urlencode(data).encode()
        try:
            response = self.opener.open(self.base + path, encoded, timeout=15)
        except urllib.error.HTTPError as error:
            response = error
        return response.code, response.headers, response.read().decode('utf-8')

    def token(self, path='/auth.php'):
        status, _, body = self.request(path)
        assert status == 200, (path, status)
        return re.search(r'name="csrf_token"\s+value="([a-f0-9]+)"', body)[1]

    def sid(self):
        return next((cookie.value for cookie in self.jar if cookie.name == 'PHPSESSID'), '')


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', required=True)
    parser.add_argument('--mysqld', required=True)
    parser.add_argument('--playwright', help='Optional path to installed playwright Node package')
    parser.add_argument('--browser', help='Chrome/Edge executable for optional responsive checks')
    parser.add_argument('--ui-assets', help='Optional shared UI checkout; copies only four pending theme/effects assets into test copy')
    args = parser.parse_args()
    if bool(args.playwright) != bool(args.browser):
        parser.error('--playwright and --browser must be provided together')
    root = Path(__file__).resolve().parents[1]
    temp = Path(tempfile.mkdtemp(prefix='luxeoptic-tv2-test-'))
    session_dir = temp / 'sessions'
    session_dir.mkdir()
    app = temp / 'app'
    shutil.copytree(root, app, ignore=shutil.ignore_patterns('.git', 'database.php', '__pycache__'))
    if args.ui_assets:
        for relative in ['assets/css/theme.css', 'assets/css/ui-effects.css',
                         'assets/js/theme.js', 'assets/js/ui-effects.js']:
            shutil.copy2(Path(args.ui_assets) / relative, app / relative)
    mysql_port, web_port = free_port(), free_port()
    while mysql_port == web_port:
        web_port = free_port()
    flags = subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0
    php = str(Path(args.php).resolve())
    mysqld = str(Path(args.mysqld).resolve())
    mysql_base = str(Path(mysqld).parent.parent)
    version = subprocess.check_output([mysqld, '--version'], text=True, creationflags=flags)
    is_mariadb = 'MariaDB' in version
    processes, handles, checks = [], [], []
    report = {'date': time.strftime('%Y-%m-%d'), 'checks': checks,
              'scope': 'HTTP + database regression; no claim to reproduce original 95 tests'}

    def check(name, condition):
        checks.append({'name': name, 'result': 'PASS' if condition else 'FAIL'})
        print(checks[-1]['result'] + ': ' + name, flush=True)

    def run_php(code):
        result = subprocess.run([php, '-r', code], capture_output=True, text=True,
                                encoding='utf-8', creationflags=flags)
        if result.returncode:
            raise RuntimeError('PHP helper failed; see private test environment')
        return result.stdout

    config = ("<?php return ['host'=>'127.0.0.1','port'=>%d,'database'=>'luxeoptic_tv2_test',"
              "'username'=>'root','password'=>'','base_url'=>''];" % mysql_port)
    (app / 'config/database.php').write_text(config, encoding='utf-8')
    sql_helper = temp / 'sql.php'
    sql_helper.write_text("""<?php
    $input=json_decode(stream_get_contents(STDIN),true);
    $pdo=new PDO('mysql:host=127.0.0.1;port='+PORT+';charset=utf8mb4','root','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    if ($input['init']??false) { $pdo->exec($input['sql']); echo '[]'; }
    else { $pdo->exec('USE luxeoptic_tv2_test'); $s=$pdo->prepare($input['sql']);
        $s->execute($input['params']??[]); echo json_encode($s->columnCount()?$s->fetchAll():[]); }
    """.replace("'+PORT+'", str(mysql_port)), encoding='utf-8')

    def sql(query, params=None, init=False):
        proc = subprocess.run([php, str(sql_helper)], input=json.dumps(
            {'sql': query, 'params': params or [], 'init': init}),
            capture_output=True, text=True, encoding='utf-8', creationflags=flags)
        if proc.returncode:
            raise RuntimeError('SQL fixture failed (private output omitted)')
        return json.loads(proc.stdout)

    # This fixture endpoint exists ONLY in the copied app, never in repository source.
    fixture = app / '_test-session.php'
    fixture.write_text("""<?php
    require __DIR__.'/includes/bootstrap.php';
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        $_SESSION['cart']=[987=>2]; $_SESSION['voucher_code']='LUXE10';
    }
    header('Content-Type: application/json');
    echo json_encode(['cart'=>$_SESSION['cart']??[], 'voucher'=>$_SESSION['voucher_code']??null,
        'user'=>$_SESSION['user_id']??null]);
    """, encoding='utf-8')
    try:
        init_log = open(temp / 'mysql-init.log', 'w', encoding='utf-8')
        handles.append(init_log)
        if is_mariadb:
            # Create an isolated data directory without installing a Windows service.
            installer = Path(mysql_base) / 'bin/mysql_install_db.exe'
            init_command = [str(installer), '--datadir=' + str(temp / 'mysql-data'),
                            '--port=' + str(mysql_port)]
        else:
            init_command = [mysqld, '--no-defaults', '--initialize-insecure',
                            '--basedir=' + mysql_base, '--datadir=' + str(temp / 'mysql-data')]
        subprocess.run(init_command, stdout=init_log, stderr=init_log,
                       check=True, creationflags=flags)
        log = open(temp / 'mysql.log', 'w', encoding='utf-8'); handles.append(log)
        processes.append(subprocess.Popen([mysqld, '--no-defaults', '--basedir=' + mysql_base,
            '--datadir=' + str(temp / 'mysql-data'), '--bind-address=127.0.0.1',
            '--port=' + str(mysql_port)] + ([] if is_mariadb else ['--mysqlx=0']), stdout=log, stderr=log, creationflags=flags))
        for attempt in range(60):
            try:
                with socket.create_connection(('127.0.0.1', mysql_port), timeout=1):
                    break
            except OSError:
                time.sleep(.5)
        schema = (app / 'database/schema.sql').read_text(encoding='utf-8-sig')
        sql(schema.replace('luxeoptic', 'luxeoptic_tv2_test'), init=True)
        report['php'] = run_php('echo PHP_VERSION;').strip()
        report['mysql'] = sql('SELECT VERSION() AS v')[0]['v']
        log = open(temp / 'php.log', 'w', encoding='utf-8'); handles.append(log)
        processes.append(subprocess.Popen([php, '-d', 'session.save_path=' + str(session_dir), '-S', '127.0.0.1:' + str(web_port), '-t', str(app)],
            stdout=log, stderr=log, creationflags=flags))
        base = 'http://127.0.0.1:' + str(web_port)
        guest = Client(base)
        for attempt in range(40):
            try:
                guest.request('/auth.php'); break
            except OSError:
                time.sleep(.25)
        password = 'Test-' + secrets.token_hex(8)
        payload = dict(full_name='Khach Thu Nghiem', email='customer@example.test',
            phone='0912 345 678', password=password, password_confirm=password,
            agree_terms='1', role='admin', status='blocked', csrf_token=guest.token())
        status, _, _ = guest.request('/actions/register.php', payload)
        rows = sql('SELECT * FROM users WHERE email=?', [payload['email']])
        check('register valid and ignore injected role/status', status == 303 and len(rows)==1
              and rows[0]['role']=='customer' and rows[0]['status']=='active')
        customer_id = rows[0]['id']
        check('phone normalized and password stored hashed', rows[0]['phone']=='0912345678'
              and rows[0]['password_hash'] != password and rows[0]['password_hash'].startswith('$2y$'))
        for name, changes in [('duplicate email', {'phone':'0900000001'}),
                              ('duplicate normalized phone', {'email':'other@example.test','phone':'+84912345678'}),
                              ('invalid email', {'email':'invalid','phone':'0900000002'}),
                              ('short password', {'email':'new@example.test','phone':'0900000002','password':'short'}),
                              ('mismatched confirmation', {'email':'new@example.test','phone':'0900000002','password_confirm':'wrong'})]:
            guest.request('/actions/register.php', payload | changes | {'csrf_token':guest.token()})
            check('register rejects '+name, sql('SELECT COUNT(*) AS n FROM users')[0]['n']==1)
        check('register requires CSRF', guest.request('/actions/register.php', payload | {'csrf_token':'bad'})[0]==403)
        check('register rejects GET', guest.request('/actions/register.php')[0]==405)
        status, headers, _ = guest.request('/profile.php')
        check('guest cannot read profile', status==302 and headers.get('Location')=='/auth.php')
        check('guest cannot read admin users', guest.request('/admin/users.php')[0]==302)
        status, _, _ = guest.request('/actions/login.php', {'identifier':payload['email'],
            'password':'wrong','csrf_token':guest.token()})
        check('incorrect password does not authenticate', status==303 and guest.request('/profile.php')[0]==302)
        guest.request('/_test-session.php', {})
        old_sid = guest.sid()
        status, headers, _ = guest.request('/actions/login.php', {'identifier':payload['email'],
            'password':password,'csrf_token':guest.token(),'return':'https://example.org'})
        check('customer login rejects external return', status==303 and headers.get('Location')=='/index.php')
        check('login regenerates session ID', old_sid != guest.sid())
        check('customer can read own profile', guest.request('/profile.php')[0]==200)
        check('customer cannot read admin users', guest.request('/admin/users.php')[0]==403)
        check('customer cannot POST admin user save', guest.request('/admin/actions/user-save.php',
            {'csrf_token':guest.token('/profile.php')})[0]==403)
        status, _, _ = guest.request('/actions/profile-update.php', {'full_name':'Ten Da Sua',
            'phone':'0900000003','id':'999','email':'hacked@example.test','role':'admin',
            'csrf_token':guest.token('/profile.php')})
        row = sql('SELECT * FROM users WHERE id=?',[customer_id])[0]
        check('profile updates own allowed fields only', status==303 and row['full_name']=='Ten Da Sua'
              and row['phone']=='0900000003' and row['email']==payload['email'] and row['role']=='customer')
        check('profile update requires CSRF', guest.request('/actions/profile-update.php',
            {'full_name':'No','phone':'0900000004'})[0]==403)
        token=guest.token('/profile.php'); old_sid=guest.sid()
        check('logout rejects GET', guest.request('/actions/logout.php')[0]==405)
        check('logout rejects wrong CSRF', guest.request('/actions/logout.php',{'csrf_token':'wrong'})[0]==403)
        status, headers, _ = guest.request('/actions/logout.php', {'csrf_token':token})
        state=json.loads(guest.request('/_test-session.php')[2])
        check('logout returns home and removes authentication', status==303 and headers.get('Location')=='/index.php' and state['user'] is None)
        check('logout regenerates session and preserves cart/voucher', old_sid!=guest.sid()
              and state['cart']=={'987':2} and state['voucher']=='LUXE10')
        check('profile protected after logout', guest.request('/profile.php')[0]==302)
        status, headers, _=guest.request('/actions/login.php',{'identifier':'+84900000003',
            'password':password,'return':'cart','csrf_token':guest.token()})
        check('phone login returns to cart',status==303 and headers.get('Location')=='/cart.php')

        # Fixture Admin uses a copied password hash, not a committed credential.
        sql("INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES (?,?,?,?,?,?)",
            ['Admin Test','admin@example.test','0900000005',row['password_hash'],'admin','active'])
        admin_id=sql("SELECT id FROM users WHERE email='admin@example.test'")[0]['id']
        admin=Client(base)
        status, headers, _=admin.request('/actions/login.php',{'identifier':'admin@example.test',
            'password':password,'csrf_token':admin.token()})
        check('admin login reaches users',status==303 and headers.get('Location')=='/admin/users.php')
        check('admin can read own profile', admin.request('/profile.php')[0]==200)
        token=admin.token('/admin/users.php')
        admin.request('/admin/actions/user-status.php',{'id':admin_id,'status':'blocked','csrf_token':token})
        check('cannot block self',sql('SELECT status FROM users WHERE id=?',[admin_id])[0]['status']=='active')
        admin.request('/admin/actions/user-save.php',{'id':admin_id,'full_name':'Admin Test',
            'email':'admin@example.test','phone':'0900000005','role':'customer','status':'active','csrf_token':token})
        check('cannot demote last active admin',sql('SELECT role FROM users WHERE id=?',[admin_id])[0]['role']=='admin')
        new_user={'full_name':'New User','email':'new@example.test','phone':'0900000006',
            'password':password,'role':'customer','status':'active','csrf_token':token}
        admin.request('/admin/actions/user-save.php',new_user)
        new=sql("SELECT * FROM users WHERE email='new@example.test'")
        check('admin creates customer',len(new)==1 and new[0]['role']=='customer')
        admin.request('/admin/actions/user-save.php',new_user | {'email':'badrole@example.test','phone':'0900000007','role':'superadmin'})
        check('admin save rejects unsupported role',not sql("SELECT id FROM users WHERE email='badrole@example.test'"))
        admin.request('/admin/actions/user-status.php',{'id':customer_id,'status':'blocked','csrf_token':token})
        check('admin blocks customer',sql('SELECT status FROM users WHERE id=?',[customer_id])[0]['status']=='blocked')
        check('blocked existing session loses access',guest.request('/profile.php')[0]==302)
        guest.request('/actions/login.php',{'identifier':payload['email'],'password':password,'csrf_token':guest.token()})
        check('blocked customer cannot login',guest.request('/profile.php')[0]==302)
        admin.request('/admin/actions/user-status.php',{'id':customer_id,'status':'active','csrf_token':token})
        check('admin unblocks customer',sql('SELECT status FROM users WHERE id=?',[customer_id])[0]['status']=='active')
        admin.request('/actions/profile-update.php',{'full_name':'Admin Updated','phone':'0900000005',
            'csrf_token':admin.token('/profile.php')})
        check('admin can update own profile',sql('SELECT full_name FROM users WHERE id=?',[admin_id])[0]['full_name']=='Admin Updated')
        status,_,body=admin.request('/admin/users.php?q=new%40example.test&role=customer&status=active')
        check('admin search/filter returns matching account',status==200 and 'new@example.test' in body and 'customer@example.test' not in body)
        check('create-admin CLI endpoint blocked on web',guest.request('/scripts/create-admin.php')[0]==403)
        if args.playwright and args.browser:
            env = os.environ | {'TV2_BASE_URL':base, 'TV2_TEST_PASSWORD':password,
                'TV2_PLAYWRIGHT':str(Path(args.playwright).resolve()),
                'TV2_BROWSER':str(Path(args.browser).resolve()), 'TV2_ARTIFACTS':str(temp)}
            subprocess.run(['node', str(root/'scripts/test-tv2-ui.cjs')], env=env,
                           check=True, creationflags=flags)
            checks.extend(json.loads((temp/'ui-results.json').read_text(encoding='utf-8')))
            report['ui_assets'] = 'four shared theme/effects files from separate UI checkout' if args.ui_assets else 'branch assets only'
        report['passed']=sum(c['result']=='PASS' for c in checks)
        report['total']=len(checks)
    finally:
        # mysqld may leave a child process on Windows; shut down this isolated port first.
        admin_cli = Path(mysql_base) / 'bin/mysqladmin.exe'
        if admin_cli.exists():
            try:
                subprocess.run([str(admin_cli), '--no-defaults', '--host=127.0.0.1',
                                '--port=' + str(mysql_port), '--user=root', 'shutdown'],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
                               timeout=15, creationflags=flags)
            except subprocess.TimeoutExpired:
                pass
        for process in reversed(processes):
            process.terminate()
            try: process.wait(timeout=15)
            except subprocess.TimeoutExpired:
                process.kill(); process.wait()
        for handle in handles: handle.close()
        (temp/'results.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
        print('Artifacts: '+str(temp),flush=True)
    return 0 if checks and all(c['result']=='PASS' for c in checks) else 1


if __name__=='__main__':
    raise SystemExit(main())
