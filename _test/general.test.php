<?php

require_once (__DIR__.'/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class dokuwiki_plugin_webcomponent_plugin_test extends DokuWikiTest
{


    protected $pluginsEnabled = array(webcomponent::PLUGIN_NAME);

    /**
     * Control the plugin.info.txt
     */
    public function test_pluginInfoTxt()
    {
        $file = __DIR__ . '/../plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertEquals(webcomponent::PLUGIN_NAME, $info['base']);

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
                webcomponent::PLUGIN_NAME,
                $plugin_controller->getList()),
            webcomponent::PLUGIN_NAME . " plugin is loaded"
        );
    }

    /**
     * Test to ensure that every conf['...'] entry in conf/default.php has a corresponding meta['...'] entry in
     * conf/metadata.php.
     */
    public function test_plugin_conf()
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

    }


}
