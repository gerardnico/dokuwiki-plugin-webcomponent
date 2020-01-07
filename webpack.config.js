const path = require('path');

// Enter through ./index.ts,
// load all .ts and .tsx files through the ts-loader,
// and output a webcomponent.js file in our current directory.
var config = {
        mode: 'development',
        entry: './js/index.ts',
        devtool: 'inline-source-map',
        output: {
            path: path.resolve(__dirname, 'dist'),
            filename: 'webcomponent.js',
            library: "wco", // The name of the global variable
            libraryTarget: "umd"
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
            ],
        },
        resolve: {
            extensions: ['.tsx', '.ts', '.js'],
        },
        devServer: {
            contentBase: './dist',
        },
        externals: {
            // require("jquery") is external and available
            //  on the global var jQuery
            jquery: "jQuery"
        }
    }
;

module.exports = (env, argv) => {

    if (argv.mode === 'production') {
        config.devtool = 'source-map'
    }

    return config;
};