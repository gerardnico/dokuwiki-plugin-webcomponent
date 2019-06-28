# Test

## Data Directory

The [data](data) folder contains pages and images that are
used for a visual control.

There is a [script called copy_visuals](../copy_visuals.cmd) 
to copy this files from the dokuwiki data directory to this test directory.


## Features

TODO ? Use the visual page to test ?

Example in a setUp function

```php
const DOKU_DATA_DIR = '/dokudata/pages';
const DOKU_CACHE_DIR = '/dokudata/cache';

// Otherwise the page are created in a tmp dir
// ie C:\Users\gerard\AppData\Local\Temp/dwtests-1550072121.2716/data/
// and we cannot visualize them
// This is not on the savedir conf value level because it has no effect on the datadir value
$conf['datadir'] = getcwd() . self::DOKU_DATA_DIR;
// Create the dir
if (!file_exists($conf['datadir'])) {
    mkdir($conf['datadir'], $mode = 0777, $recursive = true);
}
$conf['cachetime'] = -1;
$conf['allowdebug'] = 1; // log in cachedir+debug.log
$conf['cachedir'] = getcwd() . self::DOKU_CACHE_DIR;
if (!file_exists($conf['cachedir'])) {
    mkdir($conf['cachedir'], $mode = 0777, $recursive = true);
}
```