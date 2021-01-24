<?php

namespace ComboStrap;

use TestRequest;

require_once(__DIR__ . '/../class/HtmlUtility.php');
require_once(__DIR__ . '/../class/XmlUtility.php');

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
     * @param string $namespace - if null change a dokuwiki conf
     */
    public static function setConf($configurations, $namespace = 'plugin')
    {
        $file = dirname(DOKU_CONF) . '/conf/local.php';
        $text = DOKU_LF;
        foreach ($configurations as $key => $value) {
            $text .= '$conf';
            if ($namespace != null) {
                $text .= '[\'' . $namespace . '\'][\'' . PluginUtility::PLUGIN_BASE_NAME . '\']';
            }
            $text .= '[\'' . $key . '\'] = \'' . $value . '\';  ' . DOKU_LF;
        }
        file_put_contents($file, $text, FILE_APPEND);

    }

    /**
     * This function was created to prevent a
     * problem with the function {@link p_read_metadata()} because
     * of the static recursion variable.
     * It means that when all test are run, the {@link p_read_metadata} will not run for the second test
     *
     * This problem is now solved by calling it
     * when creating the page with the function {@link addPage}
     *
     * The old function is still there at {@link getMetaDirect}
     *
     * @param $pageId
     * @param $key
     * @return mixed|string
     */
    public static function getMeta($pageId, $key)
    {
        return p_get_metadata($pageId, $key);
    }

    /**
     * @param TestRequest $request
     */
    public static function becomeSuperUser(&$request)
    {
        global $conf;
        $request->setServer('REMOTE_USER', $conf['superuser']);
    }

    /**
     * @param $expected
     * @return mixed
     */
    public static function normalizeComboXml($expected)
    {
        return XmlUtility::normalize($expected);
    }

    public static function renderText2Xhtml($text)
    {
        return RenderUtility::renderText2Xhtml($text);
    }

    public static function HtmlDiff($expected, $rendered)
    {
        return HtmlUtility::diff($expected, $rendered);
    }

    /**
     * See {@link getMeta}
     *
     * @param $pageId
     * @param $key
     * @return mixed|null
     */
    private static function getMetaDirect($pageId, $key)
    {
        $meta = p_read_metadata($pageId, false);
        $meta = p_render_metadata($pageId, $meta);
        if ($meta == null) {
            // When you make a admin test call, the page ID = start and there is no meta
            return null;
        }
        $value = null;
        if (key_exists($key, $meta['persistent'])) {
            // value may null here also
            $value = $meta['persistent'][$key];
        }
        if ($value != null) {
            return $value;
        } else {
            return $meta['current'][$key];
        }
    }

    /**
     * Add a page to DokuWiki
     *   (save and add it to the index)
     * @param $pageId
     * @param $content
     * @param string $summary - an optional summary for the save/change
     */
    public static function addPage($pageId, $content, $summary = "Test")
    {

        saveWikiText($pageId, $content, $summary);

        /**
         * The static $recursion field of {@link p_get_metadata()}
         * strike again thinking that the test are a metadata recursion
         * avoiding rendering the meta and therefore we got not metadata and no backlinks
         * to avoid this problem, we just call the metadata before the index
         */
        $meta = p_read_metadata($pageId, false);
        $meta = p_render_metadata($pageId, $meta);
        p_save_metadata($pageId, $meta);

        /**
         * Add the page to the index
         */
        idx_addPage($pageId);


    }

    /**
     * Format an HTML in order to be able to compare it
     * @param $text
     * @param bool $keepDokuWikiRootNode - set it to true if your output does not have a root element
     * @return mixed
     */
    static function normalizeDokuWikiHtml($text, $keepDokuWikiRootNode = false)
    {
        /**
         * By default, Dokuwiki instruction wraps the output with a p element
         * See {@link plugin_combo_dokuwiki_test::test_p_tag()}
         */
        if (!$keepDokuWikiRootNode) {
            StringUtility::ltrim($text, "<p>");
            StringUtility::rtrim($text, "</p>");
        }
        $text = str_replace(DOKU_LF.DOKU_LF,DOKU_LF,$text);
        return HtmlUtility::normalize($text);
    }
}
