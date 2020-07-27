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

        $text = "<ntoc>" . DOKU_LF
            ."<file-item>Bla</file-item>"
            . "</ntoc>";
        $expected = "";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }


}
