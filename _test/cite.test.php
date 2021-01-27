<?php

use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../../combo/class/' . 'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/' . 'LinkUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_cite_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_element_name()
    {

        $elementName = syntax_plugin_combo_cite::TAG;

        $this->assertEquals('cite', $elementName);

    }

    public function test_cite_base()
    {

        $element = syntax_plugin_combo_cite::TAG;
        $doku_text = '<' . $element . ' >Citation</' . $element . '>';
        $expected = '<cite>Citation</cite>';

        $xhtml = PluginUtility::render($doku_text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }

    public function test_cite_with_url()
    {

        $element = syntax_plugin_combo_cite::TAG;
        $id = 'namespace:page';
        $doku_text = '<' . $element . ' >[[' . $id . '#section|bla]]</' . $element . '>';
        $expected = '<cite><a href="/./doku.php?id=' . $id . '#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="' . $id . '">bla</a></cite>';
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }

    public function test_cite_extra_attribute()
    {

        $element = syntax_plugin_combo_cite::TAG;
        $id = 'namespace:page';
        $extraAttr = 'class="m-2"';
        $doku_text = '<' . $element . ' ' . $extraAttr . '>[[' . $id . '#section|bla]]</' . $element . '>';
        $expected = '<cite ' . $extraAttr . '><a href="/./doku.php?id=' . $id . '#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="' . $id . '">bla</a></cite>';
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $expectedNormalized = TestUtility::normalizeDokuWikiHtml($expected);
        $this->assertEquals(
            $expectedNormalized,
            TestUtility::normalizeDokuWikiHtml($xhtml));

    }


}
