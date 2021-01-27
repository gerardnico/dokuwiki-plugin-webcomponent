<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

/**
 *
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\AdsUtility;
use ComboStrap\PluginUtility;


require_once(__DIR__ . "/TestUtility.php");

require_once(__DIR__ . '/../../combo/class/'. "PluginUtility.php");
require_once(__DIR__ . '/../../combo/class/'."AdsUtility.php");


class plugin_combo_ads_test extends DokuWikiTest
{


    const PAGE_WITH_ENOUGH_CONTENT_FOR_INARTICLE_AD = '====== Title ======' . DOKU_LF
    . "===== About =====" . DOKU_LF
    . "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. \\\\ " . DOKU_LF
    . "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure \\\\ dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum." . DOKU_LF
    . "===== Second =====" . DOKU_LF
    . "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. \\\\" . DOKU_LF
    . "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure \\\\" . DOKU_LF
    . "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. \\\\" . DOKU_LF
    . "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. \\\\" . DOKU_LF
    . "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. " . DOKU_LF;

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        global $conf;
        parent::setUp();
        $conf['renderer_xhtml'] = renderer_plugin_combo_renderer::COMBO_RENDERER_NAME;

    }


    /**
     * Test a ad tag
     *
     */
    public function test_ad_tag_basic()
    {

        // Save a page
        $adPageContent = 'AdsPage';
        $adName = "sideBar";
        $adId = strtolower(AdsUtility::ADS_NAMESPACE . $adName);
        TestUtility::addPage($adId, $adPageContent);

        $page = '<ad name="' . $adName . '"/>';
        $html = TestUtility::renderText2Xhtml($page);
        $tagId = AdsUtility::getTagId($adName);
        $this->assertEquals("<div id=\"$tagId\">$adPageContent</div>", $html);


    }


    public function test_ad_placeholder()
    {

        // Save a page
        $articleContent = self::PAGE_WITH_ENOUGH_CONTENT_FOR_INARTICLE_AD;

        $articleId = "inarticle-test-placeholder";
        TestUtility::addPage($articleId, $articleContent);

        // No ad page
        global $conf;
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][AdsUtility::CONF_IN_ARTICLE_PLACEHOLDER]=1;

        $name = "inarticle1";
        $adPage = AdsUtility::getAdPage($name);
        $this->assertFalse(page_exists($adPage),"The page ad should not exist");

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array("id" => $articleId));

        $ad = $testResponse->queryHTML("#" . AdsUtility::getTagId($name));

        $this->assertEquals(1, $ad->count(), "The ad page does not exist but a placeholder is shown");
        $this->assertTrue(strpos($ad->text(),"placeholder")>0, "The ad page does not exist but a placeholder is shown");

    }

    /**
     * Test a ad tag
     *
     */
    public function test_in_article_ad_no_ad_page()
    {


        $adName = "inarticle1";

        // Save a page
        $articleContent = self::PAGE_WITH_ENOUGH_CONTENT_FOR_INARTICLE_AD;

        $articleId = "inarticle-test-ad";
        TestUtility::addPage($articleId, $articleContent);

        // No ad page
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array("id" => $articleId));
        $adCount = $testResponse->queryHTML("#" . AdsUtility::getTagId($adName))->count();
        $this->assertEquals(0, $adCount, "The ad page does not exist, there is therefore no ads");




    }

    /**
     * Test a ad tag
     *
     */
    public function test_in_article_ad_base()
    {


        $adName = "inarticle1";
        $adId = strtolower(AdsUtility::ADS_NAMESPACE . $adName);

        // Save a page
        $articleContent = self::PAGE_WITH_ENOUGH_CONTENT_FOR_INARTICLE_AD;

        $articleId = "inarticle-test-ad-base";
        TestUtility::addPage($articleId, $articleContent);


        // Ad the ad page
        TestUtility::addPage($adId, "Ad");
        $this->assertEquals(true, page_exists($adId),"The page exists");
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array("id" => $articleId));
        $adCount = $testResponse->queryHTML("#" . AdsUtility::getTagId($adName))->count();
        $this->assertEquals(1, $adCount, "The ad page does exist, there is therefore 1 ads");


    }




    public function test_tag_id()
    {
        $id = AdsUtility::getTagId("bla");
        $this->assertEquals("combostrap-ads-bla",$id,"The id should be correct");

    }


}
