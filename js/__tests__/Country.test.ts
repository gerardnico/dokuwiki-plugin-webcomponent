import * as Country from "../Country";
import Gdpr from "../Gdpr"



test('France is a eur country', () => {
    let country: Country.country = {
        country: 'France',
        country2: 'FR',
        country3: 'FRA',
    }
    let isEu = Country.isEu(country)
    expect(isEu).toBe(true);
});


describe('Consent Box', () => {
    beforeAll(async () => {
        await page.goto('http://localhost:8080')
    })

    it('Should load the Consent Popup', async () => {
        page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        await page.evaluate(() => console.log(`url is ${location.href}`));
        await page.evaluate((storageKey) => {
            localStorage.removeItem(storageKey);
        }, Gdpr.storage_key);
        await expect(page).toMatchElement('#' + Gdpr.element_id)
    })
})