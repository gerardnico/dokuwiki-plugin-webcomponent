<?php
/**
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\Analytics;
use ComboStrap\PluginUtility;
use ComboStrap\SeoUtility;
use ComboStrap\TestUtility;


require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/SeoUtility.php');
require_once(__DIR__ . '/../class/Analytics.php');


/**
 * Class plugin_combo_quality_test
 * Low quality page are handled differently
 */
class plugin_combo_quality_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        TestUtility::setConf(array(
            SeoUtility::CONF_LOW_QUALITY_PAGE_NOT_PUBLIC_ENABLE => 1,
        ));
        parent::setUp();

    }


    function testLowQualityPage()
    {

        /**
         * A common term to the low and high quality
         * page in order to check the result in one query
         */
        $commonTerm = "page";

        /**
         * A high quality page
         */
        $highPageId = "high{$commonTerm}";
        $lowPageId = "low{$commonTerm}";

        /**
         * The high page with a link to a low quality page
         * to check that the link is not showing
         */
        $contentHighQualityPage = "high {$commonTerm} [[:{$lowPageId}]]";
        TestUtility::addPage($highPageId, $contentHighQualityPage);
        SeoUtility::setLowQualityPage($highPageId,false);

        /**
         * The low page with a link to a high quality page
         * to check that the backlinks are not showing the low quality page
         */
        $contentLowQualityPage = "low {$commonTerm} [[:{$highPageId}]]";
        TestUtility::addPage($lowPageId, $contentLowQualityPage);
        SeoUtility::setLowQualityPage($lowPageId,true);

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
        $aclCheck = auth_aclcheck($lowPageId, '', array()) ;
        $this->assertTrue($aclCheck < AUTH_READ);

        /**
         * Render the high quality page as anonymous user
         * The link to the low page should render but
         * with span element
         */
        $render = TestUtility::renderText2Xhtml($contentHighQualityPage);
        $expected = "high page <span data-wiki-id=\":lowpage\">:lowpage</span>";
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($render)
        );
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
     * Create a low pages
     */
    public function testStatsIntegration()
    {
        $contentLowQualityPage = "low page";
        $lowPageId = "integrationLowPage";
        TestUtility::addPage($lowPageId, $contentLowQualityPage);
        Analytics::process($lowPageId);
        $this->assertEquals(true,SeoUtility::isLowQualityPage($lowPageId));

    }


}
