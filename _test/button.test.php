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
        $id = 'namespace:page';
        $expected = '<button type="button" class="btn btn-primary"><a href="/./doku.php?id='.$id.'#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="'.$id.'">' . $link_content . '</a></button>';
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
        $elements = syntax_plugin_webcomponent_button::getTags();
        $link_content = 'Go Somewhere';
        $id = "namespace:page";
        $expected = '<button type="button" class="btn btn-primary mbt-3"><a href="/./doku.php?id='.$id.'#section" class="wikilink2" title="namespace:page" rel="nofollow" data-wiki-id="'.$id.'">' . $link_content . '</a></button>';
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
        $elements = syntax_plugin_webcomponent_button::getTags();
        $link_content = 'Go Somewhere';
        $external = 'https://gerardnico.com';
        $expected = '<button type="button" class="btn btn-primary"><a href="https://gerardnico.com" class="urlextern" title="https://gerardnico.com" rel="ugc nofollow">' . $link_content . '</a></button>';
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
