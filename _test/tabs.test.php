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
class plugin_combo_tabs_test extends DokuWikiTest
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
    public function test_base()
    {

        $text = "<tabpanels>" . DOKU_LF
            . "<tabpanel id=\"home\">" . DOKU_LF
            . "First" . DOKU_LF
            . "</tabpanel>" . DOKU_LF
            . "<tabpanel id=\"profile\">" . DOKU_LF
            . "Second" . DOKU_LF
            . "</tabpanel>" . DOKU_LF
            . "</tabpanels>";
        $expected = "";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }



}
