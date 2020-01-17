import consent from './Consent';
import country from './Country';
import httpListener from './HttpRequestListener'

export interface Wco {
    consent: typeof import("./Consent").default,
    country: typeof import("./Country").default
    httpListener: typeof import("./HttpRequestListener").default
}

let wco: Wco = {
     consent,
     country,
     httpListener
}

export default wco;


