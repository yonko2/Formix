import { test, expect } from '@playwright/test';

test('has title', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Formica/);
});

test('can login', async ({ page }) => {
  await page.goto('/login.php');

  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  
  await expect(page).toHaveScreenshot('login-page.png');
  
  await page.click('button[type="submit"]');

  await expect(page).toHaveURL(/\/index\.php/);
  
  await expect(page).toHaveScreenshot('home-page-logged-in.png');
});
