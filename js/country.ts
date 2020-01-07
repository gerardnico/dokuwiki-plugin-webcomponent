import * as jQuery from "jquery";

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

export interface country {
    country2: string,
    country3: string,
    country: string
}

export function isEu(country: country):boolean {
    let euCountryCodes: string[] = ['AL', 'AD', 'AM', 'AT', 'BY', 'BE', 'BA', 'BG', 'CH', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FO', 'FI', 'FR', 'GB', 'GE', 'GI', 'GR', 'HU', 'HR', 'IE', 'IS', 'IT', 'LT', 'LU', 'LV', 'MC', 'MK', 'MT', 'NO', 'NL', 'PO', 'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SM', 'TR', 'UA', 'VA'];
    return euCountryCodes.lastIndexOf(country.country2) > -1;
}

/**
 *
 * @returns {string} the country code on two characters
 */
export function getCountry(): country {

    let countryString : string = localStorage.getItem(countryKey);

    if (countryString == null ) {
        jQuery.ajax(
            'https://api.gerardnico.com/ip',
            {
                success: function (data: ipJson) {
                    let country: country =  {
                        country2: data.country2,
                        country3: data.country3,
                        country: data.country,
                    }
                    localStorage.setItem(countryKey, JSON.stringify(country));
                    return country;
                },
                error: function() {
                    // An error occurred, trying the fallback
                    jQuery.ajax(
                        'https://ip2c.org/self',
                        {
                            success: function (data: string) {
                                let strings = data.split(";");
                                let result: string = strings[0];
                                let country: country = {
                                    country2: strings[1],
                                    country3: strings[2],
                                    country: strings[3],
                                };
                                // the country was found in the database, otherwise it return 2;;;UNKNOWN
                                if (result=='1') {
                                    localStorage.setItem(countryKey, JSON.stringify(country));
                                }
                                return country;
                            }
                        }
                    )
                }
            }
        )
    } else {

        return JSON.parse(countryString);

    }

}





