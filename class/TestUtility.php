<?php

namespace ComboStrap;

/**
 * Class TestUtility
 * @package ComboStrap
 * A class with Test utility static function
 */
class TestUtility
{

    /**
     * This function can be added in a setUp function of a test that creates pages
     * in order to get the created pages in the dokuwiki and not in a temp space
     * in order to be able to visualise them
     */
    public static function setUpPagesLocation()
    {
        // Otherwise the page are created in a tmp dir
        // ie C:\Users\gerard\AppData\Local\Temp/dwtests-1550072121.2716/data/
        // and we cannot visualize them
        // This is not on the savedir conf value level because it has no effect on the datadir value
        $conf['datadir'] = getcwd() . PluginUtility::DOKU_DATA_DIR;
        // Create the dir
        if (!file_exists($conf['datadir'])) {
            mkdir($conf['datadir'], $mode = 0777, $recursive = true);
        }
        $conf['cachetime'] = -1;
        $conf['allowdebug'] = 1; // log in cachedir+debug.log
        $conf['cachedir'] = getcwd() . PluginUtility::DOKU_CACHE_DIR;
        if (!file_exists($conf['cachedir'])) {
            mkdir($conf['cachedir'], $mode = 0777, $recursive = true);
        }
    }
}
