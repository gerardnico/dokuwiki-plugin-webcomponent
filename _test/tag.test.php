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
 * Test on node
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;
use ComboStrap\UrlCanonical;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

/**
 * Class plugin_combo_tag_test
 * This is a test class based on the card and blockquote element
 *
 * Each function represents a lexer state, add the function that you want ot test
 * in one of this function.
 *
 */
class plugin_combo_tag_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    /**
     * Test the node function in an {@link DOKU_LEXER_ENTER} state
     * without siblings
     */
    public function test_enter_tag_without_sibling()
    {
        $text = '<card><blockquote warning>' . DOKU_LF
            . ' ' . DOKU_LF
            . '<tag important></tag></blockquote></card>';
        $id = "id_test_node";
        TestUtility::addPage($id, $text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id" => $id));
        $node = $response->queryHTML("tag-enter");
        $this->assertEquals(syntax_plugin_combo_tag::TAG, $node->attr("name"), "name test");
        $this->assertEquals("blockquote", $node->attr("parent"), "parent test");
        $this->assertEquals("warning", $node->attr("parent-type"), "parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"), "child of test");
        $this->assertEquals("false", $node->attr("has-siblings"), "has siblings test");
        $this->assertEquals("true", $node->attr("descendant-of-card"), "descendant test");

        $node = $response->queryHTML("tag-unmatched");
        $this->assertEquals(0, $node->count(), "no unmatched");
        $node = $response->queryHTML("tag-special");
        $this->assertEquals(0, $node->count(), "no special");


    }

    /**
     * Test the node function in an {@link DOKU_LEXER_ENTER} state
     * with sibling (ie a direct sibling in ascendant order)
     */
    public function test_enter_tag_with_sibling()
    {
        $text = '<card>' . DOKU_LF
            . '<blockquote warning>' . DOKU_LF
            . '<header></header>' . DOKU_LF
            . '<tag important></tag>' . DOKU_LF
            . '</blockquote></card>';
        $id = "id_test_sibling";
        TestUtility::addPage($id, $text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id" => $id));
        $node = $response->queryHTML("tag-enter");
        $this->assertEquals("tag", $node->attr("name"), "name test");
        $this->assertEquals("important", $node->attr("type"), "name test");
        $this->assertEquals("blockquote", $node->attr("parent"), "parent test");
        $this->assertEquals("warning", $node->attr("parent-type"), "parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"), "child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"), "descendant test");
        $this->assertEquals("true", $node->attr("has-siblings"), "has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"), "has siblings test");


    }

    /**
     * Test the node function in an {@link DOKU_LEXER_UNMATCHED} state
     */
    public function test_unmatched_tag()
    {
        $text = '<card>' . DOKU_LF
            . '<blockquote warning>' . DOKU_LF
            . '<header></header>' . DOKU_LF
            . '<tag important>Unmatched</tag>' . DOKU_LF
            . '</blockquote></card>';
        $id = "id_test_sibling";
        TestUtility::addPage($id, $text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id" => $id));
        $node = $response->queryHTML("tag-enter");
        $this->assertEquals(syntax_plugin_combo_tag::TAG, $node->attr("name"), "enter name test");
        $this->assertEquals("important", $node->attr("type"), "enter type");
        $this->assertEquals("blockquote", $node->attr("parent"), "enter parent test");
        $this->assertEquals("warning", $node->attr("parent-type"), "enter parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"), "enter child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"), "enter descendant test");
        $this->assertEquals("true", $node->attr("has-siblings"), "enter has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"), "has siblings test");
        $node = $response->queryHTML("tag-unmatched");
        $this->assertEquals(syntax_plugin_combo_tag::TAG, $node->attr("name"), "unmatched name test");
        $this->assertEquals("important", $node->attr("type"), "unmatched type");
        $this->assertEquals("tag", $node->attr("parent"), "unmatched parent test");
        $this->assertEquals("important", $node->attr("parent-type"), "unmatched parent-type test");
        $this->assertEquals("false", $node->attr("child-of-blockquote"), "unmatched child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"), "unmatched descendant test");
        $this->assertEquals("false", $node->attr("has-siblings"), "unmatched has siblings test");
        $this->assertEquals("false", $node->attr("first-sibling"), "unmatched first siblings test");


    }

    /**
     * Test a special tag
     */
    public function test_special_tag()
    {
        $text = '<card>' . DOKU_LF
            . '<blockquote warning>' . DOKU_LF
            . '<header></header>' . DOKU_LF
            . '<tag important />' . DOKU_LF
            . '</blockquote>' . DOKU_LF
            . '</card>';
        $id = "id_test_sibling";
        TestUtility::addPage($id, $text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id" => $id));
        $node = $response->queryHTML("tag-special");
        $this->assertEquals(syntax_plugin_combo_tag::TAG, $node->attr("name"), "special name test");
        $this->assertEquals("important", $node->attr("type"), "special type");
        $this->assertEquals("blockquote", $node->attr("parent"), "special parent test");
        $this->assertEquals("warning", $node->attr("parent-type"), "special parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"), "special child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"), "special descendant test");
        $this->assertEquals("true", $node->attr("has-siblings"), "special has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"), "special siblings test");

    }


    /**
     * Test the node function in an {@link DOKU_LEXER_EXIT} state
     */
    public function test_exit_tag()
    {
        $text = '<card>' . DOKU_LF
            . '<blockquote warning>' . DOKU_LF
            . '<header></header>' . DOKU_LF
            . '<tag important>Unmatched [[url|url]]' . DOKU_LF
            . '<badge>Hallo [[url|url]]</badge>'
            . '</tag>' . DOKU_LF
            . '</blockquote></card>';
        $id = "id_test_sibling";
        TestUtility::addPage($id, $text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id" => $id));
        $node = $response->queryHTML("tag-exit");
        $this->assertEquals(syntax_plugin_combo_tag::TAG, $node->attr("name"), "exit name test");
        $this->assertEquals("", $node->attr("type"), "exit type");
        $this->assertEquals("blockquote", $node->attr("parent"), "exit parent test");
        $this->assertEquals("warning", $node->attr("parent-type"), "exit parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"), "exit child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"), "exit descendant test");
        $this->assertEquals("true", $node->attr("has-siblings"), "exit has siblings test");
        $this->assertEquals("true", $node->attr("has-descendants"), "exit has descendant test");
        $this->assertEquals("6", $node->attr("descendants-count"), "exit descendant count test");
        $this->assertEquals("true", $node->attr("has-badge-descendant"), "exit badge descendant test");
        $this->assertEquals("Hallo [[url|url]]", $node->attr("badge-content"), "exit badge content test");


    }


}
