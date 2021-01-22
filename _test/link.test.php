<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

/**
 *
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\AdsUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/LinkUtility.php');


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
        $html = LinkUtility::renderAsAnchorElement($render,$attributes);
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
        $html = LinkUtility::renderAsAnchorElement($render,$attributes);
        $this->assertEquals("<span>$title</span>",$html,"The html should be the good one");

    }


    /**
     * A link with a button should be in the index
     */
    public function test_indexer()
    {

        // The home page
        $pageIdReferent = PluginUtility::getNameSpace() . 'referrer';
        TestUtility::addPage($pageIdReferent, 'Not null', 'test_indexer test base');

        // The backlinks page
        $pageWithBacklinks = PluginUtility::getNameSpace() . 'test_indexer';
        $element = syntax_plugin_combo_button::getTags()[0];
        $textWithBackLinks = '<' . $element . '>' . '[[' . $pageIdReferent . ']]' . '</' . $element . '>';
        TestUtility::addPage($pageWithBacklinks, $textWithBackLinks, 'test_indexer test base');


        // The test
        $backLinks = ft_backlinks($pageIdReferent);
        $expected = 1;
        $this->assertEquals($expected, sizeof($backLinks), "There should be 1 link in the backlinks");


    }


}
