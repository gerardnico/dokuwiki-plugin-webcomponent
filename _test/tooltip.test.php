<?php

use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;


require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'HtmlUtility.php');

/**
 * Test the {@link syntax_plugin_combo_tooltip} component
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_tooltip_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_base_tooltip()
    {

        $text = "<tooltip text='Text'>Text</tooltip>";
        $text .= $text;
        $id = "tooltip";
        TestUtility::addPage($id, $text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id" => $id));
        $scriptTooltipCount = $response->queryHTML("#" . syntax_plugin_combo_tooltip::SCRIPT_ID)->count();
        $this->assertEquals(1, $scriptTooltipCount, "The number of script tooltip should be one");
        $tooltipCount = $response->queryHTML("span[data-toggle=\"tooltip\"]")->count();
        $this->assertEquals(2, $tooltipCount, "The number of tooltip should be two");
    }


}
