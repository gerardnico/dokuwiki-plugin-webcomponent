<?php

use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;

use ComboStrap\XmlUtility;

require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_title_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     * @throws Exception
     */
    public function test_title_base()
    {

        $text = "<title>Title</title>";
        $expected = "<h1>Title</h1>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            XmlUtility::normalize($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
        $text = "<title level='2'>Title</title>";
        $expected = "<h2>Title</h2>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            XmlUtility::normalize($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
    }

    /**
     * @throws Exception
     */
    public function test_title_type()
    {

        $text = "<title 0>Title</title>";
        $expected = "<h1>Title</h1>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            XmlUtility::normalize($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
        $text = "<title 1>Title</title>";
        $expected = "<h1 class=\"display-1\">Title</h1>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            XmlUtility::normalize($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );
    }




}
