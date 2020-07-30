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

        $text = "<tabs>" . DOKU_LF
            . "<tab panel=\"home\" selected=\"true\">Home</tab>" . DOKU_LF
            . "<tab panel=\"profile\">Profile</tab>" . DOKU_LF
            . "</tabs>" . DOKU_LF
            . "<tabpanels>" . DOKU_LF
            . "<tabpanel id=\"home\">First</tabpanel>" . DOKU_LF
            . "<tabpanel id=\"profile\">Second</tabpanel>" . DOKU_LF
            . "</tabpanels>";
        $expected = "";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            $xhtmlLi
        );

    }

    public function test_tabs_alone()
    {

        $text = "<tabs>" . DOKU_LF
            . "<tab panel=\"home\">" . DOKU_LF
            . "Home" . DOKU_LF
            . "</tab>" . DOKU_LF
            . "<tab panel=\"profile\">" . DOKU_LF
            . "Profile" . DOKU_LF
            . "</tab>" . DOKU_LF
            . "</tabs>";
        $expected = "";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }

    /**
     * Panel is a mandatory attribute
     */
    public function test_tabs_panel_mandatory()
    {

        $text = "<tabs>" . DOKU_LF
            . "<tab>Home</tab>" . DOKU_LF
            . "<tab>Profile</tab>" . DOKU_LF
            . "</tabs>" . DOKU_LF;
        $error = false;
        try {
            PluginUtility::render($text);
        } catch (Exception $e) {
            $error = true;
        }

        $this->assertEquals(
            true,
            $error
        );

    }


}
