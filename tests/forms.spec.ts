import { test, expect } from '@playwright/test';

test.describe('Form Management', () => {
  
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login.php');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/index\.php/);
  });

  test('can create a new form', async ({ page }) => {
    await page.goto('/create_form.php');
    
    // Fill form details
    await page.fill('input[name="form_name"]', 'Test Form ' + Date.now());
    await page.fill('textarea[name="form_description"]', 'This is a test form created by Playwright');
    
    // Take screenshot of create form page
    await page.screenshot({ path: 'test-results/create-form-page.png' });
    
    // Submit
    await page.click('button:has-text("Create Form")'); // Assuming button text or type
    
    // Expect to be redirected to edit form page
    await expect(page).toHaveURL(/\/edit_form\.php\?id=\d+/);
    
    // Take screenshot of edit form page
    await page.screenshot({ path: 'test-results/edit-form-page.png' });
  });

  test('can view my forms', async ({ page }) => {
    await page.goto('/my_forms.php');
    
    // Verify page title or content
    await expect(page.locator('h2')).toContainText('My Forms');
    
    // Take screenshot of my forms page
    await page.screenshot({ path: 'test-results/my-forms-page.png' });
  });

});
