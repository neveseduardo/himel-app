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

  const baseURL = config.projects[0].use.baseURL || 'http://localhost';

  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  await page.goto(`${baseURL}/login`);
  await page.getByLabel('Email').fill('e2e@test.com');
  await page.getByLabel('Password').fill('password');
  await page.getByRole('button', { name: 'Log in' }).click();

  await page.waitForURL('**/dashboard');

  await context.storageState({ path: storageStatePath });
  await browser.close();
}

export default globalSetup;
