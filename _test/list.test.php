<?php

use ComboStrap\StyleUtility;
use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/StyleUtility.php');

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

        $text = <<<EOF
<list>
    <li>
        Icon
    </li>
    <li>
        Badge
    </li>
</list>
EOF;

        $styleNode = '<style>' . StyleUtility::getRule(syntax_plugin_combo_listitem::getListItemStyle(), "." . syntax_plugin_combo_listitem::COMBO_LIST_ITEM_CLASS) . '</style>';
        $expected = TestUtility::normalizeDokuWikiHtml("<ul class=\"combo-list\">
{$styleNode}
<li class=\"combo-list-item\">Icon</li>
<li class=\"combo-list-item\">Badge</li>
</ul>
");
        $rendered = PluginUtility::render($text);
        $normalizedOutput =  TestUtility::normalizeDokuWikiHtml($rendered,true);
        $this->assertEquals(
            $expected,
            $normalizedOutput
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
<a href="/./doku.php?id=hallo" class="wikilink2" title="hallo" rel="nofollow" data-wiki-id="hallo">Hallo World ...</a></li>
</ul>
', $output);

    }

}
