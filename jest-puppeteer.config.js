
jest_puppeteer_conf = {
    launch: {
        timeout: 30000,
        dumpio: true // Whether to pipe the browser process stdout and stderr 
    }
}

var debug = (typeof v8debug === 'object');
if (debug) {
    jest_puppeteer_conf.launch.headless = false; // for debug:  to see what the browser is displaying
    jest_puppeteer_conf.launch.slowMo = 250;  // slow down by 250ms for each step
    jest_puppeteer_conf.launch.devtools = true; // This lets you debug code in the application code browser
}


if (require('ci-info').isCI) {
    jest_puppeteer_conf.server = {
        command: `webpack-dev-server --progress -d --inline --open --port ${port}`,
        port: port,
        launchTimeout: 30000

    };
}

module.exports = jest_puppeteer_conf;
