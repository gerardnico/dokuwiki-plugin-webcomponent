import * as Country from "../Country";
import * as Consent from "../Consent";
import { Wco } from "../index"

// Declare global constant
declare global {
    interface Window {
        wco: Wco;
    }
}

// Otherwise a breakpoint will fail the test
jest.setTimeout(100000);




describe('Basic: Consent Box in EU country', () => {
    
    beforeAll(async () => {
        
        // Log all console statement
        await page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        await page.evaluate(() => console.log(`url is ${location.href}`));

        // Get an empty local storage
        await page.goto('http://localhost:8080')
        await page.evaluate(() => {
            window.wco.consent.remove();
        });
        // Set a EU country
        let frCountry: Country.country = {
            country: 'France',
            country2: 'FR',
            country3: 'FRA',
        }
        await page.evaluate((country) => {
            window.wco.country.store(country);
        }, frCountry);

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should load the Consent Popup', async () => {
        await expect(page).toMatchElement('#' + Consent.htmlBoxId)
    })

    test('A click should destroy the element and accepts Gdpr', async () => {
        await expect(page).toClick('#'+Consent.htmlBoxId+' > button');
        await expect(page).not.toMatchElement('#' + Consent.htmlBoxId)
        const consent: Consent.consent = JSON.parse(await page.evaluate(() => {
            return JSON.stringify(window.wco.consent.get());            
        }));
        expect(consent.choice).toBe(Consent.consent_choice.YES);
        var today = new Date();
        var dateGreater = new Date(today.getTime() - 60);
        var consentDate = new Date(consent.date);
        expect(consentDate.getTime()).toBeGreaterThan(dateGreater.getTime());
    })

})

describe('Basic: No Consent Box in Non EU country', () => {

    beforeAll(async () => {

        // Log all console statement
        await page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        await page.evaluate(() => console.log(`url is ${location.href}`));

        // Get an empty local storage
        await page.goto('http://localhost:8080')
        await page.evaluate(() => {
            window.wco.consent.remove();
        });
        // Set a Non-EU country
        let nonEuCountry: Country.country = {
            country: 'America',
            country2: 'US',
            country3: 'USA',
        }
        await page.evaluate((country) => {
            window.wco.country.store(country);
        }, nonEuCountry);

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should not load the Consent Popup', async () => {
        await expect(page).not.toMatchElement('#' + Consent.htmlBoxId)
    })

    test('Should have an implicit consent', async () => {
        const consent: Consent.consent = JSON.parse(await page.evaluate(() => {
            return JSON.stringify(window.wco.consent.get());
        }));
        expect(consent.choice).toBe(Consent.consent_choice.NEU);
        var today = new Date();
        var dateGreater = new Date(today.getTime() - 60);
        var consentDate = new Date(consent.date);
        expect(consentDate.getTime()).toBeGreaterThan(dateGreater.getTime());
        
    })

})