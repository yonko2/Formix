import { test, expect } from '@playwright/test';

test('has title', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Formica/);
});

test('can login', async ({ page }) => {
  await page.goto('/login.php');

  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');

  // Expect to be redirected to home and see "Logout" or similar
  await expect(page).toHaveURL(/\/index\.php/);
  // Assuming there is a logout link when logged in
  // await expect(page.getByText('Logout')).toBeVisible();
});
