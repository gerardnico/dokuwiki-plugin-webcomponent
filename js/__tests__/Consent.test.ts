import * as Country from "../Country";
import * as Consent from "../Consent";
import { Wco } from "../index"
import { JSONObject } from "puppeteer";

// Declare global constant
declare global {
    interface Window {
        wco: Wco;
    }
}

// Otherwise a breakpoint will fail the test
jest.setTimeout(100000);


// Log all console statement
page.on('console', msg => console.log('PAGE LOG:', msg.text()));
page.evaluate(() => console.log(`url is ${location.href}`));


test('Consent has expired', () => {
    let consent: Consent.consent = {
        date: new Date(new Date().getTime() - Consent.expiration_periode * (24 * 60 * 60 * 1000)),
        choice: Consent.consent_choice.YES
    }
    expect(Consent.hasExpired(consent)).toBe(true);
})

test('Consent has not expired', () => {
    let consent: Consent.consent = {
        choice: Consent.consent_choice.YES,
        date: new Date((new Date().getTime() - (Consent.expiration_periode - 1) * 1000 * 60 * 60 * 24))
    }
    expect(Consent.hasExpired(consent)).toBe(false);
})

describe('EU no consent', () => {

    beforeAll(async () => {

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
            date: new Date(),
        }
        await page.evaluate((country) => {
            window.wco.country.store(country);
        }, JSON.stringify(frCountry));

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should load the Consent Popup', async () => {
        await expect(page).toMatchElement('#' + Consent.htmlBoxId)
    })

    test('A click should destroy the element and accepts Gdpr', async () => {
        await expect(page).toClick('#' + Consent.htmlBoxId + ' > button');
        await expect(page).not.toMatchElement('#' + Consent.htmlBoxId)
        const consent: Consent.consent | null = Consent.toConsent(await page.evaluate(() => {
            return JSON.stringify(window.wco.consent.get());
        }));
        expect(consent).not.toBeNull();
        if (consent != null) {
            expect(consent.choice).not.toBeNull();
            expect(consent.choice).toBe(Consent.consent_choice.YES);
            var today = new Date();
            var dateGreater = new Date(today.getTime() - 60);
            expect(consent.date).not.toBeNull();
            if (consent.date != null) {
                expect(consent.date.getTime()).toBeGreaterThan(dateGreater.getTime());
            }
        }
    })

})

describe('EU consent not expired', () => {

    beforeAll(async () => {

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
            date: new Date()
        }
        await page.evaluate((country) => {
            window.wco.country.store(country);
        }, JSON.stringify(frCountry));

        // Set an non expired consent
        let consent: Consent.consent = {
            choice: Consent.consent_choice.YES,
            date: new Date((new Date().getTime() - (Consent.expiration_periode - 1) * 1000 * 60 * 60 * 24))
        }
        let consent_json: string = JSON.stringify(consent);
        await page.evaluate((consent: string) => {
            window.wco.consent.set(consent)
        }, consent_json);

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should not load the Consent Popup', async () => {
        await expect(page).not.toMatchElement('#' + Consent.htmlBoxId)
    })


})

describe('EU Consent expired', () => {

    beforeAll(async () => {


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
            date: new Date()
        }
        await page.evaluate((country) => {
            window.wco.country.store(country);
        }, JSON.stringify(frCountry));

        // Set an expired consent
        let consent: Consent.consent = {
            choice: Consent.consent_choice.YES,
            date: new Date((new Date().getTime() - (Consent.expiration_periode + 1) * 1000 * 60 * 60 * 24))
        }
        let consent_json: string = JSON.stringify(consent);
        await page.evaluate((consent: string) => {
            window.wco.consent.set(consent)
        }, consent_json);

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should load the Consent Popup', async () => {
        await expect(page).toMatchElement('#' + Consent.htmlBoxId)
    })

    test('A click should destroy the element and accepts Gdpr', async () => {
        await expect(page).toClick('#' + Consent.htmlBoxId + ' > button');
        await expect(page).not.toMatchElement('#' + Consent.htmlBoxId)
        const consent: Consent.consent | null = Consent.toConsent(await page.evaluate(() => {
            return JSON.stringify(window.wco.consent.get());
        }));
        expect(consent).not.toBeNull();
        if (consent != null) {
            expect(consent.choice).toBe(Consent.consent_choice.YES);
            var today = new Date();
            var dateGreater = new Date(today.getTime() - 60);
            expect(consent.date).not.toBeNull()
            if (consent.date != null) {
                expect(consent.date.getTime()).toBeGreaterThan(dateGreater.getTime());
            }
        }
    })

})

describe('Non EU Consent: No Consent Box in Non EU country', () => {

    beforeAll(async () => {

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
            date: new Date()
        }
        await page.evaluate((country) => {
            window.wco.country.store(country);
        }, JSON.stringify(nonEuCountry));

        // Go back
        await page.goto('http://localhost:8080')

    })

    test('Should not load the Consent Popup', async () => {
        await expect(page).not.toMatchElement('#' + Consent.htmlBoxId)
    })

    test('Should have an implicit consent', async () => {
        const consent: Consent.consent | null = Consent.toConsent(await page.evaluate(() => {
            return JSON.stringify(window.wco.consent.get());
        }));
        expect(consent).not.toBeNull();
        if (consent != null) {
            expect(consent.choice).toBe(Consent.consent_choice.NEU);
            var today = new Date();
            var dateGreater = new Date(today.getTime() - 6*1000);
            expect(consent.date).not.toBeNull();
            if (consent.date != null) {
                expect(consent.date.getTime()).toBeGreaterThan(dateGreater.getTime());
            }
        }

    })

})