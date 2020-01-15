port = 8082;

module.exports = {
    // server: {
    //     command: `cross-env NODE_ENV=development webpack-dev-server --progress -d --inline --open --port ${port}`,
    //     port: port,
    //     launchTimeout: 30000
    // },
    launch: {
        headless: false, // for debug:  to see what the browser is displaying
        devtools: true,
        timeout: 30000,
        slowMo: 250, // slow down by 250ms
        dumpio: true // Whether to pipe the browser process stdout and stderr 
    }
}