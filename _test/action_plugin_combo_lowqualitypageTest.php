<?php
/**
 * Copyright (c) 2021. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

use ComboStrap\Analytics;
use ComboStrap\PluginUtility;
use ComboStrap\LowQualityPage;
use ComboStrap\TestUtility;


require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/LowQualityPage.php');
require_once(__DIR__ . '/../class/Analytics.php');


/**
 * Class plugin_combo_quality_test
 * Low quality page are handled differently
 */
class action_plugin_combo_lowqualitypageTest extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        TestUtility::setConf(array(
            LowQualityPage::CONF_LOW_QUALITY_PAGE_PROTECTION_ENABLE => 1,
        ));
        parent::setUp();

    }


    /**
     * Test the low quality page functionality
     * without the run of the {@link renderer_plugin_combo_analytics}
     * that set the low page indicator
     */
    function testLowQualityPage()
    {

        /**
         * A common term to the low and high quality
         * page in order to check the result in one query
         */
        $commonTerm = "page";

        /**
         * Pages
         */
        $highPageId = "high{$commonTerm}";
        $lowPageId = "low{$commonTerm}";

        /**
         * The high page with a link to a low quality page
         * to check that the link is not showing
         */
        $contentHighQualityPage = "high {$commonTerm} [[:{$lowPageId}]]";
        TestUtility::addPage($highPageId, $contentHighQualityPage);
        LowQualityPage::setLowQualityPage($highPageId, false);

        /**
         * The low page with a link to a high quality page
         * to check that the backlinks are not showing the low quality page
         */
        $contentLowQualityPage = "low {$commonTerm} [[:{$highPageId}]]";
        TestUtility::addPage($lowPageId, $contentLowQualityPage);
        LowQualityPage::setLowQualityPage($lowPageId, true);

        $user = null;
        $groups = array();

        /**
         * Test ACL
         */
        $this->assertEquals(AUTH_NONE, auth_aclcheck($lowPageId, $user, $groups));
        $this->assertEquals(AUTH_UPLOAD, auth_aclcheck($highPageId, $user, $groups));

        /**
         * Test Full Search
         */
        $regex = array();
        $pageSearch = ft_pageSearch($commonTerm, $regex);
        $this->assertEquals(1, sizeof($pageSearch));
        $this->assertArrayHasKey($highPageId, $pageSearch);
        $this->assertArrayNotHasKey($lowPageId, $pageSearch);

        /**
         * Test Page Lookup
         */
        $pageLookup = ft_pageLookup($commonTerm);
        $this->assertEquals(1, sizeof($pageLookup));
        $this->assertArrayHasKey($highPageId, $pageLookup);
        $this->assertArrayNotHasKey($lowPageId, $pageLookup);

        /**
         * Search function, check the ACL
         * The user passed is the empty string and not null
         * A low page should not show
         *
         */
        global $conf;
        $searchResult = array();
        $search_opts = array(
            'depth' => 1,
            'pagesonly' => true,
            'listfiles' => true,
            'listdirs' => true,
            'firsthead' => true
        );
        search($searchResult, // The returned data
            $conf['datadir'], // The root
            'search_universal', // The recursive function (callback)
            $search_opts, // The options given to the recursive function
            '', // The current directory
            $lvl = 1 // Only one level in the tree
        );
        // extract the pages
        $pages = array();
        while ($result = array_shift($searchResult)) {
            $pages[$result['id']] = 1;
        }
        $this->assertTrue(sizeof($pages) > 1);
        $this->assertArrayHasKey($highPageId, $pages);
        $this->assertArrayNotHasKey($lowPageId, $pages);

        /**
         * Backlink from a low page for a anonymous user
         * should not be visible
         * (ACL based)
         */
        $ft_backlinks = ft_backlinks($highPageId);
        $this->assertEquals(array(), $ft_backlinks);

        /**
         * Rss / DokuWiki Change Log Test
         *
         * The feed {@link rssRecentChanges()}
         * http://localhost:81/feed.php?type=rss2&num=5
         * use the {@link getRecents()}
         *
         * We cannot test {@link rssRecentChanges()}
         * because we cannot mock a RSS request
         * Therefore we test the log function
         * {@link getRecents()}
         */
        $opt = array();
        $opt['items'] = 5;
        $opt['namespace'] = '';
        $flags = 0;
        // This function is used in the RSS
        $recents = getRecents(0, $opt['items'], $opt['namespace'], $flags);
        // extract the pages
        $recentPages = array();
        while ($result = array_shift($recents)) {
            $recentPages[$result['id']] = 1;
        }
        $this->assertTrue(sizeof($recentPages) >= 1);
        $this->assertArrayHasKey($highPageId, $recentPages);
        $this->assertArrayNotHasKey($lowPageId, $recentPages);

        /**
         * A low quality page should not leak in the Sitemap
         * /var/www/html/data/cache/sitemap.xml.gz
         * https://www.dokuwiki.org/sitemap
         * The file is SiteMap.php that uses the class {@link Mapper}
         * The creation of the page is ACL based,
         * there is no method to extract and test the pages
         * we test the acl check signature used in the function {@link Mapper::generate()}
         */
        $aclCheck = auth_aclcheck($lowPageId, '', array());
        $this->assertTrue($aclCheck < AUTH_READ);

        /**
         * Render the high quality page as anonymous user
         * The link to the low page should render but
         * with span element
         */
        $render = TestUtility::renderText2Xhtml($contentHighQualityPage);
        $expected = "high page <a href=\"#\" class=\"low-quality\" data-wiki-id=\":lowpage\" data-toggle=\"tooltip\" title=\"To follow this link, you need to log in (".LowQualityPage::ACRONYM.")\">:lowpage</a>";
        $this->assertEquals( "",TestUtility::HtmlDiff($expected,$render));
        /**
         * Render the low quality page as anonymous user
         * The link should render with anchor
         */
        $render = TestUtility::renderText2Xhtml($contentLowQualityPage);
        $expected = "low page <a href=\"/./doku.php?id=highpage\" class=\"\" title=\"highpage\" data-wiki-id=\"highpage\">highpage</a>";
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($render)
        );




    }

    /**
     * Test the integration with the renderer
     */
    public function testStatsIntegration()
    {
        /**
         * Neighbors page of the low quality page
         */
        $neighborPage = "adjacent_page";
        TestUtility::addPage($neighborPage, "Content");

        /**
         * Low quality page
         */
        $contentLowQualityPage = "low page [[${neighborPage}]] [[?do=action]] [[broken]]";
        $lowPageId = "integration_low_page";
        TestUtility::addPage($lowPageId, $contentLowQualityPage);

        /**
         * Start the stat
         */
        $stats = Analytics::getDataAsArray($lowPageId);

        /**
         * Test
         */
        $this->assertEquals(true, LowQualityPage::isLowQualityPage($lowPageId));
        $this->assertEquals("internal links",3, $stats[Analytics::STATISTICS][Analytics::INTERNAL_LINKS_COUNT]);
        $this->assertEquals("broken links",1, $stats[Analytics::STATISTICS][Analytics::INTERNAL_LINKS_BROKEN_COUNT]);


    }


}
