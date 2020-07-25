<?php
/**
 * Integration Tests for the handling of the canonical
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;
use ComboStrap\UrlCanonical;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');



class plugin_combo_renderer_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
        $conf['renderer_xhtml'] = renderer_plugin_combo_renderer::COMBO_RENDERER_NAME;
    }


    /**
     * Test a internal canonical rewrite redirect
     *
     */
    public function test_no_toc()
    {

        $pageId = "renderer";


        // Save a page
        $text = '===== h1 ====='.DOKU_LF;
        $text .= '==== h2 ===='.DOKU_LF;
        TestUtility::addPage($pageId, $text, 'Page creation');

        // In a request
        $request = new TestRequest();
        $request->get(array('id' => $pageId), '/doku.php');



    }




}
