<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

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
        global $conf;
        $conf['template'] = 'strap';
        $conf['useheading'] = 1;

        $metaTitleValue = "Title Meta - Go see my beautiful website";
        $titleHeading = "Title Heading - Hallo";
        $metaTitleKey = 'title';

        $pageId = 'title_test_id';

        $pageContent = "====== {$titleHeading} =====";
        saveWikiText($pageId, $pageContent, 'Created');

        // Test meta
        $titleMeta = TestUtility::getMeta($pageId,$metaTitleKey);
        $this->assertEquals($titleHeading, $titleMeta, "Title test 1 - The default meta should be the h1 heading");

        // Test html title
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');
        $titlePage = $response->queryHTML('title')->text();
        $this->assertEquals($titleHeading, $titlePage,"Title test 2 - The title should be the h1 heading");

        // Add the front matter
        $frontMatter = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "' . $metaTitleKey . '":"' .$metaTitleValue.'",' . DOKU_LF
            . '   "description":"'.$metaTitleValue.'"' . DOKU_LF // Set the desc to test side effect
            . '}' .DOKU_LF
            . '---'.DOKU_LF;
        saveWikiText($pageId, $frontMatter.$pageContent, 'Add frontmatter');

        // Test
        $titleMeta = p_get_metadata($pageId, $metaTitleKey);
        $this->assertEquals($metaTitleValue, $titleMeta,"Title test 3 - The title  meta should be present");

        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');
        $titlePage = $response->queryHTML('title')->text();
        $this->assertEquals($metaTitleValue, $titlePage,"Title test 4 - The title should be the meta title");

        // Resave without frontmatter
        $emptyFrontMatter = DOKU_LF . '---json' . DOKU_LF
            . '---'.DOKU_LF;
        saveWikiText($pageId, $emptyFrontMatter.$pageContent, 'Add frontmatter');
        $titleMeta = p_get_metadata($pageId, $metaTitleKey);
        $this->assertEquals($titleHeading, $titleMeta, "Title test 5 - The default meta should be the h1 heading");

    }




}
