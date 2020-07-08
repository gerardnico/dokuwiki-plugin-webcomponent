<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the title meta
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_meta_title_test extends DokuWikiTest
{


    public function setUp()
    {

        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;


        parent::setUp();



    }



    /**
     * Test the description
     */
    public function test_title()
    {

        $pageId = 'description_test';
        $titleValue = "Go see my beautiful website";
        $titleKey = 'title';
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "' . $titleKey . '":"' .$titleValue.'",' . DOKU_LF
            . '   "description":"'.$titleValue.'"' . DOKU_LF // Set the desc to test side effect
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Created');

        $titleMeta = p_get_metadata($pageId, $titleKey);
        self::assertEquals($titleValue, $titleMeta);


        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');
        $titlePage = $response->queryHTML('title')->text();
        $this->assertEquals($titleValue, $titlePage,"The title should be the good one");

    }




}
