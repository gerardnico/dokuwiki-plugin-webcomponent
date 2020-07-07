<?php

use ComboStrap\PluginUtility;
use ComboStrap\UrlUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/UrlUtility.php');



/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_css_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;


        parent::setUp();
    }




    /**
     * Add a query string to make a difference between public and private
     */
    public function test_css_query_string()
    {

        $pageId = "cssQueryStringPage";
        saveWikiText($pageId, "A page that should exist to be able to make a query", "Summary");
        idx_addPage($pageId);
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId),"/doku.php");
        $cssHrefAttribute = $testResponse->queryHTML('link[href*="css.php"]' )->attr('href');
        $endKeyValue =  UrlUtility::getPropertyValue($cssHrefAttribute, action_plugin_combo_css::END_KEY);
        $this->assertEquals(action_plugin_combo_css::VALUE_FRONT, $endKeyValue);


    }




}
