<?php

require_once(__DIR__ . '/../class/PluginUtility.php');

use ComboStrap\HeadingUtility;
use ComboStrap\PluginUtility;


/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_blockquote_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_tag_name()
    {

        $elementName = syntax_plugin_combo_blockquote::TAG;

        $this->assertEquals('blockquote', $elementName);

    }

    public function test_base_typo()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $dokuContent = '<' . $element . ' typo>MyQuote</' . $element . '>';
        $expected = '<blockquote class="blockquote">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'</blockquote>'.DOKU_LF;

        $instructions = p_get_instructions($dokuContent);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_base_card()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $dokuContent = '<' . $element . '>MyQuote</' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF;

        $instructions = p_get_instructions($dokuContent);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_card_with_heading()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $dokuContent = '<' . $element . '>'.DOKU_LF
            .'=== Header ==='.DOKU_LF
            .'MyQuote'
            .'</' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            . "<h4 class=\"card-title\" ".HeadingUtility::COMPONENT_TITLE_STYLE.">Header</h4>".DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF
            ;


        $instructions = p_get_instructions($dokuContent);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_card_with_header()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $dokuContent = '<' . $element . '>'.DOKU_LF
            .'<header>Header</header>'.DOKU_LF
            .'MyQuote'
            .'</' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .'<div class="card-header">'.DOKU_LF
            .'Header'.DOKU_LF
            .'</div>'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF
        ;


        $instructions = p_get_instructions($dokuContent);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_blockquote_attribute()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $dokuContent = '<' . $element . ' width="100px">MyQuote</' . $element . '>';
        $expected = '<div class="card" style="max-width:100px">'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF;

        $instructions = p_get_instructions($dokuContent);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }



    public function test_blockquote_typo_with_cite_base()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $doku_text = '<' . $element . ' typo>MyQuote<cite>Nico</cite></' . $element . '>';
        $expected = '<blockquote class="blockquote">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'<footer class="blockquote-footer"><cite>Nico</cite></footer>'.DOKU_LF
            .'</blockquote>'.DOKU_LF;
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_blockquote_card_no_class()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $doku_text = '<' . $element . ' class="" >MyQuote</' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'MyQuote'.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF;

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_blockquote_heading()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $doku_text = '<' . $element . ' >'.DOKU_LF
          .'=== Title ==='.DOKU_LF
          .'MyQuote'.DOKU_LF
          .'</' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            .'<h4 class="card-title" '.HeadingUtility::COMPONENT_TITLE_STYLE.'>Title</h4>'.DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'MyQuote'.DOKU_LF.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF;

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }


    /**
     * Add a space
     * if you want to have a real card
     * in case for instance that you use
     * a component inside such as math
     */
    public function test_blockquote_card_with_no_content()
    {

        $element = syntax_plugin_combo_blockquote::TAG;
        $doku_text = '<' . $element . '>'.DOKU_LF
            . ' '.DOKU_LF
            . '<cite>Nico</cite></' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .'<div class="card-body">'.DOKU_LF
            .'<blockquote class="blockquote mb-0">'.DOKU_LF
            .'<footer class="blockquote-footer"><cite>Nico</cite></footer>'.DOKU_LF
            .'</blockquote>'.DOKU_LF
            .'</div>'.DOKU_LF
            .'</div>'.DOKU_LF;

        $xhtml = PluginUtility::render($doku_text);
        $this->assertEquals($expected, $xhtml);

    }


}
