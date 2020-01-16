import * as Country from "../Country";
import * as Gdpr from "../Consent";
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

