<?php

use ComboStrap\HtmlUtility;


/**
 * Tests over DokuWiki function for the Combo plugin
 *
 * @group plugin_combo
 * @group plugins
 */
require_once(__DIR__ . '/TestUtility.php');


class plugin_combo_dokuwiki_test extends DokuWikiTest
{






    /** Page exist can be tested on two ways within DokuWiki
     *   * page_exist
     *   * and the $INFO global variable
     */
    public function test_pageExists()
    {

        $pageExistId = 'page_exist';
        TestUtility::addPage($pageExistId, 'REDIRECT Best Page Name Same Branch', 'Test initialization');
        // Not in a request
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertTrue(page_exists($pageExistId));

        // In a request
        $request = new TestRequest();
        $request->get(array('id' => $pageExistId), '/doku.php');
        global $INFO;

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertTrue($INFO['exists']);

        // Not in a request
        $pageDoesNotExist = "pageDoesNotExist";
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertFalse(page_exists($pageDoesNotExist));

        // In a request
        $request = new TestRequest();
        $request->get(array('id' => $pageDoesNotExist), '/doku.php');
        global $INFO;
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertFalse($INFO['exists']);

    }

    public function test_p_tag()
    {

        $content = "yolo";
        $instructions = p_get_instructions($content);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals(HtmlUtility::normalize("<p>yolo</p>"), HtmlUtility::normalize($xhtml));

    }


}
