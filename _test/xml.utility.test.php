<?php

use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\XmlUtility;

require_once(__DIR__ . '/../class/XmlUtility.php');


/**
 * Test the {@link \ComboStrap\XmlUtility}
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_xml_test extends DokuWikiTest
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




}
