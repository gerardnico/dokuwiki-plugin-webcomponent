<?php
/**
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;
use ComboStrap\TplConstant;
use dokuwiki\Extension\Event;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');


/**
 * Class plugin_combo_quality_test
 * Low quality page are handled differently
 */
class plugin_combo_quality_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();

    }



    function testLowQualityPage(){

        /**
         * A common term to the low and high quality
         * page in order to check the result in one query
         */
        $commonTerm = "page";

        /**
         * A high quality page
         */
        $highPage = "high{$commonTerm}";
        TestUtility::addPage($highPage,"high {$commonTerm} ");

        /**
         * A low page with a link to a high quality page
         * to check that the backlinks are not showing the low quality page
         */
        $lowPage = "low{$commonTerm}";
        TestUtility::addPage($lowPage, "low {$commonTerm} [[:{$highPage}]]");

        $user = null;
        $groups = array();

        /**
         * Test ACL
         */
        $this->assertEquals(AUTH_NONE, auth_aclcheck($lowPage, $user, $groups));
        $this->assertEquals(AUTH_UPLOAD, auth_aclcheck($highPage, $user, $groups));

        /**
         * Test Full Search
         */
        $regex = array();
        $pageSearch = ft_pageSearch($commonTerm,$regex);
        $this->assertEquals(1,sizeof($pageSearch));
        $this->assertArrayHasKey($highPage, $pageSearch);
        $this->assertArrayNotHasKey($lowPage, $pageSearch);

        /**
         * Test Page Lookup
         */
        $pageLookup = ft_pageLookup($commonTerm);
        $this->assertEquals(1,sizeof($pageLookup));
        $this->assertArrayHasKey($highPage, $pageLookup);
        $this->assertArrayNotHasKey($lowPage, $pageLookup);

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
        while($result = array_shift($searchResult)) {
            $pages[$result['id']]=1;
        }
        $this->assertTrue(sizeof($pages)>1);
        $this->assertArrayHasKey($highPage, $pages);
        $this->assertArrayNotHasKey($lowPage, $pages);

        /**
         * Backlink from a low page for a anonymous user
         * should not be visible
         * (ACL based)
         */
        $ft_backlinks = ft_backlinks($highPage);
        $this->assertEquals(array(), $ft_backlinks);

    }


}
