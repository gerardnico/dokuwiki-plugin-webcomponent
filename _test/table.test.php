<?php
/**
 * Integration Tests for the handling of the canonical
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;

use ComboStrap\UrlCanonical;

require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');



class plugin_combo_table_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        global $conf;
        parent::setUp();
        $conf['renderer_xhtml'] = 'combo_renderer';

    }


    /**
     * Test the {@link \ComboStrap\TableUtility::tableOpen()}
     * function
     *
     */
    public function test_table()
    {

        // Save a page with a table
        $text = '^ Header ^ Header ^' . DOKU_LF
            . '| Value 1 | Value 2 |' . DOKU_LF;
        $pageId ="table";
        TestUtility::addPage($pageId, $text, 'Page creation');

        // In a request
        $request = new TestRequest();
        $response =  $request->get(array('id' => $pageId), '/doku.php');

        $count = $response->queryHTML(".table-responsive")->count();
        $this->assertEquals(1,$count,"The table should be responsive");

    }





}
