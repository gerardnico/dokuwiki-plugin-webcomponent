<?php

use ComboStrap\HeadingUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/HeadingUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_teaser_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_component_name()
    {

        $componentName = syntax_plugin_combo_card::TAG;

        $this->assertEquals('card', $componentName);

    }

    public function test_heading()
    {
        $tag = syntax_plugin_combo_card::TAG;
        $doku_text = '<' . $tag . '>' . DOKU_LF .
            '=== Teaser Title ===' . DOKU_LF .
            'A example taken ' . DOKU_LF .
            '</' . $tag . '>';
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $expected = '<div class="card">' . DOKU_LF
            . '<div class="card-body">' . DOKU_LF
            . '<h4 class="card-title" '.HeadingUtility::COMPONENT_TITLE_STYLE.'>Teaser Title</h4>'.DOKU_LF
            . 'A example taken '.DOKU_LF
            . '</div>'.DOKU_LF
            . '</div>'.DOKU_LF;

        $this->assertEquals(HtmlUtility::normalize($expected), HtmlUtility::normalize($xhtml));

    }

    public function test_header()
    {
        $tag = syntax_plugin_combo_card::TAG;
        $doku_text = '<' . $tag . '>' . DOKU_LF .
            '<header>Teaser Header</header>' . DOKU_LF .
            'A example taken ' . DOKU_LF .
            '</' . $tag . '>';
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $expected = '<div class="card">' . DOKU_LF
            .'<div class="card-header">'.DOKU_LF
            . 'Teaser Header'.DOKU_LF
            .'</div>'.DOKU_LF
            . '<div class="card-body">' . DOKU_LF
            . 'A example taken '.DOKU_LF
            . '</div>'.DOKU_LF
            . '</div>'.DOKU_LF;

        $this->assertEquals(HtmlUtility::normalize($expected), HtmlUtility::normalize($xhtml));

    }

    public function test_with_image()
    {

        $tag = syntax_plugin_combo_card::TAG;
        $doku_text = '<' . $tag . ' style="width: 18rem;" class="mb-3">' . DOKU_LF .
            '{{:allowclipboardhelper.jpg?30|}}' . DOKU_LF .
            '=== Teaser Title ===' . DOKU_LF .
            'A example taken from [[https://getbootstrap.com/docs/4.3/components/card/#example|the bootstrap quick example]] on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</' . $tag . '>';

        $info = array();

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);

        $expected = '<div style="width: 18rem" class="mb-3 card">' . DOKU_LF .
            '<img class="card-img-top" src="/./lib/exe/fetch.php?w=30&amp;tok=029902&amp;media=allowclipboardhelper.jpg" alt="" width="30">' . DOKU_LF .
            '<div class="card-body">' . DOKU_LF .
            '<h4 class="card-title" ' . HeadingUtility::COMPONENT_TITLE_STYLE . '>Teaser Title</h4>' . DOKU_LF .
            'A example taken from <a href="https://getbootstrap.com/docs/4.3/components/card/#example" class="urlextern" title="https://getbootstrap.com/docs/4.3/components/card/#example" rel="ugc nofollow">the bootstrap quick example</a> on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</div>' . DOKU_LF .
            '</div>' . DOKU_LF;


        $this->assertEquals(HtmlUtility::normalize($expected), HtmlUtility::normalize($xhtml));

    }

    public function test_two_teaser()
    {

        $componentName = syntax_plugin_combo_card::TAG;
        $doku_text = '<' . $componentName . ' style="width: 18rem;">' . DOKU_LF .
            '{{:allowclipboardhelper.jpg?30|}}' . DOKU_LF .
            '=== Teaser Title ===' . DOKU_LF .
            'A example taken from [[https://getbootstrap.com/docs/4.3/components/card/#example|the bootstrap quick example]] on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</' . $componentName . '>' . DOKU_LF;


        $expected = '<div style="width: 18rem" class="card">' . DOKU_LF
            . '<img class="card-img-top" src="/./lib/exe/fetch.php?w=30&amp;tok=029902&amp;media=allowclipboardhelper.jpg" alt="" width="30">' . DOKU_LF
            . '<div class="card-body">' . DOKU_LF
            . '<h4 class="card-title" ' . HeadingUtility::COMPONENT_TITLE_STYLE . '>Teaser Title</h4>' . DOKU_LF .
            'A example taken from <a href="https://getbootstrap.com/docs/4.3/components/card/#example" class="urlextern" title="https://getbootstrap.com/docs/4.3/components/card/#example" rel="ugc nofollow">the bootstrap quick example</a> on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</div>' . DOKU_LF .
            '</div>' . DOKU_LF;

        $instructions = p_get_instructions($doku_text . $doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected . $expected, $xhtml);

    }


}
