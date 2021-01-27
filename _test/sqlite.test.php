<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
class dokuwiki_plugin_combo_sqlite_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;

        parent::setUp();

    }


    /**
     * Asking a page that does not exist will
     * trigger the url manager that use SQLite
     *
     * this test should not produce any errors even without sqlite
     */
    public function test_without_sqlite_no_errors()
    {

        $request = new TestRequest();
        $request->get(array('id' => 'doesnot_exist'));
        $this->assertEquals(0, 0, "An assertion is mandatory for phpunit");

    }


}
