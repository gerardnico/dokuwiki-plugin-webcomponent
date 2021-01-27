<?php

use ComboStrap\PluginUtility;


if (!defined('DW_LF')) {
    define('DW_LF', "\n");
}

require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');

/**
 * Test the page rules admin pages
 *
 * @group plugin_combo
 * @group plugins
 */
class dokuwiki_plugin_page_rules_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'sqlite';

        parent::setUp();


    }


    /**
     *  Make a call to the admin page
     */
    public function test_adminCall()
    {


        $pageName = admin_plugin_combo_pagerules::getAdminPageName();

        $request = new TestRequest();
        PluginUtility::runAsAdmin($request);
        $response = $request->get(array('do' => 'admin', 'page' => $pageName),'/doku.php');

        // Simple
        $countListContainer = $response->queryHTML("#pagerules_list")->count();
        $this->assertEquals(1, $countListContainer, "There should a div for the list of rules");

    }


}
