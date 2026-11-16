// Called by test-tv2.py against its disposable local server and fixture users.
const { chromium } = require(process.env.TV2_PLAYWRIGHT);
const fs = require('node:fs');
const path = require('node:path');

(async () => {
  const browser = await chromium.launch({executablePath: process.env.TV2_BROWSER, headless: true});
  const results = [];
  const check = (name, ok) => results.push({name, result: ok ? 'PASS' : 'FAIL'});
  const base = process.env.TV2_BASE_URL;
  const output = process.env.TV2_ARTIFACTS;
  try {
    for (const width of [375, 768, 1440]) {
      const context = await browser.newContext({viewport: {width, height: 900}});
      const page = await context.newPage();
      await page.goto(base + '/auth.php');
      await page.locator('#tabRegister').click();
      check(`UI ${width}: registration tab opens`, await page.locator('#formRegister').isVisible());
      await page.locator('#tabLogin').click();
      check(`UI ${width}: login tab opens`, await page.locator('#formLogin').isVisible());
      await page.screenshot({path:path.join(output, `auth-${width}.png`),fullPage:true});
      check(`UI ${width}: auth no page overflow`,await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
      await page.locator('#formLogin [name=identifier]').fill('admin@example.test');
      await page.locator('#formLogin [name=password]').fill(process.env.TV2_TEST_PASSWORD);
      await Promise.all([page.waitForURL('**/admin/users.php'),page.locator('#formLogin button[type=submit]').click()]);
      check(`UI ${width}: admin no page overflow`,await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
      await page.screenshot({path:path.join(output, `admin-${width}.png`),fullPage:true});
      await page.goto(base+'/profile.php');
      check(`UI ${width}: profile no page overflow`,await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
      await page.locator('#profileName').fill('Admin UI '+width);
      await Promise.all([page.waitForURL('**/profile.php'),page.locator('#profileForm button[type=submit]').click()]);
      // Wait for POST navigation rather than relying on same-URL wait alone.
      await page.waitForLoadState('networkidle');
      check(`UI ${width}: profile save completes`,await page.locator('#profileName').inputValue()==='Admin UI '+width);
      await page.screenshot({path:path.join(output, `profile-${width}.png`),fullPage:true});
      await context.close();
    }
  } finally {
    fs.writeFileSync(path.join(output,'ui-results.json'),JSON.stringify(results,null,2));
    await browser.close();
  }
  for (const r of results) console.log(r.result+': '+r.name);
})().catch(error=>{console.error(error.message);process.exitCode=1;});
