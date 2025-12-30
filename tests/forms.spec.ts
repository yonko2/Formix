import { test, expect } from '@playwright/test';

test.describe('Form Management', () => {
  test('can create a new form', async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/index\.php/);

    await page.goto('/create_form.php');
    
    await page.fill('input[name="form_name"]', 'Test Form ');
    await page.fill('textarea[name="form_description"]', 'This is a test form created by Playwright');
    
    await expect(page).toHaveScreenshot('create-form-page.png');
    
    await page.click('button:has-text("Create Form")');
    
    await expect(page).toHaveURL(/\/edit_form\.php\?id=\d+/);
    
    await expect(page).toHaveScreenshot('edit-form-page.png');
  });

  test('can view my forms', async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="email"]', 'user@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/index\.php/);

    await page.goto('/my_forms.php');
    
    await expect(page.locator('h2')).toContainText('My Forms');
    
    await expect(page).toHaveScreenshot('my-forms-page.png');
  });

});
