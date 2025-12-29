import { test, expect } from '@playwright/test';

test('has title', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Formica/);
});

test('can login', async ({ page }) => {
  await page.goto('/login.php');

  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  
  // Take screenshot before login
  await page.screenshot({ path: 'test-results/login-page.png' });
  
  await page.click('button[type="submit"]');

  // Expect to be redirected to home and see "Logout" or similar
  await expect(page).toHaveURL(/\/index\.php/);
  
  // Take screenshot after login
  await page.screenshot({ path: 'test-results/home-page-logged-in.png' });
});
