<?php


use ComboStrap\PluginUtility;

require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'/PluginUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_hr_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    public function test_hr_html_syntax()
    {

        $text = "<hr>";
        $expected = "<hr/>";
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
