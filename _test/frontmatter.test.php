<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

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

        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();


    }


    /**
     * Test to create a meta
     */
    public function test_frontmatter_meta_setting()
    {

        $pageId = 'frontMatterTest';
        $key = 'whatever';
        $value = "A whatever value";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "' . $key . '":"' . $value . '",' . DOKU_LF
            . '   "description":"whatever"' . DOKU_LF // Description has some processing, we set it to test that they does not come into play
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';
        TestUtility::addPage($pageId, $text, 'Created');

        $metaValue = p_get_metadata($pageId, $key);
        self::assertEquals($value, $metaValue);


    }

    /**
     * Test to create a meta that is not modifiable
     */
    public function test_frontmatter_meta_not_modifiable()
    {

        $pageId = 'frontMatterTest';
        $key = 'user';
        $value = "another user";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "' . $key . '":"' . $value . '"' . DOKU_LF
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';

        $error = null;
        try {
            TestUtility::addPage($pageId, $text, 'Created');
            // trigger a metadata render
            p_get_metadata($pageId, $key);
        } catch (Exception $e) {
            $error = $e;
        }

        $this->assertNotNull($error);
        $message = $error->getMessage();
        $inString =  strpos($message,PluginUtility::$PLUGIN_NAME);
        $this->assertNotFalse($inString, "This is a combo message");


    }

    /**
     * From
     * https://www.dokuwiki.org/devel:unittesting
     */
    function metaGeneratorTest()
    {
        // make a request
        $request = new TestRequest();
        $response = $request->execute();

        // get the generator name from the meta tag.
        $generator = $response->queryHTML('meta[name="generator"]')->attr('content');

        // check the result
        $this->assertEquals('DokuWiki', $generator);
    }


}
