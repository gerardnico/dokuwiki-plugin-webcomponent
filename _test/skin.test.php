<?php

use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

/**
 * Test the skin attribute
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_skin_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_skin_syntax()
    {

        $text = "<note skin='outline'>Outlined note</note>";
        $expected = "<div class=\"alert alert-info\" style=\"color:#0c5460;background-color:transparent;border-color:#0c5460\" role=\"note\">Outlined note</div>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }




}
