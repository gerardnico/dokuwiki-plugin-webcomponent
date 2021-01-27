<?php

use ComboStrap\StyleUtility;
use ComboStrap\PluginUtility;


require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'StyleUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'Text.php');

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

        $plainText = <<<EOF
<list>
    <li>
        Icon
    </li>
    <li>
        Badge
    </li>
</list>
EOF;

        $styleListItem = '<style>' . StyleUtility::getRule(syntax_plugin_combo_listitem::getStyles(), "." . syntax_plugin_combo_listitem::COMBO_LIST_ITEM_CLASS) . '</style>';
        $styleList = '<style>' . StyleUtility::getRule(syntax_plugin_combo_list::getStyles(), "." . syntax_plugin_combo_list::COMBO_LIST_CLASS) . '</style>';
        $expected = <<<EOF
{$styleList}
<ul class="combo-list">
{$styleListItem}
<li class="combo-list-item">Icon</li>
<li class="combo-list-item">Badge</li>
</ul>
EOF;

        $rendered = PluginUtility::render($plainText);
        $error = TestUtility::HtmlDiff($expected, $rendered);
        $this->assertEquals("", $error);

    }


    /**
     * Test pipeline in a list
     */
    public function test_pipeline_link()
    {

        $styleListItem = '<style>' . StyleUtility::getRule(syntax_plugin_combo_listitem::getStyles(), "." . syntax_plugin_combo_listitem::COMBO_LIST_ITEM_CLASS) . '</style>';
        $styleList = '<style>' . StyleUtility::getRule(syntax_plugin_combo_list::getStyles(), "." . syntax_plugin_combo_list::COMBO_LIST_CLASS) . '</style>';
        $input = '<list><li>[[hallo|<' . syntax_plugin_combo_pipeline::TAG . '>"Hallo World" | rconcat(" ...") </' . syntax_plugin_combo_pipeline::TAG . '>]]</li></list>';
        $output = PluginUtility::render($input);
        $this->assertEquals("{$styleList}
<ul class=\"combo-list\">
{$styleListItem}
<li class=\"combo-list-item\">
<a href=\"/./doku.php?id=hallo\" class=\"wikilink2\" title=\"hallo\" rel=\"nofollow\" data-wiki-id=\"hallo\">Hallo World ...</a></li>
</ul>
", $output);

    }

}
