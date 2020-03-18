<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_button_test extends DokuWikiTest
{

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_component_name()
    {

        $componentName = syntax_plugin_webcomponent_button::getTag();

        $this->assertEquals('button', $componentName);

    }

    public function test_internal_base()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_webcomponent_button::getTags();
        $link_content = 'Go Somewhere';
        $expected = '<a class="btn btn-primary" href="/./doku.php?id=:namespace:page%23section">' . $link_content . '</a>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . '[[:namespace:page#section|' . $link_content . ']]' . '</' . $element . '>';
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
        $elements = syntax_plugin_webcomponent_button::getTags();
        $link_content = 'Go Somewhere';
        $expected = '<a class="btn btn-primary mbt-3" href="/./doku.php?id=:namespace:page%23section">' . $link_content . '</a>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . ' class="mbt-3" >' . '[[:namespace:page#section|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals($expected, $xhtml);
        }

    }

    public function test_external_link()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $elements = syntax_plugin_webcomponent_button::getTags();
        $link_content = 'Go Somewhere';
        $external = 'https://gerardnico.com';
        $expected = '<a class="btn btn-primary" href="'.$external.'">' . $link_content . '</a>';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . '[['.$external.'|' . $link_content . ']]' . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $this->assertEquals($expected, $xhtml);
        }

    }

    public function test_indexer()
    {

        $pageIdReferent = webcomponent::getNameSpace().'referrer';
        $pageId =  webcomponent::getNameSpace() . 'test_indexer';


        $element = syntax_plugin_webcomponent_button::getTags()[0];
        $doku_text = '<' . $element . '>' . '[['.$pageIdReferent.']]' . '</' . $element . '>';


        saveWikiText($pageIdReferent, 'Not null', 'test_indexer test base');
        idx_addPage($pageIdReferent);


        saveWikiText($pageId, $doku_text, 'test_indexer test base');
        idx_addPage($pageId);

        $backlinks = ft_backlinks($pageIdReferent);
        $expected = 1;
        $this->assertEquals($expected, sizeof($backlinks));


    }


}
