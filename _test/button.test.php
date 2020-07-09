<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

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
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_component_name()
    {

        $componentName = syntax_plugin_combo_button::getTag();

        $this->assertEquals('button', $componentName);

    }

    public function test_internal_base()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_combo_button::getTags();
        $link_content = 'Go Somewhere';
        $id = 'namespace:page';
        $expected = '<button type="button" class="btn btn-primary"><a href="/./doku.php?id='.$id.'#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="'.$id.'" style="'.syntax_plugin_combo_buttonlink::STYLE_VALUE.'">' . $link_content . '</a></button>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . '[['.$id.'#section|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals($expected, $xhtml);
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
        $expected = '<button type="button" class="mbt-3 btn btn-primary"><a href="/./doku.php?id='.$id.'#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="'.$id.'" style="'.syntax_plugin_combo_buttonlink::STYLE_VALUE.'">' . $link_content . '</a></button>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . ' class="mbt-3" >' . '[['.$id.'#section|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals($expected, $xhtml);
        }

    }

    public function test_external_link()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_combo_button::getTags();
        $link_content = 'Go Somewhere';
        $external = 'https://gerardnico.com';
        $expected = '<button type="button" class="btn btn-primary"><a href="https://gerardnico.com" class="urlextern" title="https://gerardnico.com" rel="ugc nofollow" style="'.syntax_plugin_combo_buttonlink::STYLE_VALUE.'">' . $link_content . '</a></button>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . '[['.$external.'|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals($expected, $xhtml);
        }

    }

    /**
     * A link with a button should be in the index
     */
    public function test_indexer()
    {

        // The home page
        $pageIdReferent = PluginUtility::getNameSpace() .'referrer';
        TestUtility::addPage($pageIdReferent, 'Not null', 'test_indexer test base');

        // The backlinks page
        $pageWithBacklinks =  PluginUtility::getNameSpace() . 'test_indexer';
        $element = syntax_plugin_combo_button::getTags()[0];
        $textWithBackLinks = '<' . $element . '>' . '[['.$pageIdReferent.']]' . '</' . $element . '>';
        TestUtility::addPage($pageWithBacklinks, $textWithBackLinks, 'test_indexer test base');


        // The test
        $backLinks = ft_backlinks($pageIdReferent);
        $expected = 1;
        $this->assertEquals($expected, sizeof($backLinks),"There should be 2 link in the backlinks");


    }


}
