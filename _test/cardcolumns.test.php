<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_cardcolumns_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_component_name() {

        $componentNames = syntax_plugin_combo_cardcolumns::getTags();
        $tags = array (
            'card-columns',
            'teaser-columns'
        );
        $this->assertEquals($tags, $componentNames);

    }

    public function test_base() {

        $componentName = syntax_plugin_combo_cardcolumns::getTags()[0];
        $doku_text = '<'. $componentName .'>'.DOKU_LF.
            '<'.syntax_plugin_combo_card::getTag().' style="width: 18rem;">'.DOKU_LF.
            '===== Title ====='.DOKU_LF.
            'Teaser Text'.DOKU_LF.
            '</'.syntax_plugin_combo_card::getTag().'>'.DOKU_LF.
            '</'.$componentName.'>';

        $info = array();

        $instructions = p_get_instructions($doku_text);
        $xhtml = p_render('xhtml', $instructions, $info);

        $expected = '<div class="card-columns">'.DOKU_LF.
            '<div style="width: 18rem" class="card">'.DOKU_LF.
            DOKU_TAB.'<div class="card-body">'.DOKU_LF.
            DOKU_TAB.DOKU_TAB.'<h2 class="card-title" '.syntax_plugin_combo_cardheader::CARD_TITLE_STYLE.'>Title</h2>'.DOKU_LF.
            '<p>'.DOKU_LF.
            'Teaser Text'.DOKU_LF.
            '</p>'.DOKU_LF.
            DOKU_TAB.'</div>'.DOKU_LF.
            '</div>'.DOKU_LF.
            '</div>'.DOKU_LF;


        $this->assertEquals($expected, $xhtml);

    }





}
