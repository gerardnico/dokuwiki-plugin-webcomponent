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

    /**
     * Set a test variable in the global scope
     * on the global array COMBO
     * @param $name
     * @param $value
     * Test property are used to set a test variable
     * in order to test ancillary doku script such as css.php/js.php
     * because the dokuwiki framework does not allow a request on them
     */
    public static function setTestProperty($name, $value)
    {
        $GLOBALS["COMBO"][$name] = $value;
    }

    /**
     * Delete all test properties
     */
    public static function unsetTestProperties()
    {
        unset($GLOBALS["COMBO"]);
    }

    /**
     * Register function are called with the configuration
     * that are copied for each test
     *
     * This function makes it easier to set a couple of configuration
     * in the setUp of test before the parent:setUp
     *
     * @param $configurations - an array of configuration
     *
     */
    public static function setConf($configurations)
    {
        $file = dirname(DOKU_CONF) . '/conf/local.php';
        $text = DOKU_LF;
        foreach ($configurations as $key => $value) {
            $text .= '$conf[\'plugin\'][\'' . PluginUtility::$PLUGIN_BASE_NAME . '\'][\'' . $key . '\'] = \'' . $value . '\';  ' . DOKU_LF;
        }
        file_put_contents($file, $text, FILE_APPEND);

    }

    /**
     * @param $pageId
     * @param $key
     * The {@link p_read_metadata()} use a static variable
     * to prevent recursive call.
     * It means that when all test are run, the {@link p_read_metadata} will not run for the second test
     * This function helps with that

     */
    public static function getMeta($pageId, $key)
    {
        $meta = p_read_metadata($pageId,false);
        $meta = p_render_metadata($pageId, $meta);
        if (key_exists($key,$meta['persistent'])) {
            return $meta['persistent'][$key];
        } else {
            return $meta['current'][$key];
        }


    }
}
