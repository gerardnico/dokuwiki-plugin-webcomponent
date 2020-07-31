<?php

use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_list_ntoc extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     * Basic
     * @throws Exception
     */
    public function test_ntoc()
    {

        /**
         * We add a page to test if the page
         * does not get a backlink from the ntoc
         */
        $page = "page";
        TestUtility::addPage($page,"Text");

        /**
         * The ntoc
         */
        $text = "<ntoc ns=':'>" . DOKU_LF
            . "<page-item>[[\$id|\$title]]</page-item>"
            . "</ntoc>";

        /**
         * What we expect
         */
        $expected = "<ul class=\"combo-list\">
  <li class=\"combo-list-item\">
    <a href=\"/./doku.php?id=int\" class=\"wikilink2\" title=\"int\" rel=\"nofollow\" data-wiki-id=\"int\">int</a>
  </li>
  <li class=\"combo-list-item\">
    <a href=\"/./doku.php?id=wiki\" class=\"wikilink2\" title=\"wiki\" rel=\"nofollow\" data-wiki-id=\"wiki\">wiki</a>
  </li>
  <li class=\"combo-list-item\">
    <a href=\"/./doku.php?id=mailinglist\" class=\"\" title=\"mailinglist\" data-wiki-id=\"mailinglist\">mailinglist</a>
  </li>
  <li class=\"combo-list-item\">
    <a href=\"/./doku.php?id=page\" class=\"\" title=\"page\" data-wiki-id=\"page\">page</a>
  </li>
  <li class=\"combo-list-item\">
    <a href=\"/./doku.php?id=sidebar\" class=\"\" title=\"sidebar\" data-wiki-id=\"sidebar\">sidebar</a>
  </li>
</ul>
";
        /**
         * Render
         */
        TestUtility::addPage("sidebar",$text);
        $xhtmlLi = TestUtility::renderText2Xhtml($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

        /**
         * No backlinks please
         */
        $backLinks = ft_backlinks($page);
        $expected = 0;
        $this->assertEquals($expected, sizeof($backLinks), "There should be no backlink from the ntoc component");

    }


}
