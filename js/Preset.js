// This file merge the preset for Jest
// You can find it in the jest.configle.js file
// It should stay in CommonJS format
// https://stackoverflow.com/questions/51002460/is-it-possible-to-use-jest-with-multiple-presets-at-the-same-time

const ts_preset = require('ts-jest/jest-preset')
const puppeteer_preset = require('jest-puppeteer/jest-preset')


module.exports = Object.assign(
    ts_preset, 
    puppeteer_preset, 
    {
        globals: {
            test_url: `http://${process.env.HOST || '127.0.0.1'}:${process.env.PORT || 3000}`,
        },
    })
