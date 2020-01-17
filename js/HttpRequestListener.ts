import uuidv4 from "uuid/v4";

/**
 * Ref:
 *   * https://gerardnico.com/web/browser/xhr
 *   * https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest
 *   * https://stackoverflow.com/questions/629671/how-can-i-intercept-xmlhttprequests-from-a-greasemonkey-script
 *   * xhr-intercept package
 * @param {*} from 
 * @param {*} to 
 */


export default class HttpRequestListener {

    name: string = 'HttpRequestListener';
    trackingUrl: string;
    from: string;
    to: string;
    
    static of(): HttpRequestListener {
        return new HttpRequestListener();
    }

    setTrackingUrl(url: string): void {
        this.trackingUrl = url;
    }

    addUrlRewriting(from: string, to: string) {
        this.from = from;
        this.to = to;
    }

    start() {
        (function (actualOpenFunction, httpRequestListener: HttpRequestListener) {
            // The open method is the first invoked
            // https://xhr.spec.whatwg.org/#the-open()-method
            XMLHttpRequest.prototype.open = function (method: string, url: string, async?: boolean, username?: string | null | undefined, password?: string | null | undefined): void {
                
                this.uuid = uuidv4();
                
                // do something with the method, url and etc.
                console.log(this.uuid+' : An XHR request has been open with the URL '+url);
                
                // example
                if (typeof httpRequestListener.from != 'undefined') {
                    if (url.indexOf(httpRequestListener.from) >= 0) {
                        url = url.replace(httpRequestListener.from, httpRequestListener.to);
                    }
                }

                // Add listener for this request
                this.addEventListener("readystatechange", function () {
                    console.log(this.uuid +' : State: '+this.readyState);
                }, false);
                
                this.addEventListener('load', function () {
                    // do something with the response text
                    console.log(this.uuid + ' : Response: ' + + this.responseText);
                });

                // Call the original
                actualOpenFunction.apply(this, arguments);

            };
        })(XMLHttpRequest.prototype.open, this)
    }


};


