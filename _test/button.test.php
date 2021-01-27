<?php

use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;



require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'LinkUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_button_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }



    public function test_internal_base()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_combo_button::getTags();
        $link_content = 'Go Somewhere';
        $id = 'namespace:page';
        $expected = '<button type="button" class="btn btn-primary"><a href="/./doku.php?id='.$id.'#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="'.$id.'" style="'. LinkUtility::STYLE_VALUE.'">' . $link_content . '</a></button>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . '[['.$id.'#section|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals(
                TestUtility::normalizeDokuWikiHtml($expected),
                TestUtility::normalizeDokuWikiHtml($xhtml)
            );
        }

    }

    /**
     * We add a class
     */
    public function test_class()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_combo_button::getTags();
        $link_content = 'Go Somewhere';
        $id = "namespace:page";
        $expected = '<button type="button" class="mbt-3 btn btn-primary"><a href="/./doku.php?id='.$id.'#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="'.$id.'" style="'.LinkUtility::STYLE_VALUE.'">' . $link_content . '</a></button>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . ' class="mbt-3" >' . '[['.$id.'#section|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals(
                TestUtility::normalizeDokuWikiHtml($expected),
                TestUtility::normalizeDokuWikiHtml($xhtml)
            );
        }

    }

    public function test_external_link()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_combo_button::getTags();
        $link_content = 'Go Somewhere';
        $external = 'https://gerardnico.com';
        $expected = '<button type="button" class="btn btn-primary"><a href="https://gerardnico.com" class="urlextern" title="https://gerardnico.com" rel="ugc nofollow" style="'.LinkUtility::STYLE_VALUE.'">' . $link_content . '</a></button>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . '[['.$external.'|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals(
                TestUtility::normalizeDokuWikiHtml($expected),
                TestUtility::normalizeDokuWikiHtml($xhtml)
            );
        }

    }


    public function test_size()
    {

        $rendered = PluginUtility::render("<btn size=\"lg\">Large button</btn>");
        $expected = "<button type=\"button\" class=\"btn btn-primary btn-lg\">Large button</button>";
        $this->assertEquals(HtmlUtility::normalize($expected), HtmlUtility::normalize($rendered));


    }


}
