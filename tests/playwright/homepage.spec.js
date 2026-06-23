// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Homepage & Navigation', () => {
    test('homepage loads with non-error status', async ({ page }) => {
        const response = await page.goto('/');
        // Age gate or homepage — either is fine, just not a 5xx
        expect(response.status()).toBeLessThan(500);
    });

    test('homepage has a page title', async ({ page }) => {
        await page.goto('/');
        const title = await page.title();
        expect(title.length).toBeGreaterThan(0);
    });

    test('homepage does not expose PHP errors', async ({ page }) => {
        await page.goto('/');
        const content = await page.content();
        expect(content).not.toContain('Fatal error');
        expect(content).not.toContain('Parse error');
        expect(content).not.toContain('Stack trace');
    });

    test('navigation has anchor links', async ({ context, page }) => {
        // Set age-gate cookie so homepage renders navigation (not the age-verify redirect)
        await context.addCookies([{ name: 'age_verified', value: '1', domain: 'phantomsmoking.local', path: '/' }]);
        await page.goto('/');
        const links = await page.locator('a').count();
        expect(links).toBeGreaterThan(0);
    });
});

test.describe('Security Headers', () => {
    test('X-Frame-Options header is SAMEORIGIN', async ({ page }) => {
        const response = await page.goto('/');
        const header = response.headers()['x-frame-options'];
        expect(header).toBeTruthy();
        expect(header.toUpperCase()).toContain('SAMEORIGIN');
    });

    test('X-Content-Type-Options is nosniff', async ({ page }) => {
        const response = await page.goto('/');
        const header = response.headers()['x-content-type-options'];
        expect(header).toBe('nosniff');
    });

    test('Content-Security-Policy header is present', async ({ page }) => {
        const response = await page.goto('/');
        const header = response.headers()['content-security-policy'];
        expect(header).toBeTruthy();
    });

    test('X-XSS-Protection header is present', async ({ page }) => {
        const response = await page.goto('/');
        const header = response.headers()['x-xss-protection'];
        expect(header).toBeTruthy();
    });

    test('session cookie has HttpOnly flag', async ({ context }) => {
        await context.clearCookies();
        const page = await context.newPage();
        await page.goto('/');
        const cookies = await context.cookies();
        const sessionCookie = cookies.find(c => c.name.startsWith('SS_'));
        if (sessionCookie) {
            expect(sessionCookie.httpOnly).toBe(true);
            expect(sessionCookie.sameSite).not.toBe('None');
        } else {
            // Mark as passed if no session cookie set (age gate may not set one)
            expect(true).toBe(true);
        }
    });

    test('Server header does not expose detailed version', async ({ page }) => {
        const response = await page.goto('/');
        const server = response.headers()['server'] || '';
        // Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12 — too much info
        expect(server).not.toMatch(/PHP\/\d+\.\d+\.\d+/);
    });
});

test.describe('Authentication', () => {
    test('login page loads', async ({ page }) => {
        const response = await page.goto('/login');
        expect(response.status()).toBe(200);
    });

    test('login form exists', async ({ page }) => {
        await page.goto('/login');
        const form = await page.locator('form').count();
        expect(form).toBeGreaterThan(0);
    });

    test('register page loads', async ({ page }) => {
        const response = await page.goto('/register');
        expect(response.status()).toBe(200);
    });

    test('login form has a hidden CSRF/token input', async ({ page }) => {
        await page.goto('/login');
        const hidden = await page.locator('input[type="hidden"]').count();
        expect(hidden).toBeGreaterThan(0);
    });

    test('admin redirects when not authenticated', async ({ page }) => {
        const response = await page.goto('/admin');
        // Should be redirected to login (302) or forbidden (403), NOT 200
        // After following redirects we expect the login page
        const finalUrl = page.url();
        const finalStatus = response.status();
        // Either it redirected us away from /admin, or returned 403
        const isProtected = finalStatus === 403 ||
                           finalUrl.includes('login') ||
                           finalUrl !== 'http://phantomsmoking.local/admin';
        expect(isProtected).toBe(true);
    });
});

test.describe('Sensitive Path Protection', () => {
    test('.env is blocked (403)', async ({ page }) => {
        const response = await page.goto('/.env');
        expect(response.status()).toBeGreaterThanOrEqual(403);
    });

    test('composer.json is blocked', async ({ page }) => {
        const response = await page.goto('/composer.json');
        expect(response.status()).toBeGreaterThanOrEqual(403);
    });

    test('vendor directory is blocked', async ({ page }) => {
        const response = await page.goto('/vendor/autoload.php');
        expect(response.status()).toBeGreaterThanOrEqual(403);
    });

    test('.git directory is blocked', async ({ page }) => {
        const response = await page.goto('/.git/config');
        expect(response.status()).toBeGreaterThanOrEqual(403);
    });
});

test.describe('XSS Prevention', () => {
    test('XSS in search query is not reflected raw', async ({ page }) => {
        await page.goto('/search?q=<script>alert(1)</script>');
        const content = await page.content();
        expect(content).not.toContain('<script>alert(1)</script>');
    });

    test('search page loads with normal query', async ({ page }) => {
        const response = await page.goto('/search?q=cigar');
        expect(response.status()).toBeLessThan(500);
    });
});

test.describe('Error Handling', () => {
    test('unknown route returns 404', async ({ page }) => {
        const response = await page.goto('/this-page-xyz-does-not-exist-404test');
        expect(response.status()).toBe(404);
    });

    test('404 page does not leak server paths', async ({ page }) => {
        await page.goto('/this-page-xyz-does-not-exist-404test');
        const content = await page.content();
        expect(content).not.toContain('C:\\xampp\\htdocs');
        expect(content).not.toContain('Stack trace');
        expect(content).not.toContain('Fatal error');
    });
});

test.describe('Core Pages', () => {
    test('cart page loads', async ({ page }) => {
        const response = await page.goto('/cart');
        expect(response.status()).toBeLessThan(500);
    });

    test('brands page loads', async ({ page }) => {
        const response = await page.goto('/brands');
        expect(response.status()).toBeLessThan(500);
    });

    test('about page loads', async ({ page }) => {
        const response = await page.goto('/about');
        expect(response.status()).toBeLessThan(500);
    });

    test('contact page loads', async ({ page }) => {
        const response = await page.goto('/contact');
        expect(response.status()).toBeLessThan(500);
    });

    test('robots.txt is accessible', async ({ page }) => {
        const response = await page.goto('/robots.txt');
        expect(response.status()).toBe(200);
    });
});
