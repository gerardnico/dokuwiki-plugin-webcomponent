<?php
/**
 * Integration Tests for the handling of the canonical
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;
use ComboStrap\Sqlite;
use ComboStrap\TestUtility;
use ComboStrap\UrlCanonical;

require_once(__DIR__ . '/../class/UrlCanonical.php');

class plugin_combo_canonical_test extends DokuWikiTest
{

    // Needed otherwise the plugin is not enabled
    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'sqlite';
        parent::setUp();
    }


    /**
     * Test a internal canonical rewrite redirect
     *
     */
    public function test_canonical()
    {

        $urlCanonicalManager = new UrlCanonical(Sqlite::getSqlite());

        // Data
        $pageId = "web:javascript:variable";
        $newPageId = "lang:javascript:variable";
        $pageCanonical = "javascript:variable";


        // Reproducible test
        if ($urlCanonicalManager->pageExist($pageId)) {
            $urlCanonicalManager->deletePage($pageId);
        }

        if ($urlCanonicalManager->pageExist($newPageId)) {
            $urlCanonicalManager->deletePage($newPageId);
        }

        $this->assertEquals(0, $urlCanonicalManager->pageExist($pageId), "The page was deleted");

        // Save a page
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "canonical":"' . $pageCanonical . '"' . DOKU_LF
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';
        TestUtility::addPage($pageId, $text);

        // In a request
        $request = new TestRequest();
        $request->get(array('id' => $pageId), '/doku.php');

        $this->assertEquals(1, $urlCanonicalManager->pageExist($pageId), "The page was added to the table");

        // Page move
        TestUtility::addPage($pageId, "", 'Page deletion');
        $this->assertEquals(false, page_exists($pageId), "The old page does not exist on disk");
        TestUtility::addPage($newPageId, $text, 'Page creation');

        // A request
        $request = new TestRequest();
        $request->get(array('id' => $newPageId), '/doku.php');

        $this->assertEquals(0, $urlCanonicalManager->pageExist($pageId), "The old page does not exist in db");
        $this->assertEquals(1, $urlCanonicalManager->pageExist($newPageId), "The new page exist");
        $pageRow = $urlCanonicalManager->getPage($newPageId);
        $this->assertEquals($pageCanonical, $pageRow['CANONICAL'], "The canonical is the same");


    }

    /**
     * Test the canonical
     * Actually it just add the og
     * When the rendering of the canonical value will be supported by
     * 404 manager, we can switch
     */
    public function test_canonical_meta()
    {

        $metaKey = UrlCanonical::CANONICAL_PROPERTY;
        $pageId = 'description:test';
        $canonicalValue = "javascript:variable";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "' . $metaKey . '":"' . $canonicalValue . '"' . DOKU_LF
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';
        TestUtility::addPage($pageId, $text, 'Created');

        $canonicalMeta = p_get_metadata($pageId, $metaKey, METADATA_RENDER_UNLIMITED);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($canonicalValue, $canonicalMeta);

        // It should never occur but yeah
        $canonicalValue = "js:variable";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "' . $metaKey . '":"' . $canonicalValue . '"' . DOKU_LF
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';
        TestUtility::addPage($pageId, $text, 'Updated meta');
        $canonicalMeta = p_get_metadata($pageId, $metaKey, METADATA_RENDER_UNLIMITED);
        $this->assertEquals($canonicalValue, $canonicalMeta);

        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' => $pageId), '/doku.php');


        // Query
        $canonicalHrefLink = $response->queryHTML('link[rel="' . $metaKey . '"]')->attr('href');
        $canonicalId = UrlCanonical::toDokuWikiId($canonicalHrefLink);
        $this->assertEquals($canonicalValue, $canonicalId, "The link canonical meta should be good");
        // Facebook: https://developers.facebook.com/docs/sharing/webmasters/getting-started/versioned-link/
        $canonicalHrefMetaOg = $response->queryHTML('meta[property="og:url"]')->attr('content');
        $this->assertEquals($canonicalHrefLink, $canonicalHrefMetaOg, "The meta canonical property should be good");


    }

    /**
     * Test the automatic canonical function
     *
     */
    public function test_canonical_meta_auto()
    {

        $canonicalKey = UrlCanonical::CANONICAL_PROPERTY;
        $canonicalValue = 'without:canonical';
        $pageId = 'page:' . $canonicalValue . '';
        TestUtility::addPage($pageId, "Non empty", 'Created');

        $canonicalMeta = p_get_metadata($pageId, $canonicalKey, METADATA_RENDER_UNLIMITED);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals("", $canonicalMeta);

        /**
         * By default no automatic canonical
         */
        $request = new TestRequest(); // initialize the request
        $request->get(array('id' => $pageId), '/doku.php');
        $canonicalMeta = p_get_metadata($pageId, $canonicalKey, METADATA_RENDER_UNLIMITED);
        $this->assertEquals("", $canonicalMeta,"No canonical in the meta when automatic canonical is off");
        $urlCanonical = new UrlCanonical(Sqlite::getSqlite());
        $page = $urlCanonical->getPage($pageId);
        $this->assertEquals("", $page[$canonicalKey],"No canonical value in the database either");

        /**
         * Set it on, a canonical should have been created
         */
        global $conf;
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][action_plugin_combo_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF] = 2;
        $request = new TestRequest(); // initialize the request
        $request->get(array('id' => $pageId), '/doku.php');
        $canonicalMeta = p_get_metadata($pageId, $canonicalKey, METADATA_RENDER_UNLIMITED);
        $this->assertEquals($canonicalValue, $canonicalMeta,"With auto canonical, a canonical is set");

        /**
         * If the last name is a start page ie
         * web:start
         * the canonical should be
         * web
         */
        $conf['start'] = 'start';
        $startPageCanonical = "web";
        $startPageId = $startPageCanonical . ':start';
        TestUtility::addPage($startPageId, "Non empty", 'Created');
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][action_plugin_combo_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF] = 2;
        $request = new TestRequest(); // initialize the request
        $request->get(array('id' => $startPageId), '/doku.php');
        $canonicalMeta = p_get_metadata($startPageId, $canonicalKey, METADATA_RENDER_UNLIMITED);
        $this->assertEquals($startPageCanonical, $canonicalMeta,"A home page of a namespace does not include the start page in its name");

        /**
         * If the page has already a canonical don't change it
         */
        $conf['start'] = 'start';
        $pageIdWithCanonical = "data:modeling:identifier";
        $canonical = "acanonical";
        p_set_metadata($pageIdWithCanonical, array($canonicalKey => $canonical));
        TestUtility::addPage($pageIdWithCanonical, "Non empty", 'Created');
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][action_plugin_combo_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF] = 2;
        $request = new TestRequest(); // initialize the request
        $request->get(array('id' => $pageIdWithCanonical), '/doku.php');
        $canonicalMeta = p_get_metadata($pageIdWithCanonical, $canonicalKey, METADATA_RENDER_UNLIMITED);
        $this->assertEquals($canonical, $canonicalMeta,"A page with a canonical does not see its canonical change");

    }

    /**
     * A canonical cannot be uppercase
     *
     */
    public function test_canonical_case()
    {

        // Save a page
        $pageCanonical = "Hallo";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "canonical":"' . $pageCanonical . '"' . DOKU_LF
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';
        $pageId = "testcano";
        TestUtility::addPage($pageId, $text);


        $canonicalMeta = p_get_metadata($pageId, UrlCanonical::CANONICAL_PROPERTY, METADATA_RENDER_UNLIMITED);
        $this->assertEquals(strtolower($pageCanonical), $canonicalMeta,"The canonical should be lowercase");



    }


}
