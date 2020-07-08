<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the description meta
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_description_test extends DokuWikiTest
{


    public function setUp()
    {

        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;


        parent::setUp();



    }



    /**
     * Test the description
     */
    public function test_description()
    {

        $pageId = 'description_test';
        $description = "Go see my beautiful website";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "description":"'.$description.'"' . DOKU_LF
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Created');

        $descriptionMeta = p_get_metadata($pageId, 'description', METADATA_RENDER_UNLIMITED);
        self::assertEquals($description, $descriptionMeta['abstract']);

        $description = "Go see my super beautiful website";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "description":"'.$description.'"' . DOKU_LF
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Updated meta');
        $descriptionMeta = p_get_metadata($pageId, 'description', METADATA_RENDER_UNLIMITED);
        self::assertEquals($description, $descriptionMeta['abstract'],"The description should have been saved");

        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');
        $metaDescription = $response->queryHTML('meta[name="description"]')->attr('content');
        $this->assertEquals($description, $metaDescription,"The description cannot be seen in the page");

    }




}
