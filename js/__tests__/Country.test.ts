import * as Country from "../Country";

test('France is a eur country', () => {
    let country: Country.country = {
        country: 'France',
        country2: 'FR',
        country3: 'FRA',
    }
    let isEu = Country.isEu(country)
    expect(isEu).toBe(true);
});

describe('Google', () => {
    
    beforeAll(async () => {
        await page.goto('https://google.com')
    })

    it('should display "google" text on page', async () => {
        await expect(page).toMatch('google')
    })
    
})
