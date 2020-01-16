import * as Country from "../Country";
import * as Gdpr from "../Gdpr";
import wco from "../index";


// Otherwise a breakpoint will fail the test
jest.setTimeout(100000);

let frCountry: Country.country = {
    country: 'France',
    country2: 'FR',
    country3: 'FRA',
}

test('France is a EU country', () => {
    let isEu = Country.isEu(frCountry)
    expect(isEu).toBe(true);
});


describe('Consent Box', () => {
    
    beforeAll(async () => {
        
        // Log all console statement
        await page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        await page.evaluate(() => console.log(`url is ${location.href}`));

        // Get an empty local storage
        await page.goto('http://localhost:8080')
        await page.evaluate(() => {
            wco.consent.remove();
        });
        await page.evaluate((country) => {
            wco.country.store(country);
        }, frCountry);

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should load the Consent Popup', async () => {
        await expect(page).toMatchElement('#' + Gdpr.htmlBoxId)
    })

    test('A click should destroy the element and accepts Gdpr', async () => {
        await expect(page).toClick('#gdpr_consent > button');
        await expect(page).not.toMatchElement('#' + Gdpr.htmlBoxId)
        const consent = await page.evaluate(() => {
            return wco.consent.get();            
        });
        expect(consent).toBe(true);
    })

})