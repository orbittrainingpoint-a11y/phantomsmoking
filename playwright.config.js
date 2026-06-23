// @ts-check
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/playwright',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: 0,
    workers: 1,
    reporter: [
        ['list'],
        ['json', { outputFile: 'tests/reports/playwright-report.json' }],
        ['html', { outputFolder: 'tests/reports/playwright-html', open: 'never' }],
    ],
    use: {
        baseURL: 'http://phantomsmoking.local',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        ignoreHTTPSErrors: true,
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
