<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the metadata viewer
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_metaviewer_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     * Test the presence of the box metadata viewer
     */
    public function test_box_is_showing_edit_mode()
    {

        $request = new TestRequest();
        $response = $request->get(array('do'=>'edit','id' => "ApageToEdit"), '/doku.php');
        $box = $response->queryHTML('#'.action_plugin_combo_metaviewer::META_MESSAGE_BOX_ID)->count();
        $this->assertEquals(1,$box,"The box is present");

    }




}
