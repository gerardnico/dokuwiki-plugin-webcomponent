<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_cite_test extends DokuWikiTest
{

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_element_name()
    {

        $elementName = syntax_plugin_webcomponent_cite::getTag();

        $this->assertEquals('cite', $elementName);

    }

    public function test_base()
    {

        // https://getbootstrap.com/docs/4.3/components/card/#using-custom-css
        $element = syntax_plugin_webcomponent_cite::getTag();
        $doku_text = '<' . $element . '>[[:namespace:page#section|bla]]</' . $element . '>';
        $expected = '<cite><a href="/./doku.php?id=namespace:page#section" class="wikilink2" title="namespace:page" rel="nofollow">bla</a></cite>';
        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected, $xhtml);

    }



}
