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



class plugin_combo_node_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    /**
     * Test node without siblings
     */
    public function test_node()
    {
        $text = '<card><blockquote warning>'.DOKU_LF
            . ' '.DOKU_LF
            . '<node important></node></blockquote></card>';
        $id = "idTestNode";
        TestUtility::addPage($id,$text);
        $testRequest = new TestRequest();
        $response = $testRequest->get(array("id"=>$id));
        $node =  $response->queryHTML("node");
        $this->assertEquals("node", $node->attr("name"),"name test");
        $this->assertEquals("blockquote", $node->attr("parent"),"parent test");
        $this->assertEquals("warning", $node->attr("parent-type"),"parent-type test");
        $this->assertEquals("1", $node->attr("child-of-blockquote"),"child of test");
        $this->assertEquals("0", $node->attr("has-siblings"),"has siblings test");
        $this->assertEquals("1", $node->attr("descendant-of-card"),"descendant test");


    }

    /**
     * Test node with sibling (ie a direct sibling in ascendant order)
     */
    public function test_sibling_node()
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
        $node =  $response->queryHTML("node");
        $this->assertEquals("node", $node->attr("name"),"name test");
        $this->assertEquals("blockquote", $node->attr("parent"),"parent test");
        $this->assertEquals("warning", $node->attr("parent-type"),"parent-type test");
        $this->assertEquals("1", $node->attr("child-of-blockquote"),"child of test");
        $this->assertEquals("1", $node->attr("descendant-of-card"),"descendant test");
        $this->assertEquals("1", $node->attr("has-siblings"),"has siblings test");
        $this->assertEquals("header", $node->attr("first-sibling"),"has siblings test");


    }
}
