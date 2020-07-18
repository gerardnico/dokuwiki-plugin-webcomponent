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
    public function test_enter()
    {
        $text = '<card><blockquote warning>'.DOKU_LF
            . ' '.DOKU_LF
            . '<node important></node></blockquote></card>';
        $id = "idTestNode";
        TestUtility::addPage($id,$text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id"=>$id));
        $node =  $response->queryHTML("enter");
        $this->assertEquals("node", $node->attr("name"),"name test");
        $this->assertEquals("blockquote", $node->attr("parent"),"parent test");
        $this->assertEquals("warning", $node->attr("parent-type"),"parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"),"child of test");
        $this->assertEquals("false", $node->attr("has-siblings"),"has siblings test");
        $this->assertEquals("true", $node->attr("descendant-of-card"),"descendant test");

        $node =  $response->queryHTML("unmatched");
        $this->assertEquals(0, $node->count(),"no unmatched");


    }

    /**
     * Test the node function in an {@link DOKU_LEXER_ENTER} state
     * with sibling (ie a direct sibling in ascendant order)
     */
    public function test_enter_sibling()
    {
        $text = '<card>'.DOKU_LF
            . '<blockquote warning>'.DOKU_LF
            . '<header></header>'.DOKU_LF
            . '<node important></node>'.DOKU_LF
            . '</blockquote></card>';
        $id = "idTestSibling";
        TestUtility::addPage($id,$text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id"=>$id));
        $node =  $response->queryHTML("enter");
        $this->assertEquals("node", $node->attr("name"),"name test");
        $this->assertEquals("important", $node->attr("type"),"name test");
        $this->assertEquals("blockquote", $node->attr("parent"),"parent test");
        $this->assertEquals("warning", $node->attr("parent-type"),"parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"),"child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"),"descendant test");
        $this->assertEquals("true", $node->attr("has-siblings"),"has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"),"has siblings test");


    }

    /**
     * Test the node function in an {@link DOKU_LEXER_UNMATCHED} state
     */
    public function test_unmatched_node()
    {
        $text = '<card>'.DOKU_LF
            . '<blockquote warning>'.DOKU_LF
            . '<header></header>'.DOKU_LF
            . '<node important>Unmatched</node>'.DOKU_LF
            . '</blockquote></card>';
        $id = "idTestSibling";
        TestUtility::addPage($id,$text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id"=>$id));
        $node =  $response->queryHTML("enter");
        $this->assertEquals("node", $node->attr("name"),"enter name test");
        $this->assertEquals("important", $node->attr("type"),"enter type");
        $this->assertEquals("blockquote", $node->attr("parent"),"enter parent test");
        $this->assertEquals("warning", $node->attr("parent-type"),"enter parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"),"enter child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"),"enter descendant test");
        $this->assertEquals("true", $node->attr("has-siblings"),"enter has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"),"has siblings test");
        $node =  $response->queryHTML("unmatched");
        $this->assertEquals("node", $node->attr("name"),"unmatched name test");
        $this->assertEquals("important", $node->attr("type"),"unmatched type");
        $this->assertEquals("blockquote", $node->attr("parent"),"unmatched parent test");
        $this->assertEquals("warning", $node->attr("parent-type"),"unmatched parent-type test");
        $this->assertEquals("true", $node->attr("child-of-blockquote"),"unmatched child of test");
        $this->assertEquals("true", $node->attr("descendant-of-card"),"unmatched descendant test");
        $this->assertEquals("false", $node->attr("has-siblings"),"unmatched has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"),"unmatched first siblings test");


    }
}
