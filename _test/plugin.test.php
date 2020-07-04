<?php

use ComboStrap\PluginUtility;
use dokuwiki\plugin\config\core\ConfigParser;
use dokuwiki\plugin\config\core\Loader;

require_once (__DIR__ . '/../class/PLuginUtility.php');
require_once (__DIR__ . '/../class/PluginUtility.php');

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
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'config';
        parent::setUp();
    }


    /**
     * Control the plugin.info.txt
     */
    public function test_pluginInfoTxt()
    {
        $file = __DIR__ . '/../plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertEquals(PluginUtility::$PLUGIN_BASE_NAME, $info['base']);

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
                PluginUtility::$PLUGIN_BASE_NAME,
                $plugin_controller->getList()),
            PluginUtility::$PLUGIN_BASE_NAME . " plugin is loaded"
        );
    }



    /**
     * Test to ensure that every conf['...'] entry
     * in conf/default.php has a corresponding meta['...'] entry in conf/metadata.php.
     */
    public function test_plugin_default()
    {
        $conf = array();
        $conf_file = __DIR__ . '/../conf/default.php';
        if (file_exists($conf_file)) {
            include($conf_file);
        }

        $meta = array();
        $meta_file = __DIR__ . '/../conf/metadata.php';
        if (file_exists($meta_file)) {
            include($meta_file);
        }


        $this->assertEquals(
            gettype($conf),
            gettype($meta),
            'Both ' . DOKU_PLUGIN . 'syntax/conf/default.php and ' . DOKU_PLUGIN . 'syntax/conf/metadata.php have to exist and contain the same keys.'
        );

        if (gettype($conf) != 'NULL' && gettype($meta) != 'NULL') {
            foreach ($conf as $key => $value) {
                $this->assertArrayHasKey(
                    $key,
                    $meta,
                    'Key $meta[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'syntax/conf/metadata.php'
                );
            }

            foreach ($meta as $key => $value) {
                $this->assertArrayHasKey(
                    $key,
                    $conf,
                    'Key $conf[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'syntax/conf/default.php'
                );
            }
        }

        /**
         * The default are read through parsing
         * by the config plugin
         * Yes that's fuck up but yeah
         * This test check that we can read them
         */
        $parser = new ConfigParser();
        $loader = new Loader($parser);
        $defaultConf = $loader->loadDefaults();
        $keyPrefix = "plugin____".PluginUtility::$PLUGIN_BASE_NAME."____";
        $this->assertTrue(is_array($defaultConf));

        // plugin defaults
        foreach ($meta as $key => $value) {
            $this->assertArrayHasKey(
                $keyPrefix.$key,
                $defaultConf,
                'Key $conf[\'' . $key . '\'] could not be parsed in ' . DOKU_PLUGIN . 'syntax/conf/default.php. Be sure to give only values and not variable.'
            );
        }


    }




}
