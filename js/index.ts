import consent from './Consent';
import country from './Country';

export interface Wco {
    consent: typeof import("./Consent").default,
    country: typeof import("./Country").default
}

let wco: Wco = {
     consent,
     country
}

export default wco;


