<?php

use ComboStrap\PluginUtility;
use dokuwiki\plugin\config\core\ConfigParser;
use dokuwiki\plugin\config\core\Loader;

require_once(__DIR__ . '/../../combo/class/' . '/PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');


/**
 * Test the edit page
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_edit_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     *
     * Test if we don't have any problem
     * in the file settings.php
     *
     * If there is, we got an error in the admin config page
     */
    public function test_base()
    {

        $pageId = "edit";
        TestUtility::addPage($pageId, "basic Content");
        $request = new TestRequest();
        PluginUtility::runAsAdmin($request);
        $response = $request->get(array('do' => 'edit', 'page' => $pageId), '/doku.php');
        $this->assertNotFalse($response, "No error");


    }


}
