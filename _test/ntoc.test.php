<?php

use ComboStrap\StyleUtility;
use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;


require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');

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
         * https://www.dokuwiki.org/config:useheading
         */
        global $conf;
        $conf['useheading'] = 1;

        /**
         * We add a page to test if the page
         * does not get a backlink from the ntoc
         */
        $page = "page";
        TestUtility::addPage($page, "Text");

        /**
         * Create a sub page
         */
        $nspage = "ns:start";
        $title = "Title";
        TestUtility::addPage($nspage, "====== {$title} ======" . DOKU_LF);

        /**
         * The ntoc
         */
        $text = "<ntoc ns=':'>" . DOKU_LF
            . "<ns-item>[[\$id|\$title]]</ns-item>"
            . "<page-item>[[\$id|\$title]]</page-item>"
            . "</ntoc>";

        /**
         * What we expect
         */
        $styleListItem = '<style>' . StyleUtility::getRule(syntax_plugin_combo_listitem::getStyles(), "." . syntax_plugin_combo_listitem::COMBO_LIST_ITEM_CLASS) . '</style>';
        $styleList = '<style>' . StyleUtility::getRule(syntax_plugin_combo_list::getStyles(), "." . syntax_plugin_combo_list::COMBO_LIST_CLASS) . '</style>';
        $expected = <<<EOF
{$styleList}
<ul class="combo-list">
{$styleListItem}
  <li class="combo-list-item">
    <a href="/./doku.php?id=$nspage" class="" title="ns:start" data-wiki-id="$nspage">$title</a>
  </li>
  <li class="combo-list-item">
    <a href="/./doku.php?id=mailinglist" class="" title="mailinglist" data-wiki-id="mailinglist">Mailing Lists</a>
  </li>
  <li class="combo-list-item">
    <a href="/./doku.php?id=page" class="" title="page" data-wiki-id="page">page</a>
  </li>
</ul>
EOF;
        /**
         * Render
         */
        TestUtility::addPage("sidebar", $text);
        $xhtmlLi = TestUtility::renderText2Xhtml($text);
        $htmlDiff = TestUtility::HtmlDiff($expected, $xhtmlLi);
        $this->assertEquals("", $htmlDiff);

        if ($htmlDiff != "") {
            $this->assertEquals($expected, $xhtmlLi);
        }

        /**
         * No backlinks please
         */
        $backLinks = ft_backlinks($page);
        $expected = 0;
        $this->assertEquals($expected, sizeof($backLinks), "There should be no backlink from the ntoc component");

    }


}
