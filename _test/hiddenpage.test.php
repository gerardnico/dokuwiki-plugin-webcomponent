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

        require_once(__DIR__ . '/../../../tpl/strap/class/TplConstant.php');
        $footerbar = tpl_getConf(TplConstant::CONF_FOOTER);
        $this->assertTrue(isHiddenPage($footerbar));

        $headerbar = tpl_getConf(TplConstant::CONF_HEADER);
        $this->assertTrue(isHiddenPage($footerbar));

        $this->assertFalse(isHiddenPage("whatever"));
    }

}
