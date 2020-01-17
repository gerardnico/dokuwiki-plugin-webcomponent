import * as Country from "../Country";


// Otherwise a breakpoint will fail the test
jest.setTimeout(100000);



test('France is a EU country', () => {
    let frCountry: Country.country = {
        country: 'France',
        country2: 'FR',
        country3: 'FRA',
        date: new Date()
    }
    let isEu = Country.isEu(frCountry)
    expect(isEu).toBe(true);
});

test('Country has expired', () => {
    let country: Country.country = {
        date: new Date(new Date().getTime() - ( Country.expiration_periode + 1) * (24 * 60 * 60 * 1000)),
        country: 'France',
        country2: 'FR',
        country3: 'FRA',
    }
    expect(Country.hasExpired(country)).toBe(true);
})

test('Country has not expired', () => {
    let country: Country.country = {
        date: new Date(new Date().getTime() - (Country.expiration_periode - 1) * (24 * 60 * 60 * 1000)),
        country: 'France',
        country2: 'FR',
        country3: 'FRA',
    }
    expect(Country.hasExpired(country)).toBe(false);
})

