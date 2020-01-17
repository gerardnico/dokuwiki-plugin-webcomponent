import jQuery from "jquery";

// They key to store the country to not hit the service every time
let countryKey = 'country_json';

export const expiration_periode = 90;

// The Json returned
interface ipJson {
    success: boolean,
    ip: string,
    country2: string,
    country3: string,
    country: string
}

export interface country {
    country2: string,
    country3: string,
    country: string,
    date: Date
}

export function isEu(country: country): boolean {

    let euCountryCodes: string[] = ['AL', 'AD', 'AM', 'AT', 'BY', 'BE', 'BA', 'BG', 'CH', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FO', 'FI', 'FR', 'GB', 'GE', 'GI', 'GR', 'HU', 'HR', 'IE', 'IS', 'IT', 'LT', 'LU', 'LV', 'MC', 'MK', 'MT', 'NO', 'NL', 'PO', 'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SM', 'TR', 'UA', 'VA'];
    return euCountryCodes.lastIndexOf(country.country2) > -1;

}

async function fetchCountry(): Promise<country | null> {

    let ctry: country | null = await getCountryFromBackend();
    if (ctry == null) {
        ctry = await getCountryFromIp2c();
    }

    if (ctry != null) {
        store(ctry);
        return ctry;
    } else {
        return null;
    }

}

export function hasExpired(country: country): boolean{
    if (country.date==null){
        return true;
    } else {
        var today = new Date();
        var expirationDate = new Date(today.getTime() - 1000 * 60 * 60 * 24 * expiration_periode);
        return expirationDate.getTime() >= country.date.getTime()
    }


}
/**
 *
 * @returns {country} the country of the caller
 */
export async function getCountry(): Promise<country | null> {

    let countryString: string | null = localStorage.getItem(countryKey);

    if (countryString == null) {

        return fetchCountry();

    } else {

        const country: country = JSON.parse(countryString);
        if (hasExpired(country)) {
            return fetchCountry();
        } else {
            return country;
        }

    }

}

/**
 * 
 * @param country A country to store locally
 * The input must be a string because of the puppeteer test (Only serializable data may be passed)
 */
export function store(country: country|string) {
    let countryString: string;
    if (typeof country == 'string'){
        countryString = country;
    } else {
        countryString = JSON.stringify(country)
    }
    localStorage.setItem(countryKey, countryString);
}

/**
 * 
 * @param country A country to remove
 */
export function remove() {
    localStorage.removeItem(countryKey);
}

/**
 * Return null if an error occurs
 */
async function getCountryFromBackend(): Promise<country | null> {
    try {

        let fetchedCountry: ipJson = await jQuery.ajax('https://api.gerardnico.com/ip');

        return {
            country2: fetchedCountry.country2,
            country3: fetchedCountry.country3,
            country: fetchedCountry.country,
            date: new Date()
        }

    } catch (e) {

        console.error("Unable to fetch the country from the backend (" + e.message + ")");
        return null;

    }


}

async function getCountryFromIp2c(): Promise<country | null> {
    console.log("Fetching the country from ip2c");
    try {
        const fetchedCountry: string = await jQuery.ajax('https://ip2c.org/self');
        let strings = fetchedCountry.split(";");
        // let result: string = strings[0];
        // if the country was not found in the database, it returns the string '2;;;UNKNOWN'
        return {
            country2: strings[1],
            country3: strings[2],
            country: strings[3],
            date: new Date()
        };
    } catch (e) {
        console.error("Unable to fetch the country from ip2c (" + e.message + ")");
        return null;
    }
}

export default {
    store: store,
    remove: remove,
    get: getCountry,
    print: function () {
        getCountry()
            .then(value => console.log(value));
    }
}