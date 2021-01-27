<?php

use ComboStrap\HtmlUtility;
use ComboStrap\PluginUtility;
use ComboStrap\StringUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../../combo/class/' . 'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/' . 'StringUtility.php');

/**
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_badge_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_internal_base()
    {

        $content = '<badge info rounded="true" class="my_class" style="my_style">Badge</badge>';

        $instructions = p_get_instructions($content);
        $xhtml = p_render('xhtml', $instructions, $info);

        $expected = '<p><span class="my_class badge alert-info badge-pill" style="my_style:">Badge</span></p>';
        $this->assertEquals(HtmlUtility::normalize($expected), HtmlUtility::normalize($xhtml));


    }

}
