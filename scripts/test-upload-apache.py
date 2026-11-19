"""Verify uploads/.htaccess using an isolated Apache + real PHP module, never the active server."""
import argparse,base64,json,os,shutil,socket,subprocess,tempfile,time,urllib.request,urllib.error
from pathlib import Path
p=argparse.ArgumentParser();p.add_argument('--xampp',default='C:/xampp');a=p.parse_args()
x=Path(a.xampp);tmp=Path(tempfile.mkdtemp(prefix='luxeoptic-apache-upload-'));root=tmp/'site';up=root/'uploads/products';up.mkdir(parents=True)
shutil.copy2(Path(__file__).resolve().parents[1]/'uploads/products/.htaccess',up/'.htaccess')
(root/'control.php').write_text('<?php echo "PHP_EXECUTES";',encoding='utf-8')
for name in ['probe.php','probe.phtml','probe.php.png']:(up/name).write_text('<?php echo "UPLOAD_EXECUTED";',encoding='utf-8')
image='a'*32+'.png';(up/image).write_bytes(base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII='))
with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
config=f'''ServerRoot "{x.as_posix()}/apache"
Listen 127.0.0.1:{port}
ServerName localhost
PidFile "{tmp.as_posix()}/httpd.pid"
ErrorLog "{tmp.as_posix()}/error.log"
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule mime_module modules/mod_mime.so
LoadModule dir_module modules/mod_dir.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadFile "{x.as_posix()}/php/php8ts.dll"
LoadModule php_module "{x.as_posix()}/php/php8apache2_4.dll"
PHPINIDir "{x.as_posix()}/php"
TypesConfig conf/mime.types
DocumentRoot "{root.as_posix()}"
<Directory "{root.as_posix()}">
  Require all granted
  AllowOverride All
</Directory>
<FilesMatch "\\.php$">
 SetHandler application/x-httpd-php
</FilesMatch>
'''
conf=tmp/'httpd.conf';conf.write_text(config,encoding='utf-8')
flags=subprocess.CREATE_NO_WINDOW if os.name=='nt' else 0
log=open(tmp/'console.log','w');proc=subprocess.Popen([str(x/'apache/bin/httpd.exe'),'-X','-f',str(conf)],stdout=log,stderr=log,creationflags=flags)
checks=[]
def request(path):
 try:r=urllib.request.urlopen(f'http://127.0.0.1:{port}'+path,timeout=5)
 except urllib.error.HTTPError as e:r=e
 return r.code,r.read()
try:
 for _ in range(50):
  try:control=request('/control.php');break
  except OSError:time.sleep(.2)
 else:raise RuntimeError('Apache did not start; see '+str(tmp))
 checks.append({'name':'Real PHP executes outside uploads','result':'PASS' if control==(200,b'PHP_EXECUTES') else 'FAIL'})
 for name in ['probe.php','probe.phtml','probe.php.png']:
  status,body=request('/uploads/products/'+name)
  checks.append({'name':name+' is denied without execution/source disclosure','result':'PASS' if status==403 and b'UPLOAD_EXECUTED' not in body and b'<?php' not in body else 'FAIL'})
 status,body=request('/uploads/products/'+image);checks.append({'name':'Valid random-name PNG still served','result':'PASS' if status==200 and body.startswith(b'\x89PNG') else 'FAIL'})
finally:
 proc.terminate();proc.wait(timeout=15);log.close()
 for f in [root/'control.php',up/'probe.php',up/'probe.phtml',up/'probe.php.png']:f.unlink(missing_ok=True)
 (tmp/'results.json').write_text(json.dumps(checks,indent=2),encoding='utf-8')
print(json.dumps(checks));print('ARTIFACTS: '+str(tmp))
if any(c['result']=='FAIL' for c in checks):raise SystemExit(1)
