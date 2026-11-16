"""TV3-TV6 integration regression checks on a disposable MySQL instance (standard library only).

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
    temp = Path(tempfile.mkdtemp(prefix='luxeoptic-commerce-test-'))
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
    processes, handles, checks = [], [], []
    report = {'date': time.strftime('%Y-%m-%d'), 'checks': checks,
              'scope': 'Catalog, admin products/uploads, checkout and order state regression'}

    def check(name, condition):
        checks.append({'name': name, 'result': 'PASS' if condition else 'FAIL'})
        print(checks[-1]['result'] + ': ' + name, flush=True)

    def run_php(code):
        result = subprocess.run([php, '-r', code], capture_output=True, text=True,
                                encoding='utf-8', creationflags=flags)
        if result.returncode:
            raise RuntimeError('PHP helper failed; see private test environment')
        return result.stdout

    config = ("<?php return ['host'=>'127.0.0.1','port'=>%d,'database'=>'luxeoptic_commerce_test',"
              "'username'=>'root','password'=>'','base_url'=>''];" % mysql_port)
    (app / 'config/database.php').write_text(config, encoding='utf-8')
    sql_helper = temp / 'sql.php'
    sql_helper.write_text("""<?php
    $input=json_decode(stream_get_contents(STDIN),true);
    $pdo=new PDO('mysql:host=127.0.0.1;port='+PORT+';charset=utf8mb4','root','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    if ($input['init']??false) { $pdo->exec($input['sql']); echo '[]'; }
    else { $pdo->exec('USE luxeoptic_commerce_test'); $s=$pdo->prepare($input['sql']);
        $s->execute($input['params']??[]); echo json_encode($s->columnCount()?$s->fetchAll():[]); }
    """.replace("'+PORT+'", str(mysql_port)), encoding='utf-8')

    def sql(query, params=None, init=False):
        proc = subprocess.run([php, str(sql_helper)], input=json.dumps(
            {'sql': query, 'params': params or [], 'init': init}),
            capture_output=True, text=True, encoding='utf-8', creationflags=flags)
        if proc.returncode:
            raise RuntimeError('SQL fixture failed (private output omitted)')
        return json.loads(proc.stdout)

    try:
        init_log = open(temp / 'mysql-init.log', 'w', encoding='utf-8')
        handles.append(init_log)
        subprocess.run([mysqld, '--no-defaults', '--initialize-insecure',
                        '--basedir=' + mysql_base, '--datadir=' + str(temp / 'mysql-data')],
                       stdout=init_log, stderr=init_log, check=True, creationflags=flags)
        log = open(temp / 'mysql.log', 'w', encoding='utf-8'); handles.append(log)
        processes.append(subprocess.Popen([mysqld, '--no-defaults', '--basedir=' + mysql_base,
            '--datadir=' + str(temp / 'mysql-data'), '--bind-address=127.0.0.1',
            '--port=' + str(mysql_port), '--mysqlx=0'], stdout=log, stderr=log, creationflags=flags))
        for attempt in range(60):
            try:
                with socket.create_connection(('127.0.0.1', mysql_port), timeout=1):
                    break
            except OSError:
                time.sleep(.5)
        schema = (app / 'database/schema.sql').read_text(encoding='utf-8-sig')
        sql(schema.replace('luxeoptic', 'luxeoptic_commerce_test'), init=True)
        report['php'] = run_php('echo PHP_VERSION;').strip()
        report['mysql'] = sql('SELECT VERSION() AS v')[0]['v']
        log = open(temp / 'php.log', 'w', encoding='utf-8'); handles.append(log)
        processes.append(subprocess.Popen([php, '-S', '127.0.0.1:' + str(web_port), '-t', str(app)],
            stdout=log, stderr=log, creationflags=flags))
        base = 'http://127.0.0.1:' + str(web_port)
        guest = Client(base)
        for attempt in range(40):
            try:
                guest.request('/auth.php'); break
            except OSError:
                time.sleep(.25)
        password = 'Test-' + secrets.token_hex(8)

        sql((app/'database/seed.sql').read_text(encoding='utf-8-sig'),init=False)
        hash_value=run_php("echo password_hash('"+password+"',PASSWORD_DEFAULT);")
        for i,role in [(1,'admin'),(2,'customer'),(3,'customer')]:
            sql('INSERT INTO users(full_name,email,phone,password_hash,role) VALUES(?,?,?,?,?)',
                ['Test '+role+str(i),role+str(i)+'@example.test','090000000'+str(i),hash_value,role])
        admin,customer,other=Client(base),Client(base),Client(base)
        def login(client,email):
            return client.request('/actions/login.php',{'identifier':email,'password':password,'csrf_token':client.token()})
        login(admin,'admin1@example.test');login(customer,'customer2@example.test');login(other,'customer3@example.test')
        def post(client,path,data,token_path='/profile.php'):
            return client.request(path,dict(data,csrf_token=client.token(token_path)))
        # Catalog reads the shared DB, with server-side filtering and stable pagination.
        status,_,body=guest.request('/products.php')
        check('catalog displays 12 of 14 seeded products',status==200 and body.count('class="product-card"')==12)
        check('second catalog page has 2 products',guest.request('/products.php?page=2')[2].count('class="product-card"')==2)
        check('out-of-range page clamps to final',guest.request('/products.php?page=999')[2].count('class="product-card"')==2)
        check('invalid price range is rejected', 'Giá tối thiểu' in guest.request('/products.php?min_price=900&max_price=1')[2])
        for slug in ['kinh-ram','gong-kinh-can','chong-anh-sang-xanh','phien-ban-gioi-han']:
            body=guest.request('/products.php?category='+slug)[2]
            expected=sql('SELECT p.name FROM products p JOIN categories c ON p.category_id=c.id WHERE c.slug=?',[slug])
            check('category '+slug,all(row['name'] in body for row in expected))
        sample=sql('SELECT * FROM products WHERE stock>0 ORDER BY id LIMIT 1')[0]; pid=sample['id']
        check('detail name/price matches selected product',sample['name'] in guest.request('/product-detail.php?id='+str(pid))[2])
        check('missing product is 404',guest.request('/product-detail.php?id=999999')[0]==404)
        info=json.loads(guest.request('/actions/product-info.php?id='+str(pid))[2])
        check('chatbot reads current DB values', info=={'id':pid,'price':sample['price'],'stock':sample['stock']})
        sql('UPDATE products SET is_active=0 WHERE id=?',[pid])
        check('hidden product denied in detail and chatbot',guest.request('/product-detail.php?id='+str(pid))[0]==404 and guest.request('/actions/product-info.php?id='+str(pid))[0]==404)
        sql('UPDATE products SET is_active=1 WHERE id=?',[pid])
        check('comparison data uses DB',sample['name'] in guest.request('/compare.php?id1='+str(pid)+'&id2=2')[2])
        home=guest.request('/index.php')[2]
        featured=sql('SELECT p.id,p.name,p.price FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 AND c.is_active=1 AND p.is_featured=1 ORDER BY p.created_at DESC,p.id DESC LIMIT 4')
        check('homepage has only DB featured cards and no stale sample product links',home.count('class="product-card"')==len(featured) and 'products.php?id=' not in home and '48 Mẫu' not in home and 'category=unisex' not in home)
        ids=set(re.findall(r'product-detail.php\?id=(\d+)',home))
        check('all homepage product links resolve to visible detail',bool(ids) and all(guest.request('/product-detail.php?id='+i)[0]==200 for i in ids))
        sql('UPDATE products SET name=?,price=? WHERE id=?',['HOME_SYNC_TEST',987654,featured[0]['id']])
        changed=guest.request('/index.php')[2]
        check('homepage name and price follow database changes','HOME_SYNC_TEST' in changed and '987.654' in changed)
        # Restore seed values to keep the downstream fixture deterministic.
        sql('UPDATE products SET name=?,price=? WHERE id=?',[featured[0]['name'],featured[0]['price'],featured[0]['id']])
        # Permissions on all TV5/TV6 pages/actions, independent of UI visibility.
        for path in ['/admin/products.php','/admin/categories.php','/admin/orders.php']:
            check('customer denied '+path,customer.request(path)[0]==403)
        for path in ['/admin/actions/product-save.php','/admin/actions/product-delete.php','/admin/actions/product-image-delete.php','/admin/actions/category-save.php','/admin/actions/category-delete.php','/admin/actions/order-status.php']:
            check('GET denied '+path,guest.request(path)[0]==405)
            check('customer POST denied '+path,post(customer,path,{})[0]==403)
            check('CSRF denied '+path,admin.request(path,{})[0]==403)
        catdata={'name':'Test category','slug':'test-category','description':'Test','sort_order':'9','is_active':'1'}
        post(admin,'/admin/actions/category-save.php',catdata)
        catid=sql('SELECT id FROM categories WHERE slug=?',['test-category'])[0]['id']
        check('category create persists',catid>0)
        post(admin,'/admin/actions/category-save.php',catdata)
        check('duplicate category slug rejected',sql('SELECT COUNT(*) n FROM categories WHERE slug=?',['test-category'])[0]['n']==1)
        post(admin,'/admin/actions/category-delete.php',{'id':1})
        check('cannot delete nonempty category',bool(sql('SELECT id FROM categories WHERE id=1')))
        # Real multipart HTTP uploads.
        import base64
        png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII=')
        def multipart(data,files):
            boundary='Boundary'+secrets.token_hex(12);chunks=[]
            for k,v in data.items():chunks.append((f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n').encode())
            for name,content in files:
                chunks.append((f'--{boundary}\r\nContent-Disposition: form-data; name="images[]"; filename="{name}"\r\nContent-Type: image/png\r\n\r\n').encode()+content+b'\r\n')
            chunks.append((f'--{boundary}--\r\n').encode())
            req=urllib.request.Request(base+'/admin/actions/product-save.php',data=b''.join(chunks),headers={'Content-Type':'multipart/form-data; boundary='+boundary})
            try:r=admin.opener.open(req)
            except urllib.error.HTTPError as error:r=error
            return r.code,r.read().decode()
        product={'category_id':catid,'sku':'TEST-01','slug':'test-frame','name':'Test Frame','price':'100000','old_price':'120000','stock':'5','color':'Black','material':'Metal','is_active':'1','is_featured':'1',
                 'description':'<p><strong>Bold</strong> <em>Italic</em><script>alert(1)</script><a href="javascript:alert(1)">Bad</a><a href="https://example.com">Good</a><ul><li>One</li></ul></p>'}
        def upload(data,files):return multipart(dict(data,csrf_token=admin.token('/admin/products.php')),files)
        upload(product,[('a.png',png),('b.png',png)])
        saved=sql('SELECT * FROM products WHERE sku=?',['TEST-01']);check('product create with two images',len(saved)==1)
        new_id=saved[0]['id'];desc=saved[0]['description']
        check('server HTML sanitizer strips script and unsafe URL, preserves formatting','<script' not in desc and 'javascript:' not in desc and '<strong>Bold</strong>' in desc and '<ul>' in desc and 'https://example.com' in desc)
        imgs=sql('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order,id',[new_id]);check('images persisted and files exist',len(imgs)==2 and all((app/x['image_path']).is_file() for x in imgs))
        upload(dict(product,id=new_id,name='Edited Frame',stock=4),[])
        check('product edit persists',sql('SELECT name FROM products WHERE id=?',[new_id])[0]['name']=='Edited Frame')
        upload(dict(product,id=new_id,price=-1),[])
        check('negative price rejected',sql('SELECT price FROM products WHERE id=?',[new_id])[0]['price']==100000)
        upload(dict(product,sku='BAD-IMAGE',slug='bad-image'),[('fake.png',b'<?php echo 1;')])
        check('fake image rejected',not sql('SELECT id FROM products WHERE sku=?',['BAD-IMAGE']))
        upload(dict(product,sku='BIG-IMAGE',slug='big-image'),[('big.png',png+b'0'*(2*1024*1024))])
        check('oversize image rejected',not sql('SELECT id FROM products WHERE sku=?',['BIG-IMAGE']))
        upload(dict(product,id=new_id),[('x.png',png)]*4)
        check('five image limit includes existing images',sql('SELECT COUNT(*) n FROM product_images WHERE product_id=?',[new_id])[0]['n']==2)
        post(admin,'/admin/actions/product-image-delete.php',{'image_id':imgs[0]['id']})
        check('image deletion removes only selected file',not (app/imgs[0]['image_path']).exists() and (app/imgs[1]['image_path']).exists())
        post(admin,'/admin/actions/product-image-delete.php',{'image_id':imgs[1]['id']})
        check('last active image cannot be deleted',bool(sql('SELECT id FROM product_images WHERE id=?',[imgs[1]['id']])))
        # Cart and checkout.
        check('new cart has zero total','0đ' in customer.request('/cart.php')[2])
        post(customer,'/actions/cart-add.php',{'product_id':pid,'quantity':1})
        post(customer,'/actions/cart-add.php',{'product_id':pid,'quantity':1})
        check('same item accumulates quantity','value="2"' in customer.request('/cart.php')[2])
        post(customer,'/actions/cart-update.php',{'product_id':pid,'quantity':99})
        check('quantity beyond stock rejected','value="2"' in customer.request('/cart.php')[2])
        post(customer,'/actions/cart-voucher.php',{'voucher_code':'luxe10'})
        def checkout(client,extra=None):
            tokenpage=client.request('/cart.php')[2]
            token=re.search('name="checkout_token" value="([a-f0-9]+)"',tokenpage)[1]
            data={'recipient_name':'Test Buyer','recipient_phone':'0912345678','recipient_email':'buyer@example.test','province':'HCM','district':'Q1','address_detail':'10 Test Street','note':'Test note','payment_method':'cod','checkout_token':token,'csrf_token':client.token('/cart.php')}
            if extra:data.update(extra)
            return client.request('/actions/checkout.php',data),data
        response,bad=checkout(customer,{'recipient_email':'bad'})
        body=customer.request('/cart.php')[2]
        check('invalid delivery retained with field error','10 Test Street' in body and 'error-recipient_email' in body and '</textarea>' in body)
        oldstock=sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']
        response,data=checkout(customer,{'total_amount':1,'price':1})
        order=sql('SELECT * FROM orders ORDER BY id DESC LIMIT 1')[0]; oid=order['id']
        check('checkout creates order and stock decrements',response[0]==303 and sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==oldstock-2)
        check('server computes voucher and ignores client price',order['subtotal']==sample['price']*2 and order['total_amount']==round(sample['price']*2*.9))
        customer.request('/actions/checkout.php',data)
        check('checkout token replay does not duplicate',sql('SELECT COUNT(*) n FROM orders')[0]['n']==1)
        check('cart clears only after success','Giỏ hàng đang trống' in customer.request('/cart.php')[2])
        check('other customer cannot read order',other.request('/my-orders.php?id='+str(oid))[0]==404)
        check('other customer cannot cancel order',post(other,'/actions/order-cancel.php',{'order_id':oid})[0]==404)
        sql('UPDATE products SET name=?,price=price+1 WHERE id=?',['Changed current name',pid])
        check('old order retains purchased name/price',sample['name'] in customer.request('/my-orders.php?id='+str(oid))[2])
        post(customer,'/actions/order-cancel.php',{'order_id':oid});post(customer,'/actions/order-cancel.php',{'order_id':oid})
        check('cancel twice restores stock exactly once',sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==oldstock and sql('SELECT status FROM orders WHERE id=?',[oid])[0]['status']=='cancelled')
        def create_order(method='cod'):
            post(customer,'/actions/cart-add.php',{'product_id':pid,'quantity':1})
            result,_=checkout(customer,{'payment_method':method})
            assert result[0]==303
            return sql('SELECT id FROM orders ORDER BY id DESC LIMIT 1')[0]['id']
        def state(oid,target,payment='unpaid'):
            return post(admin,'/admin/actions/order-status.php',{'order_id':oid,'status':target,'payment_status':payment})
        oid=create_order();state(oid,'completed')
        check('cannot skip order states',sql('SELECT status FROM orders WHERE id=?',[oid])[0]['status']=='pending')
        state(oid,'confirmed');state(oid,'shipping');state(oid,'completed')
        check('COD completion marks paid atomically',sql('SELECT status,payment_status FROM orders WHERE id=?',[oid])[0]=={'status':'completed','payment_status':'paid'})
        state(oid,'pending');check('completed is terminal',sql('SELECT status FROM orders WHERE id=?',[oid])[0]['status']=='completed')
        bank=create_order('bank_transfer');state(bank,'confirmed');state(bank,'shipping')
        check('unpaid bank transfer cannot ship',sql('SELECT status FROM orders WHERE id=?',[bank])[0]['status']=='confirmed')
        state(bank,'confirmed','paid');state(bank,'cancelled','paid');check('paid order cannot cancel',sql('SELECT status FROM orders WHERE id=?',[bank])[0]['status']=='confirmed')
        state(bank,'shipping','paid');check('paid transfer can ship',sql('SELECT status FROM orders WHERE id=?',[bank])[0]['status']=='shipping')
        cancel=create_order();state(cancel,'confirmed');before=sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock'];state(cancel,'cancelled');state(cancel,'cancelled')
        check('admin cancel confirmed restores once',sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==before+1)
        # Fault injection only in isolated test database: a trigger forces failure after order insert.
        sql("CREATE TRIGGER fail_items BEFORE INSERT ON order_items FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test rollback'")
        count=sql('SELECT COUNT(*) n FROM orders')[0]['n'];before=sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']
        post(customer,'/actions/cart-add.php',{'product_id':pid,'quantity':1});checkout(customer)
        check('checkout rollback has no partial order/stock change and retains cart',sql('SELECT COUNT(*) n FROM orders')[0]['n']==count and sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==before and 'Giỏ hàng đang trống' not in customer.request('/cart.php')[2])
        sql('DROP TRIGGER fail_items')
        # Two independent PHP servers simulate simultaneous purchases of the last unit.
        from concurrent.futures import ThreadPoolExecutor
        second_port=free_port()
        processes.append(subprocess.Popen([php,'-S','127.0.0.1:'+str(second_port),'-t',str(app)],stdout=log,stderr=log,creationflags=flags))
        other.base='http://127.0.0.1:'+str(second_port)
        time.sleep(1)
        post(customer,'/actions/cart-remove.php',{'product_id':pid})
        sql('UPDATE products SET stock=1 WHERE id=?',[pid])
        for buyer in [customer,other]:post(buyer,'/actions/cart-add.php',{'product_id':pid,'quantity':1})
        requests=[]
        for buyer in [customer,other]:
            body=buyer.request('/cart.php')[2]
            token=re.search('name="checkout_token" value="([a-f0-9]+)"',body)[1]
            requests.append((buyer,dict(data,csrf_token=buyer.token('/cart.php'),checkout_token=token,payment_method='cod')))
        before=sql('SELECT COUNT(*) n FROM orders')[0]['n']
        with ThreadPoolExecutor(max_workers=2) as pool:
            list(pool.map(lambda pair:pair[0].request('/actions/checkout.php',pair[1]),requests))
        check('concurrent checkout last unit creates exactly one order and never negative stock',sql('SELECT COUNT(*) n FROM orders')[0]['n']==before+1 and sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==0)
        race_id=sql('SELECT id FROM orders ORDER BY id DESC LIMIT 1')[0]['id']
        worker=temp/'cancel-worker.php'
        worker.write_text("<?php require "+repr((app/'includes/orders.php').as_posix())+"; try { change_order((int)$argv[1], 'cancelled', null, null); echo 'ok'; } catch (DomainException $e) { echo 'rejected'; }",encoding='utf-8')
        workers=[subprocess.Popen([php,str(worker),str(race_id)],stdout=subprocess.PIPE,stderr=subprocess.PIPE,creationflags=flags) for _ in range(2)]
        outputs=[w.communicate(timeout=20)[0] for w in workers]
        check('concurrent cancellation restores stock only once',sorted(outputs)==[b'ok',b'rejected'] and sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==1)
        previous_files=set((app/'uploads/products').glob('*'))
        sql("CREATE TRIGGER fail_image BEFORE INSERT ON product_images FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test image rollback'")
        upload(dict(product,sku='ROLLBACK-IMG',slug='rollback-img'),[('a.png',png)])
        check('upload DB failure cleans new files and product row',not sql('SELECT id FROM products WHERE sku=?',['ROLLBACK-IMG']) and set((app/'uploads/products').glob('*'))==previous_files)
        sql('DROP TRIGGER fail_image')
        # Cancellation rollback and cancellation versus payment must stay atomic.
        sql('UPDATE products SET stock=20 WHERE id=?',[pid])
        rollback_id=create_order();before=sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']
        sql("CREATE TRIGGER fail_stock BEFORE UPDATE ON products FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test cancel rollback'")
        state(rollback_id,'cancelled')
        check('cancellation DB failure rolls back status and stock together',sql('SELECT status FROM orders WHERE id=?',[rollback_id])[0]['status']=='pending' and sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']==before)
        sql('DROP TRIGGER fail_stock')
        race_worker=temp/'state-worker.php'
        race_worker.write_text("<?php require "+repr((app/'includes/orders.php').as_posix())+"; try { change_order((int)$argv[1], $argv[2], $argv[3], null); echo 'ok'; } catch (DomainException $e) { echo 'rejected'; }",encoding='utf-8')
        outcomes=[]
        for attempt in range(3):
            order_id=create_order('bank_transfer');before=sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']
            workers=[subprocess.Popen([php,str(race_worker),str(order_id),target,payment],stdout=subprocess.PIPE,stderr=subprocess.PIPE,creationflags=flags) for target,payment in [('cancelled','unpaid'),('pending','paid')]]
            outputs=[w.communicate(timeout=20)[0] for w in workers]
            final=sql('SELECT status,payment_status FROM orders WHERE id=?',[order_id])[0];stock=sql('SELECT stock FROM products WHERE id=?',[pid])[0]['stock']
            outcomes.append(sorted(outputs)==[b'ok',b'rejected'] and ((final=={'status':'cancelled','payment_status':'unpaid'} and stock==before+1) or (final=={'status':'pending','payment_status':'paid'} and stock==before)))
        check('three cancel versus payment races never produce cancelled-paid or wrong stock',all(outcomes))
        # Combined filters, query escaping and pager boundaries.
        filtered=guest.request('/products.php?category=gong-kinh-can&min_price=900000&max_price=1200000&sort=price_desc')[2]
        expected=sql("SELECT p.name FROM products p JOIN categories c ON p.category_id=c.id WHERE c.slug='gong-kinh-can' AND p.price BETWEEN 900000 AND 1200000 ORDER BY p.price DESC,p.id")
        positions=[filtered.find(row['name']) for row in expected]
        check('combined category price and sort match database',len(positions)>0 and all(n>=0 for n in positions) and positions==sorted(positions))
        check('literal wildcard does not return all products',guest.request('/products.php?q=%25')[2].count('class="product-card"')==0)
        check('sort injection is ignored',guest.request('/products.php?sort=price;DROP%20TABLE%20products')[0]==200)
        check('cart empty voucher cannot activate',post(guest,'/actions/cart-voucher.php',{'voucher_code':'LUXE10'},'/auth.php')[0]==303)
        check('checkout guest returns through login to cart',post(guest,'/actions/checkout.php',{},'/auth.php')[1].get('Location','').endswith('auth.php?return=cart'))
        check('malformed product ID handled',guest.request('/actions/product-info.php?id[]=1')[0]==404)
        # UI tests use the same isolated application and fake accounts.
        if args.playwright:
            env=os.environ|{'COMMERCE_BASE':base,'COMMERCE_PASSWORD':password,'COMMERCE_ARTIFACTS':str(temp),'COMMERCE_PLAYWRIGHT':args.playwright,'COMMERCE_BROWSER':args.browser}
            subprocess.run(['node',str(root/'scripts/test-commerce-ui.cjs')],env=env,check=True,creationflags=flags)
            report['ui']=json.loads((temp/'ui-results.json').read_text(encoding='utf-8'))
        report['checks']=checks
    finally:
        (temp/'results.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
        for proc in reversed(processes):
            proc.terminate()
            try:proc.wait(timeout=10)
            except subprocess.TimeoutExpired:proc.kill()
        for handle in handles:handle.close()
        print('ARTIFACTS: '+str(temp),flush=True)
    if any(c['result']=='FAIL' for c in checks+report.get('ui',[])):raise SystemExit(1)

if __name__=='__main__':main()

