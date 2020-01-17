import jQuery from 'jquery';
import * as Country from './Country';
import { JSONObject } from 'puppeteer';

// // Must be started after page load
// jQuery(function () {
//         Gdpr.consent({
//           message: 'By using our site, you acknowledge that you have read, understand and agreed to our <a href="legal/privacy">Privacy Policy</a> and <a href="legal/terms">Terms of service</a>'.
//         });
//     }
// );

export enum consent_choice {
    YES = 'Yes',
    NO = 'No',
    NEU = 'Non Eu Country',
}

export interface consent {
    date: Date | null,
    choice: consent_choice | null
}

const localStorageKey: string = 'consent_gdpr';

export const htmlBoxId: string = localStorageKey;

/**
 * Expiration periode in days
 */
export const expiration_periode: number = 90;


// The Json type
interface Config {
    message?: string;
}

// Declare global constant
declare global {
    interface Window {
        ezConsentCategories: any;
        __ezconsent: any;
    }
}

let localConfig: Config;
function consentBox(config: Config) {

    localConfig = config || {};

    if (typeof localConfig.message === 'undefined') {
        localConfig.message = 'By using our site, you acknowledge that you have read and understood our policy.';
    }
    let consentBoxSelector: string = '#' + htmlBoxId;
    let consentBox: string = `
                <div id="${htmlBoxId}" class="container alert alert-secondary alert-dismissible fixed-bottom text-center fade" role="alert" >
                    ${localConfig.message}
                    <button type="button" class="close" style="float:initial"  data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
    jQuery("body").append(consentBox);
    // Show the alert
    jQuery(consentBoxSelector).addClass('show');
    // When it's closed, we save the consent
    jQuery(consentBoxSelector).on('closed.bs.alert', function () {

        //  This event is fired when the alert has been closed (will wait for CSS transitions to complete)
        let consent: consent = {
            date: new Date(),
            choice: consent_choice.YES
        }
        set(consent);

        // Ezoic on the pages ?
        // Check to make sure the ezoic consent receiver is on the page
        // https://svc.ezoic.com/svc/pub/app/54/thirdparty.php
        if (typeof window.ezConsentCategories == 'object' && typeof window.__ezconsent == 'object') {

            //set each of the users consent choices
            window.ezConsentCategories.preferences = true;
            window.ezConsentCategories.statistics = true;
            window.ezConsentCategories.marketing = true;

            //call to update ezoic of the users choices
            window.__ezconsent.setEzoicConsentSettings(window.ezConsentCategories);

        }

    })


}

/**
 * Store the consent
 * @param consent 
 * We can pass a string because this is the only way to pass information to puppeteer in the test
 */
export function set(consent: string | consent) {
    let consentS: string;
    if (typeof consent != 'string') {
        consentS = JSON.stringify(consent);
    } else {
        consentS = consent;
    }
    localStorage.setItem(localStorageKey, consentS);
}



/**
 * Return if this is a EuCountry
 * and save an implicit consent if not
 */
async function onlyEuCountry(): Promise<boolean> {
    let country: Country.country | null = await Country.getCountry();
    if (country != null) {
        if (Country.isEu(country)) {
            return true;
        } else {
            let consent: consent = {
                date: new Date(),
                choice: consent_choice.NEU
            }
            set(consent);
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Return if the consent box must be shown
 * @param consent 
 */
async function consentBoxShouldAppear(consent: consent | null): Promise<boolean> {

    if (consent == null) {
        return onlyEuCountry();
    } else {
        // expired ?
        debugger;
        if (hasExpired(consent)) {
            return onlyEuCountry();
        } else {
            return false;
        }
    }

}

export function hasExpired(consent: consent) {

    if (consent.date == null) {
        return true;
    } else {
        var today = new Date();
        var expirationDate = new Date(today.getTime() - 1000 * 60 * 60 * 24 * expiration_periode);
        return expirationDate.getTime() >= consent.date.getTime()
    }

}

export async function execute(config: Config) {

    let consent: consent | null = get();

    const showConsentBox: boolean = await consentBoxShouldAppear(consent);
    if (showConsentBox == true) {
        consentBox(config);
    }

}

/**
 * Return the current consent
 * or build a consent from a JSON string
 * @param consent 
 */
export function get(consent?: string | null): consent | null {
    if (typeof consent === 'undefined' || consent == null){
        consent = localStorage.getItem(localStorageKey);
    }
    if (consent == null) {
        return null;
    } else {
        return toConsent(consent);
    }
}

function remove() {

    let returnValue: string | null = localStorage.getItem(localStorageKey);
    if (returnValue != null) {
        localStorage.removeItem(localStorageKey);
    } else {
        console.log("The consent was not found. Not removed");
    }

}

function toJsonObject(consent: consent): JSONObject {
    let consentObject: JSONObject = {
        choice: consent.choice,
        date: consent.date == null ? "null" : consent.date.toString()
    }
    return consentObject;
}

function toConsent(consent: string): consent {
    let consentJson: consent = JSON.parse(consent);
    let consentObject: consent = {
        choice: consentJson.choice,
        date: consentJson.date == null ? null : new Date(consentJson.date)
    }
    return consentObject;
}

/**
 * Will delete the consent and execute it again
 */
export function reset() {
    remove();
    execute(localConfig);
}

export function config(config: Config): Config {
    localConfig = config || localConfig;
    return localConfig;
}

function info() {
    let consent: consent = get() || {
        date: null,
        choice: null
    };
    console.log(Object.assign(consent, hasExpired(consent)))
}
export default {
    execute: execute,
    remove: remove,
    get: get,
    reset: reset,
    set: set,
    config: config,
    info: info
}



