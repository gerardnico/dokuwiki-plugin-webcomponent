<?php

use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/LinkUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the link utility
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_link_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     * Test the internal link
     */
    public function test_internal_link()
    {

        $id = 'namespace:page';
        $fragment = '#section';
        $qualifiedId = $id.$fragment;
        $title = 'title';
        $dokuInternalLink = '[['.$qualifiedId.'|' . $title . ']]';
        $attributes = LinkUtility::getAttributes($dokuInternalLink);
        $this->assertEquals(LinkUtility::TYPE_INTERNAL,$attributes[LinkUtility::ATTRIBUTE_TYPE],"It should be the good type");
        $this->assertEquals($qualifiedId,$attributes[LinkUtility::ATTRIBUTE_ID],"It should be the good id");
        $this->assertEquals($title,$attributes[LinkUtility::ATTRIBUTE_TITLE],"It should be the good title");

        $render = new Doku_Renderer_xhtml();
        $html = LinkUtility::renderHTML($render,$attributes);
        $expectedHtml = '<a href="/./doku.php?id='.$qualifiedId.'" class="wikilink2" title="'.$id.'" rel="nofollow" data-wiki-id="'.$id.'">'.$title.'</a>';
        $this->assertEquals($expectedHtml,$html,"The html should be the good one");
    }

    public function test_interwiki_link()
    {
        $id = "doesnotexist>foo";
        $title = "Title";
        $link = "[[{$id}|{$title}]]";
        $attributes = LinkUtility::getAttributes($link);
        $this->assertEquals(3, sizeof($attributes));
        $this->assertEquals(LinkUtility::TYPE_INTERWIKI,$attributes[LinkUtility::ATTRIBUTE_TYPE],"It should be the good type");
        $this->assertEquals($id,$attributes[LinkUtility::ATTRIBUTE_ID],"It should be the good id");
        $this->assertEquals($title,$attributes[LinkUtility::ATTRIBUTE_TITLE],"It should be the good title");

        $render = new Doku_Renderer_xhtml();
        $html = LinkUtility::renderHTML($render,$attributes);
        $this->assertEquals("<span>$title</span>",$html,"The html should be the good one");

    }





}
