<?php
/**
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;
use ComboStrap\TplConstant;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');



class plugin_combo_hiddenpage_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
        global $conf;
        $conf['template'] = 'strap';
    }



    function testHiddenBar(){

        $constantFile = __DIR__ . '/../../../tpl/strap/class/TplConstant.php';
        if (file_exists($constantFile)) {
            /** @noinspection PhpIncludeInspection */
            require_once($constantFile);
        }
        $footerBar = tpl_getConf(TplConstant::CONF_FOOTER);
        $this->assertTrue(isHiddenPage($footerBar));

        $headerBar = tpl_getConf(TplConstant::CONF_HEADER);
        $this->assertTrue(isHiddenPage($headerBar));

        $sidekick = tpl_getConf(TplConstant::CONF_SIDEKICK);
        $this->assertTrue(isHiddenPage($sidekick));

        $this->assertTrue(isHiddenPage(PluginUtility::COMBOSTRAP_NAMESPACE_NAME));

        $this->assertFalse(isHiddenPage("whatever"));
    }


}
