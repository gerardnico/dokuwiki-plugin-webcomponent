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

class plugin_combo_navbar_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        parent::setUp();
    }

    /**
     * Basic test with claa
     */
    public function test_navbar()
    {

        $extraClass = "yolo";
        $content = "<navbar class=\"$extraClass\"></navbar>";
        $instructions = p_get_instructions($content);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals("<nav class=\"$extraClass navbar\"></nav>", StringUtility::normalized($xhtml));

    }


}
