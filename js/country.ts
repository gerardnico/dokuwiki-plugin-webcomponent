import jQuery from "jquery";
import { JSONObject } from "puppeteer";

// They key to store the country to not hit the service every time
let countryKey = 'country_json';


// The Json returned
interface ipJson {
    success: boolean,
    ip: string,
    country2: string,
    country3: string,
    country: string
}

export interface country extends JSONObject {
    country2: string,
    country3: string,
    country: string
}

export function isEu(country: country): boolean {
    try {
        let euCountryCodes: string[] = ['AL', 'AD', 'AM', 'AT', 'BY', 'BE', 'BA', 'BG', 'CH', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FO', 'FI', 'FR', 'GB', 'GE', 'GI', 'GR', 'HU', 'HR', 'IE', 'IS', 'IT', 'LT', 'LU', 'LV', 'MC', 'MK', 'MT', 'NO', 'NL', 'PO', 'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SM', 'TR', 'UA', 'VA'];
        return euCountryCodes.lastIndexOf(country.country2) > -1;
    } catch (e) {
        console.error("Unable to determine if the country is a EU country for the country (%o) %s", country, e.message);
    }
}

/**
 *
 * @returns {country} the country of the caller
 */
export async function getCountry(): Promise<country> {

    let countryString: string = localStorage.getItem(countryKey);

    if (countryString == null) {

        let ctry: country = await getCountryFromBackend();
        if (ctry ==null){
            ctry = await getCountryFromIp2c();
        }

        if (ctry !=null){
            store(ctry);
            return ctry;
        } else {
            return null;
        }

    } else {

        return JSON.parse(countryString);

    }

}

/**
 * 
 * @param country A country to store locally
 */
export function store(country: country){
    localStorage.setItem(countryKey, JSON.stringify(country));
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
async function getCountryFromBackend(): Promise<country> {
    let fetchedCountry: ipJson = null;
    try {

        fetchedCountry = await jQuery.ajax('https://api.gerardnico.com/ip');
        
        return {
            country2: fetchedCountry.country2,
            country3: fetchedCountry.country3,
            country: fetchedCountry.country,
        }

    } catch (e) {

        console.error("Unable to fetch the country from the backend (" + e.message + ")");
        return null;

    }


}

async function getCountryFromIp2c(): Promise<country> {
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
    print: function(){
         getCountry()
        .then(value => console.log(value));
    }
}