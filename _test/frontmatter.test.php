<?php

use ComboStrap\Auth;
use ComboStrap\PluginUtility;
use ComboStrap\StringUtility;

require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/' . 'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/' . 'StringUtility.php');
require_once(__DIR__ . '/../../combo/class/' . 'LogUtility.php');

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
        $this->pluginsEnabled[] = "sqlite";
        parent::setUp();


    }


    /**
     * Test to create a meta
     */
    public function test_frontmatter_meta_setting()
    {

        $pageId = 'front_matter_test';
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

        $pageId = 'front_matter_test';
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

    public function test_frontmatter_not_valid_object()
    {



        $pageId = 'front_matter_test_not_valid';
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "key":"value\'\'' . DOKU_LF
            . '}' . DOKU_LF
            . '---' . DOKU_LF
            . 'Content';

        saveWikiText($pageId, $text, 'Created');


        $request = new TestRequest();
        Auth::becomeSuperUser($request);
        $response = $request->get(
            array(
                'id'=>$pageId,
                'loglevel'=> "-1"
            ),
            '/doku.php');

        // get the generator name from the meta tag.
        $div = $response->queryHTML('.error');

        $text = $div->text();
        $result = StringUtility::contain("is not valid",$text);
        // check the result
        $this->assertTrue($result,"The json error message is not in ({$text})");


    }


}
