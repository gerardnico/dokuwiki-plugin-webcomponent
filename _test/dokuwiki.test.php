<?php
/**
 * Tests over DokuWiki function for the webcomponent plugin
 *
 * @group plugin_combo
 * @group plugins
 */


class plugin_combo_dokuwiki_test extends DokuWikiTest
{






    /** Page exist can be tested on two ways within DokuWiki
     *   * page_exist
     *   * and the $INFO global variable
     */
    public function test_pageExists()
    {

        $pageExistId = 'page_exist';
        saveWikiText($pageExistId, 'REDIRECT Best Page Name Same Branch', 'Test initialization');
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

}
