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


class plugin_combo_linkmove_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'move';
        parent::setUp();
    }




    /**
     * A link with a button should be in the index
     * @noinspection DuplicatedCode
     */
    public function test_link_move()
    {

        // The home page
        $pageIdReferent = PluginUtility::getNameSpace() . 'referrer';
        TestUtility::addPage($pageIdReferent, 'Content of referent page', 'test_indexer test base');

        // The backlinks page
        $pageWithBacklinks = PluginUtility::getNameSpace() . 'test_indexer';
        $element = syntax_plugin_combo_button::TAG;
        $textWithBackLinks = '<' . $element . '>' . '[[' . $pageIdReferent . ']]' . '</' . $element . '>';
        //$textWithBackLinks = '[[' . $pageIdReferent . ']]';
        TestUtility::addPage($pageWithBacklinks, $textWithBackLinks, 'test_indexer test base');


        // The test
        $backLinks = ft_backlinks($pageIdReferent);
        $expected = 1;
        $this->assertEquals($expected, sizeof($backLinks), "There should be 1 link in the backlinks");
        $this->assertEquals($pageWithBacklinks, $backLinks[0], "The backlinks is the good one");

        /**
         * Making the move
         */
        /** @var helper_plugin_move_op $MoveOp */
        $pageIdReferentMoved = $pageIdReferent . "moved";
        $MoveOp = plugin_load('helper', 'move_op');
        $result = $MoveOp->movePage($pageIdReferent, $pageIdReferentMoved);

        /**
         * Moved page test
         */
        $this->assertTrue($result);
        $backLinks = ft_backlinks($pageIdReferentMoved);
        $this->assertEquals($expected, sizeof($backLinks), "There should be 1 link in the backlinks");
        $this->assertEquals($pageWithBacklinks, $backLinks[0], "The backlinks is the good one");

        /**
         * Backlink page after the move test
         */
        /** @var helper_plugin_move_rewrite $Rewriter */
        $Rewriter = plugin_load('helper', 'move_rewrite');
        $Rewriter->rewritePage($pageWithBacklinks);

        $pageWithBacklinksContent = rawWiki($pageWithBacklinks);
        $expectedContent = '<' . $element . '>' . '[[' . $pageIdReferentMoved . ']]' . '</' . $element . '>';
        $this->assertEquals($expectedContent, $pageWithBacklinksContent);


    }



}
