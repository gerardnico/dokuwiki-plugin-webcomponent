<?php

use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/LinkUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_jumbotron_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_jumbotron_base()
    {

        $text = "<jumbotron spacing=\"mt-3\">Hallo Jumbotron</jumbotron>";
        $expected = '<div class="jumbotron mt-3">Hallo Jumbotron</div>';
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
    }

    public function test_jumbotron_title()
    {

        $text = "<jumbotron spacing=\"mt-3\">" . DOKU_LF
            . "=== Title ===" . DOKU_LF
            . "Hallo Jumbotron" . DOKU_LF
            . "</jumbotron>";
        $expected = '<div class="jumbotron mt-3"><h4 id="title">Title</h4><p>Hallo Jumbotron</p></div></div>';
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
    }


}
