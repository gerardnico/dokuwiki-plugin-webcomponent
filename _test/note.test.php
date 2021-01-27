<?php

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
class plugin_combo_note_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_note_with_title()
    {

        $text = "<note><title 3>Title</title>A note</note>";
        $expected = "<div class=\"alert alert-info\" role=\"note\"><h1 class=\"display-3\">Title</h1>A note</div>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }

    public function test_hr_xhtml_syntax()
    {

        $text = "<hr/>";
        $expected = "<hr/>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
    }

    public function test_hr_spacing()
    {

        $text = "<hr spacing=\"mt-3\">";
        $expected = "<hr class=\"mt-3\">";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
    }



}
