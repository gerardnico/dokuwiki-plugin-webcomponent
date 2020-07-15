<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;
use ComboStrap\UrlUtility;

require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');



/**
 * Test the test utility class
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_test_utility_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;

        TestUtility::setConf(array(self::KEY_TEST => self::VALUE_TEST));
        parent::setUp();

    }




    /**
     * Value used in the test {@link test_conf_in_test_conf_file}
     */
    const VALUE_TEST = "valueTest";
    const KEY_TEST = "keyTest";

    /**
     * Test the utility function to add configuration value
     * in the copied test local.php file
     * The call is made in the {@link setUp}
     */
    public function test_conf_in_test_conf_file(){

        $file = dirname(DOKU_CONF).'/conf/local.php';
        $this->assertTrue(file_exists($file));

        global $conf;
        $value = $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][self::KEY_TEST];
        $this->assertEquals(self::VALUE_TEST,$value,"The configuration should have been set");

    }

    /**
     * Add a query string to make a difference between public and private
     */
    public function test_css_query_string()
    {
        global $conf;
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][action_plugin_combo_css::CONF_ENABLE_MINIMAL_FRONTEND_STYLESHEET]=1;

        $pageId = "cssQueryStringPage";
        TestUtility::addPage($pageId, "A page that should exist to be able to make a query", "Summary");
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId),"/doku.php");
        $cssHrefAttribute = $testResponse->queryHTML('link[href*="css.php"]' )->attr('href');
        $endKeyValue =  UrlUtility::getPropertyValue($cssHrefAttribute, action_plugin_combo_css::END_KEY);
        $this->assertEquals(action_plugin_combo_css::VALUE_FRONT, $endKeyValue);

    }

    /**
     * Disable the dokuwiki stylesheet
     */
    public function test_css_disable_dokuwiki_stylesheet()
    {

        $pageId = "cssDisableDokuwikiStyleSheet";
        TestUtility::addPage($pageId, "A page that should exist to be able to make a query", "Summary");
        global $conf;
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][action_plugin_combo_css::CONF_DISABLE_DOKUWIKI_STYLESHEET]=1;

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId),"/doku.php");
        $dokuwikiStyleSheetCount = $testResponse->queryHTML('link[href*="css.php"]' )->count();
        $this->assertEquals(0, $dokuwikiStyleSheetCount,"The dokuwiki stylesheet was not included");

    }




}
