<?php

use ComboStrap\MetadataUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

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
        $box = $response->queryHTML('#'. MetadataUtility::META_MESSAGE_BOX_ID)->count();
        $this->assertEquals(1,$box,"The box is present");

        global $conf;
        $conf['plugin'][PluginUtility::$PLUGIN_BASE_NAME][MetadataUtility::CONF_ENABLE_WHEN_EDITING]=0;
        $request = new TestRequest();
        $response = $request->get(array('do'=>'edit','id' => "ApageToEdit"), '/doku.php');
        $box = $response->queryHTML('#'. MetadataUtility::META_MESSAGE_BOX_ID)->count();
        $this->assertEquals(0,$box,"The box is not present");

    }

    /**
     * Test the presence of the box metadata
     */
    public function test_box_is_showing_metadata_tag()
    {

        $pageId="metadataViewer";
        TestUtility::addPage($pageId, MetadataUtility::TAG, "Summary");
        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId), '/doku.php');
        $box = $response->queryHTML('#'. MetadataUtility::META_MESSAGE_BOX_ID)->count();
        $this->assertEquals(0,$box,"The box is not present");

    }




}
