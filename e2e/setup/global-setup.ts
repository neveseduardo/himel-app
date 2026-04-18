import { chromium, type FullConfig } from '@playwright/test';
import { execSync } from 'child_process';
import { mkdirSync } from 'fs';
import path from 'path';

async function globalSetup(config: FullConfig) {
  const storageStatePath = path.join('e2e', '.auth', 'user.json');
  mkdirSync(path.dirname(storageStatePath), { recursive: true });

  execSync('php artisan db:seed --class=E2eTestSeeder --no-interaction', {
    stdio: 'inherit',
  });

  const baseURL = config.projects[0].use.baseURL || 'http://127.0.0.1:8000';

  // Use full Chromium (not headless shell) to ensure JS module execution
  const defaultPath = chromium.executablePath();
  const fullChromiumPath = defaultPath.includes('headless_shell')
    ? defaultPath.replace('chromium_headless_shell-', 'chromium-')
        .replace('headless_shell', 'chrome-linux64/chrome')
    : defaultPath;

  const browser = await chromium.launch({
    executablePath: fullChromiumPath,
  });
  const context = await browser.newContext();
  const page = await context.newPage();

  await page.goto(`${baseURL}/login`);
  await page.getByLabel('Endereço de e-mail').waitFor({ state: 'visible', timeout: 30000 });
  await page.getByLabel('Endereço de e-mail').fill('e2e@test.com');
  await page.locator('#password input').fill('password');
  await page.getByRole('button', { name: 'Entrar' }).click();

  await page.waitForURL('**/dashboard**');

  await context.storageState({ path: storageStatePath });
  await browser.close();
}

export default globalSetup;
