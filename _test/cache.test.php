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

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;
use dokuwiki\Cache\CacheInstructions;
use dokuwiki\Cache\CacheRenderer;

/**
 *
 * @group plugin_combo
 * @group plugins
 */


require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'AdsUtility.php');


/**
 *
 * Class plugin_combo_cache_test
 *
 * Test the {@link action_plugin_combo_cachebursting} class
 *
 * Test case can be seen in
 *   * cache_use.test.php
 *   * cache_stalecheck.test.php
 */
class plugin_combo_cache_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();

    }

    public function test_cache()
    {

        $namespace = "ns";
        $id = $namespace.':sidebar';
        $file = wikiFN($id);
        $conf['cachetime'] = 0;  // ensure the value is not -1, which disables caching

        saveWikiText($id, 'Content',"summary");

        /**
         * $cache->cache is the file
         */
        $renderCache = new CacheRenderer($id, $file, 'xhtml');
        $instructionsCache = new CacheInstructions($id, $file);

        /**
         * There is no cache before rendering
         */
        $this->assertEquals(false, file_exists($renderCache->cache));
        $this->assertEquals(false, file_exists($instructionsCache->cache));

        /**
         * Rendering create the cache
         */
        $testRequest = new TestRequest();
        $testRequest->get(array("id"=>$id));

        /**
         * The cache was created
         */
        $this->assertEquals(true, file_exists($renderCache->cache));
        $this->assertEquals(true, file_exists($instructionsCache->cache));

        /**
         * Save a page in the same namespace and
         * there is no cache for the sidebar
         */
        $otherIdSameNs = $namespace.':otherpage';
        saveWikiText($otherIdSameNs, 'Content',"summary");

        $this->assertEquals(false, file_exists($renderCache->cache));
        $this->assertEquals(false, file_exists($instructionsCache->cache));

    }


}
