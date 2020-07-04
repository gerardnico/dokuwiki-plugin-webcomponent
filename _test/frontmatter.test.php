<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the front matter component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_frontmatter_test extends DokuWikiTest
{


    public function setUp()
    {

        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'sqlite';

        global $conf;

        parent::setUp();

        // To get nice url
        // https://www.dokuwiki.org/config:userewrite
        $conf['userewrite']= 1;
        // https://www.dokuwiki.org/config:useslash
        $conf['useslash']= 1;

    }



    /**
     * Test the description
     */
    public function test_frontmatter_description()
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
        self::assertEquals($description, $descriptionMeta['abstract']);

        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');
        $metaDescription = $response->queryHTML('meta[name="description"]')->attr('content');
        $this->assertEquals($description, $metaDescription);
    }

    /**
     * From
     * https://www.dokuwiki.org/devel:unittesting
     */
    function metaGeneratorTest() {
        // make a request
        $request = new TestRequest();
        $response = $request->execute();

        // get the generator name from the meta tag.
        $generator = $response->queryHTML('meta[name="generator"]')->attr('content');

        // check the result
        $this->assertEquals('DokuWiki', $generator);
    }


}
