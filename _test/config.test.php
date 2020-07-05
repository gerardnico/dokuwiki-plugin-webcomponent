<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');



/**
 * Test the settings.php file
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_setting_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     *
     * Test if we don't have any problem
     * in the file settings.php
     *
     * If there is, we got an error in the admin config page
     */
    public function test_base()
    {

        $request = new TestRequest();
        PluginUtility::runAsAdmin($request);
        $response = $request->get(array('do' => 'admin', 'page' => "config"),'/doku.php');

        // Simple
        $countListContainer = $response->queryHTML("#plugin____".PluginUtility::$PLUGIN_BASE_NAME."____plugin_settings_name")->count();
        $this->assertEquals(1, $countListContainer, "There should an element");

    }


}
