<?php
/**
* Test the {@link syntax_plugin_combo_navbar}
*
* @group plugin_combo
* @group plugins
*
*/

use ComboStrap\ArrayUtility;
use ComboStrap\PluginUtility;
use ComboStrap\StringUtility;

require_once(__DIR__ . '/../class/StringUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');

class plugin_combo_navbargroup_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    /**
     * Basic test with claa
     */
    public function test_navbar()
    {

        $content = "<navbar><group expand=\"true\"></group></navbar>";
        $instructions = p_get_instructions($content);
        $xhtml = p_render('xhtml', $instructions, $info);
        $strpos = strpos(StringUtility::normalized($xhtml),'<ul class="navbar-nav mr-auto"></ul>');
        $this->assertNotFalse($strpos, "mr auto should be present");

    }


}
