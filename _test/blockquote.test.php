<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_blockquote_test extends DokuWikiTest
{

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_tag_name()
    {

        $elementName = syntax_plugin_webcomponent_blockquote::getTagName();

        $this->assertEquals('blockquote', $elementName);

    }

    public function test_base()
    {

        $element = syntax_plugin_webcomponent_blockquote::getTagName();
        $doku_text = '<' . $element . '>MyQuote</' . $element . '>';
        $expected = '<div class="card m-3">'.DOKU_LF
            .DOKU_TAB.'<div class="card-body">'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'<blockquote class="blockquote m-0">'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'MyQuote'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'</blockquote>'.DOKU_LF
            .DOKU_TAB.'</div>'.DOKU_LF
            .'</div>';

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_with_cite_base()
    {

        $element = syntax_plugin_webcomponent_blockquote::getTagName();
        $doku_text = '<' . $element . '>MyQuote<cite>Nico</cite></' . $element . '>';
        $expected = '<div class="card m-3">'.DOKU_LF
            .DOKU_TAB.'<div class="card-body">'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'<blockquote class="blockquote m-0">'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'MyQuote'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'<footer class="blockquote-footer text-right"><cite>Nico</cite></footer>'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'</blockquote>'.DOKU_LF
            .DOKU_TAB.'</div>'.DOKU_LF
            .'</div>';
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }

    public function test_base_no_class()
    {

        $element = syntax_plugin_webcomponent_blockquote::getTagName();
        $doku_text = '<' . $element . ' class="" >MyQuote</' . $element . '>';
        $expected = '<div class="card">'.DOKU_LF
            .DOKU_TAB.'<div class="card-body">'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'<blockquote class="blockquote m-0">'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'MyQuote'.DOKU_LF
            .DOKU_TAB.DOKU_TAB.'</blockquote>'.DOKU_LF
            .DOKU_TAB.'</div>'.DOKU_LF
            .'</div>';

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }


}
