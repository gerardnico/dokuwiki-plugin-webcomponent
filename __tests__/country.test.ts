import * as Country from "../js/Country";

test('France is a eur country', () => {
    let country: Country.country = {
        country: 'France',
        country2: 'FR',
        country3: 'FRA',
    }
    let isEu = Country.isEu(country)
    expect(isEu).toBe(true);
});


