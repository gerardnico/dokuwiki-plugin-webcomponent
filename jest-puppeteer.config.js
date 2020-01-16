port = 8082;

module.exports = {
    // server: {
    //     command: `cross-env NODE_ENV=development webpack-dev-server --progress -d --inline --open --port ${port}`,
    //     port: port,
    //     launchTimeout: 30000
    // },
    launch: {
        // headless: false, // for debug:  to see what the browser is displaying
        // slowMo: 250, // slow down by 250ms
        devtools: true, // This lets you debug code in the application code browser
        timeout: 30000,
        dumpio: true // Whether to pipe the browser process stdout and stderr 
    }
}