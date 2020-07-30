<?php

use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_list_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     * Basic
     * @throws Exception
     */
    public function test_list()
    {

        $text = "<list>" . DOKU_LF
            . "<li>" . DOKU_LF
            . "Title" . DOKU_LF
            . "</li>" . DOKU_LF
            . "</list>";
        $expected = "<ul class=\"combo-list\">
  <li class=\"combo-list-item\">
    <span>Title</span>
  </li>
</ul>
";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            TestUtility::normalizeComboXml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }


    /**
     * Test pipeline in a list
     */
    public function test_pipeline_link()
    {

        $input = '<list><li>[[hallo|<' . syntax_plugin_combo_pipeline::TAG . '>"Hallo World" | rconcat(" ...") </' . syntax_plugin_combo_pipeline::TAG . '>]]</li></list>';
        $output = PluginUtility::render($input);
        $this->assertEquals('<ul class="combo-list">
  <li class="combo-list-item">
    <span>Title</span>
  </li>
</ul>
', $output);

    }

}
