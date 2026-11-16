// Runs against the disposable app created by test-commerce.py.
const {chromium}=require(process.env.COMMERCE_PLAYWRIGHT);
const fs=require('node:fs'), path=require('node:path');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.COMMERCE_BROWSER,headless:true});
 const results=[], output=process.env.COMMERCE_ARTIFACTS, base=process.env.COMMERCE_BASE;
 const check=(name,ok)=>{results.push({name,result:ok?'PASS':'FAIL'});console.log((ok?'PASS':'FAIL')+': '+name);};
 try {
 for(const width of [375,768,1024,1440]){
  const context=await browser.newContext({viewport:{width,height:950},reducedMotion:'reduce'});
  const page=await context.newPage(); const errors=[];page.on('pageerror',e=>errors.push(e.message));
  for(const route of ['/index.php','/products.php','/product-detail.php?id=2','/compare.php?id1=1&id2=2','/cart.php']){
   const r=await page.goto(base+route);await page.waitForLoadState('domcontentloaded');
   check(`UI ${width} ${route}: renders without page overflow`,r.status()===200&&await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
   if(width===375||width===1440)await page.screenshot({path:path.join(output,route.split('?')[0].slice(1,-4)+'-'+width+'.png'),fullPage:true});
  }
  await page.goto(base+'/products.php');
  await page.locator('[name=compare]').nth(0).check();await page.locator('[name=compare]').nth(1).check();
  await page.locator('#compareButton').click();await page.waitForURL('**/compare.php?*');
  check(`UI ${width}: comparison picks two distinct frames`,await page.locator('.frame-table select').evaluateAll(s=>s.length===2&&s[0].value!==s[1].value&&s.every(x=>x.value)));
  await page.locator('.frame-table select').nth(1).selectOption('');
  check(`UI ${width}: comparison can remove frame`,(await page.locator('#frameComparison [role=status]').textContent()).includes('Chọn hai'));
  await page.goto(base+'/product-detail.php?id=2');await page.getByRole('button',{name:'Hỏi LuxeOptic',exact:true}).click();
  await page.getByRole('button',{name:'Mẫu này giá bao nhiêu?',exact:true}).click();
  await page.waitForFunction(()=>document.querySelector('.help-thread').textContent.includes('Giá hiện tại:'));
  await page.getByRole('button',{name:'Mẫu này còn hàng không?',exact:true}).click();
  await page.waitForFunction(()=>document.querySelector('.help-thread').textContent.includes('Mẫu này còn hàng.'));
  check(`UI ${width}: chatbot retains both responses`,await page.locator('.help-bubble.from-customer').count()===2);
  await page.goto(base+'/auth.php');await page.locator('#formLogin [name=identifier]').fill('admin1@example.test');await page.locator('#formLogin [name=password]').fill(process.env.COMMERCE_PASSWORD);
  await Promise.all([page.waitForURL('**/admin/users.php'),page.locator('#formLogin button[type=submit]').click()]);
  await page.goto(base+'/product-detail.php?id=2');
  await Promise.all([page.waitForURL('**/cart.php'),page.getByRole('button',{name:'Thêm vào giỏ',exact:true}).click()]);
  check(`UI ${width}: checkout form and session badge work`,await page.locator('#checkoutForm').isVisible() && await page.locator('.action-badge').textContent()==='1');
  check(`UI ${width}: filled cart no page overflow`,await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
  await page.locator('[name=payment_method][value=bank_transfer]').check();
  check(`UI ${width}: payment border follows selected radio`,await page.locator('.payment-option:has(:checked)').count()===1);
  if(width===375||width===1440)await page.screenshot({path:path.join(output,'checkout-'+width+'.png'),fullPage:true});
  for(const route of ['/admin/products.php','/admin/categories.php','/admin/orders.php','/my-orders.php']){
   await page.goto(base+route);
   check(`UI ${width} ${route}: no page overflow`,await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
   if(width===375||width===1440)await page.screenshot({path:path.join(output,route.replaceAll('/','-').replace('.php','')+'-'+width+'.png'),fullPage:true});
  }
  if(width===1440){await page.goto(base+'/admin/orders.php?id=2');await page.screenshot({path:path.join(output,'order-detail-1440.png'),fullPage:true});}
  await page.goto(base+'/admin/products.php?edit=15');
  check(`UI ${width}: local Quill editor loaded`,await page.locator('.ql-editor').isVisible());
  if(width===1440){
   await page.evaluate(()=>{const q=Quill.find(document.getElementById('editor'));q.setContents([{insert:'Bold text',attributes:{bold:true}},{insert:'\n'},{insert:'Italic text',attributes:{italic:true}},{insert:'\n'},{insert:'First bullet'},{insert:'\n',attributes:{list:'bullet'}},{insert:'Link',attributes:{link:'https://example.com'}},{insert:'\n'}]);});
   await Promise.all([page.waitForResponse(r=>r.url().includes('/product-save.php')&&r.request().method()==='POST'),page.locator('#productForm button[type=submit]').click()]);
   await page.waitForLoadState('networkidle');
   check('Quill save and reload retains bold italic bullet and link',await page.locator('.ql-editor strong').count()>0 && await page.locator('.ql-editor em').count()>0 && await page.locator('.ql-editor li').count()>0 && await page.locator('.ql-editor a[href="https://example.com"]').count()>0);
   await page.goto(base+'/product-detail.php?id=15');
   check('Public detail keeps sanitized editor formatting',await page.locator('.description strong').count()>0 && await page.locator('.description ul li').count()>0);
   await page.goto(base+'/admin/products.php?edit=15');
  }

  if(width<1024){await page.locator('.admin-menu-toggle').click();check(`UI ${width}: shared admin menu opens`,await page.locator('#adminMenu').evaluate(el=>el.classList.contains('sidebar-open')));await page.locator('.sidebar-close').click();}
  await page.evaluate(()=>document.documentElement.dataset.theme='dark');
  check(`UI ${width}: dark admin retains layout`,await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
  if(width===1440)await page.screenshot({path:path.join(output,'admin-editor-dark.png'),fullPage:true});
  check(`UI ${width}: no JavaScript errors`,errors.length===0);
  await context.close();
 }
 }finally{fs.writeFileSync(path.join(output,'ui-results.json'),JSON.stringify(results,null,2));await browser.close();}
 if(results.some(r=>r.result==='FAIL'))process.exitCode=1;
})().catch(e=>{console.error(e);process.exitCode=1;});
