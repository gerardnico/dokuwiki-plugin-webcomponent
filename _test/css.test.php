<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;
use ComboStrap\UrlUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/UrlUtility.php');



/**
 * Test the CSS component plugin
 * There is also a second file (see css.twisted.php)
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_css_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;


        parent::setUp();
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
