<?php

use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\XmlUtility;

require_once(__DIR__ . '/../class/XmlUtility.php');
require_once(__DIR__ . '/../class/HtmlUtility.php');


/**
 * Test the {@link \ComboStrap\HtmlUtilityUtility}
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_html_test extends DokuWikiTest
{


    /**
     * Test the internal link
     */
    public function test_deleteClass()
    {

        $html = "<div class=\"foo bar\"></div>";
        $xmlElement = new SimpleXMLElement($html);
        XmlUtility::deleteClass("bar",$xmlElement);
        $this->assertEquals("foo",$xmlElement["class"],"The class foo was deleted");


    }

    /**
     * Test the internal link
     */
    public function test_isXml()
    {

        $text = "hello";
        $this->assertFalse(XmlUtility::isXml($text), "The string is not xml");

        $html = "<span>{$text}</span>";
        $this->assertTrue(XmlUtility::isXml($html), "The string with span is xml");

    }

    /**
     * Test the internal link
     */
    public function test_normalized()
    {

        $textWithoutEol = "<div><span></span></div>";
        $textWithEol = "<div><span>".DOKU_LF.DOKU_LF."</span></div>";
        $this->assertEquals(HtmlUtility::normalize($textWithEol), HtmlUtility::format($textWithoutEol), "The string are the same");


    }

    /**
     * Test the internal link
     */
    public function test_format()
    {


        $text = "<div><span></span></div>";
        $text = HtmlUtility::format($text);
        $expected = "<div>".DOKU_LF."  <span/>".DOKU_LF."</div>".DOKU_LF;
        $this->assertEquals($expected,$text, "The HTML is formatted");


    }





}
