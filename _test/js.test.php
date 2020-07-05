<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');



/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_js_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     *
     * Should not work
     * Test is only manual with the browser by hitting
     * http://localhost:81/lib/exe/js.php?t=dokuwiki
     *
     */
    public function test_base()
    {

        $message = "";
        try {
            $request = new TestRequest();
            $request->get(array('t' => 'dokuwiki'), '/lib/exe/js.php');
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals("/lib/exe/js.php \n--- only /doku.php, /lib/exe/fetch.php, /lib/exe/detail.php, /lib/exe/ajax.php are supported currently",$message);

    }

    /**
     * Add a query string to make a difference between public and editor
     */
    public function test_js_show_query_string()
    {

        $test_name = 'test_js_show_query_string';
        $pageId = PluginUtility::getNameSpace() . $test_name;
        $doku_text = 'whatever';
        saveWikiText($pageId, $doku_text, $test_name);
        idx_addPage($pageId);
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId));
        $jsSrcAttribute = $testResponse->queryHTML('script[src*="js.php"]' )->attr('src');
        $pos = strpos($jsSrcAttribute,action_plugin_combo_js::ACCESS_PROPERTY_KEY.'=public');
        $this->assertEquals(true, $pos > 0);


    }


    /**
     * Test that Js php is running smoothly
     */
    public function test_js_php()
    {

        $jsFile = DOKU_INC . 'lib/exe/js.php';
        $this->assertEquals(true, file_exists($jsFile));
        /** @noinspection PhpIncludeInspection */
        require_once $jsFile;
        ob_start();
        js_out();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(strlen($output)>0,"There is an output");

    }
}
