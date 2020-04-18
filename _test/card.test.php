<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_teaser_test extends DokuWikiTest
{

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_component_name()
    {

        $componentName = syntax_plugin_webcomponent_card::getTag();

        $this->assertEquals('card', $componentName);

    }

    public function test_base()
    {

        $componentName = syntax_plugin_webcomponent_card::getTag();
        $doku_text = '<' . $componentName . ' style="width: 18rem;">' . DOKU_LF .
            '{{:allowclipboardhelper.jpg?30|}}' . DOKU_LF .
            '=== Teaser Title ===' . DOKU_LF .
            'A example taken from [[https://getbootstrap.com/docs/4.3/components/card/#example|the bootstrap quick example]] on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</' . $componentName . '>';

        $info = array();

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);

        $expected = '<div class="card" style="width: 18rem;">' . DOKU_LF .
            DOKU_TAB . '<img class="card-img-top" src="/./lib/exe/fetch.php?w=30&amp;tok=029902&amp;media=allowclipboardhelper.jpg" alt="" width="30">' . DOKU_LF .
            DOKU_TAB . '<div class="card-body">' . DOKU_LF .
            DOKU_TAB . DOKU_TAB . '<h4 class="card-title">Teaser Title</h4>' . DOKU_LF .
            '<p>' . DOKU_LF .
            'A example taken from <a href="https://getbootstrap.com/docs/4.3/components/card/#example" class="urlextern" title="https://getbootstrap.com/docs/4.3/components/card/#example" rel="nofollow">the bootstrap quick example</a> on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</p>' . DOKU_LF .
            DOKU_TAB . '</div>' . DOKU_LF .
            '</div>' . DOKU_LF;


        $this->assertEquals($expected, $xhtml);

    }

    public function test_two_teaser()
    {

        $componentName = syntax_plugin_webcomponent_card::getTag();
        $doku_text = '<' . $componentName . ' style="width: 18rem;">' . DOKU_LF .
            '{{:allowclipboardhelper.jpg?30|}}' . DOKU_LF .
            '=== Teaser Title ===' . DOKU_LF .
            'A example taken from [[https://getbootstrap.com/docs/4.3/components/card/#example|the bootstrap quick example]] on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</' . $componentName . '>' . DOKU_LF;


        $expected = '<div class="card" style="width: 18rem;">' . DOKU_LF .
            DOKU_TAB . '<img class="card-img-top" src="/./lib/exe/fetch.php?w=30&amp;tok=029902&amp;media=allowclipboardhelper.jpg" alt="" width="30">' . DOKU_LF .
            DOKU_TAB . '<div class="card-body">' . DOKU_LF .
            DOKU_TAB . DOKU_TAB . '<h4 class="card-title">Teaser Title</h4>' . DOKU_LF .
            '<p>'.DOKU_LF.
            'A example taken from <a href="https://getbootstrap.com/docs/4.3/components/card/#example" class="urlextern" title="https://getbootstrap.com/docs/4.3/components/card/#example" rel="nofollow">the bootstrap quick example</a> on how to build a card title in order to make up the bulk of the teaser content.' . DOKU_LF .
            '</p>'.DOKU_LF.
            DOKU_TAB . '</div>' . DOKU_LF .
            '</div>' . DOKU_LF;

        $instructions = p_get_instructions($doku_text . $doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);
        $this->assertEquals($expected . $expected, $xhtml);

    }


}
