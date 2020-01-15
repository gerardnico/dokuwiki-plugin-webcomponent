import jQuery from 'jquery';
import * as Country from './Country';

// // Must be started after page load
// jQuery(function () {
//         Gdpr.consent({
//           message: 'By using our site, you acknowledge that you have read, understand and agreed to our <a href="legal/privacy">Privacy Policy</a> and <a href="legal/terms">Terms of service</a>'.
//         });
//     }
// );


export const consentKey: string = 'consent_gdpr';
export const consentBoxId: string = 'gdpr_consent';

// Consent Value set if the country is not an EU country
let consentValueNonEu: string = 'nonEu';

// The Json type
interface Config {
    message: string;
}

// Declare global constant
declare global {
    interface Window {
        ezConsentCategories: any;
        __ezconsent: any;
    }
}


function consentBox(config: Config) {

    if (typeof config.message === 'undefined') {
        config.message = 'By using our site, you acknowledge that you have read and understand our policy.';
    }
    let consentBoxSelector: string = '#' + consentBoxId;
    let consentBox: string = `
                <div id="${consentBoxId}" class="container alert alert-secondary alert-dismissible fixed-bottom text-center fade" role="alert" >
                    ${config.message}
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
        localStorage.setItem(consentKey, true.toString());

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


export async function consent(config: Config) {

    let consentStorage: string = localStorage.getItem(consentKey);
    if (consentStorage == null) {
        localStorage.setItem(consentKey, false.toString());
    }
    // getItem return a string, therefore !'false' is false and not true
    let consent: boolean = (consentStorage !== 'true' && consentStorage !== consentValueNonEu);
    if (consent) {

        let country: Country.country = await Country.getCountry();
        if (country != null) {
            if (Country.isEu(country)) {
                consentBox(config);
            } else {
                localStorage.setItem(consentKey, consentValueNonEu);
            }
        }

    }

}


export default {
    consent: consent,
    storage_key: consentKey,
    element_id : consentBoxId
}



