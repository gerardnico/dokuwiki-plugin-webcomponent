<?php

use ComboStrap\PluginUtility;
use dokuwiki\plugin\config\core\ConfigParser;
use dokuwiki\plugin\config\core\Loader;

require_once(__DIR__ . '/../../combo/class/' . 'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/' . 'PluginUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class dokuwiki_plugin_combo_plugin_test extends DokuWikiTest
{


    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'config';
        parent::setUp();
    }


    /**
     * Control the plugin.info.txt
     */
    public function test_pluginInfoTxt()
    {
        $file = __DIR__ . '/../../combo/' . 'plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertEquals(PluginUtility::PLUGIN_BASE_NAME, $info['base']);

        $this->assertArrayHasKey('author', $info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('desc', $info);

        $this->assertArrayHasKey('date', $info);
        $this->assertRegExp('/^\d\d\d\d-\d\d-\d\d$/', $info['date']);
        $this->assertTrue(false !== strtotime($info['date']));


        $this->assertArrayHasKey('url', $info);
        $this->assertRegExp('/^https?:\/\//', $info['url']);

        $this->assertArrayHasKey('email', $info);
        $this->assertTrue(mail_isvalid($info['email']));


    }

    /**
     * test if the plugin is loaded.
     */
    public function test_plugin_isLoaded()
    {
        global $plugin_controller;
        $this->assertTrue(
            in_array(
                PluginUtility::PLUGIN_BASE_NAME,
                $plugin_controller->getList()),
            PluginUtility::PLUGIN_BASE_NAME . " plugin is loaded"
        );
    }


}
