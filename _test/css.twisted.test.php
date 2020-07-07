<?php

use ComboStrap\PluginUtility;
use ComboStrap\UrlUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/UrlUtility.php');



/**
 * Test the css run
 * This is in an apart script because
 * we set the fact that this is a css file call script via the setUp function
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_css_twisted_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;

        /**
         * {@link DokuWikiTest} set the environment at set up to front
         */
        PluginUtility::setTestProperty(action_plugin_combo_css::END_KEY,action_plugin_combo_css::VALUE_FRONT);
        PluginUtility::setTestProperty("SCRIPT_NAME","css.php");

        PluginUtility::setConf(array(action_plugin_combo_css::CONF_ENABLE_MINIMAL_FRONTEND_STYLESHEET=>1));


        parent::setUp();



    }





    /**
     * Test that css php is running smoothly
     * This run is done as if this was a front end run (ie public)
     * via the variables set in the {@link setUp}
     */
    public function test_css_php()
    {



        $cssFile = DOKU_INC . 'lib/exe/css.php';
        $this->assertEquals(true, file_exists($cssFile));


        /** @noinspection PhpIncludeInspection */
        require_once $cssFile;
        ob_start();
        /**
         * A call as front set in the {@link setUp}
         */
        css_out();
        $output = ob_get_contents();
        ob_end_clean();
        $frontLength = strlen($output);
        $this->assertTrue($frontLength >0,"There is an output of ".$frontLength);


        /**
         * A call as backend
         */
        PluginUtility::setTestProperty(action_plugin_combo_css::END_KEY,action_plugin_combo_css::VALUE_BACK);
        ob_start();
        css_out();
        $output = ob_get_contents();
        ob_end_clean();
        $endLength = strlen($output);
        $this->assertTrue($endLength >0,"There is an output of ".$endLength);
        $this->assertTrue($endLength >$frontLength,"We don't do that for nothing");

        // Before - 141620
        // After - 103711

        PluginUtility::unsetTestProperties();

    }
}
