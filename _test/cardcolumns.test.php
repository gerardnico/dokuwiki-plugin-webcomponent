<?php

require_once(__DIR__ . '/../webcomponent.php');
/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_cardcolumns_test extends DokuWikiTest
{

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_component_name() {

        $componentNames = syntax_plugin_webcomponent_cardcolumns::getTags();
        $tags = array (
            'card-columns',
            'teaser-columns'
        );
        $this->assertEquals($tags, $componentNames);

    }

    public function test_base() {

        $componentName = syntax_plugin_webcomponent_cardcolumns::getTags()[0];
        $doku_text = '<'. $componentName .'>'.DOKU_LF.
            '<'.syntax_plugin_webcomponent_card::getTag().' style="width: 18rem;">'.DOKU_LF.
            '===== Title ====='.DOKU_LF.
            'Teaser Text'.DOKU_LF.
            '</'.syntax_plugin_webcomponent_card::getTag().'>'.DOKU_LF.
            '</'.$componentName.'>';

        $info = array();

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);

        $expected = '<div class="card-columns">'.DOKU_LF.
            '<div class="card" style="width: 18rem;">'.DOKU_LF.
            DOKU_TAB.'<div class="card-body">'.DOKU_LF.
            DOKU_TAB.DOKU_TAB.'<h2 class="card-title">Title</h2>'.DOKU_LF.
            '<p>'.DOKU_LF.
            'Teaser Text'.DOKU_LF.
            '</p>'.DOKU_LF.
            DOKU_TAB.'</div>'.DOKU_LF.
            '</div>'.DOKU_LF.
            '</div>'.DOKU_LF;


        $this->assertEquals($expected, $xhtml);

    }





}
