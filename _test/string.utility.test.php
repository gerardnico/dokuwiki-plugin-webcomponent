<?php

use ComboStrap\PluginUtility;
use ComboStrap\StringUtility;
use ComboStrap\TestUtility;


require_once(__DIR__ . '/../class/StringUtility.php');

/**
 * Test the component plugin {@link ComboStrap\PluginUtility} class
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_string_utility_test extends DokuWikiTest
{



    public function test_to_string()
    {
        /**
         * A string variable works without problem
         */
        $var = true;
        $expected = 'true';
        $this->assertEquals($expected, StringUtility::toString($var));

        /**
         * A string variable in an array
         * has commas around the value
         */
        $array = array(
            "key"=>"value"
        );
        $value = $array["key"];

        // It's a string type
        $type = gettype($value);
        $this->assertEquals("string", $type);

        $isObject = is_object($value);
        $this->assertEquals(false, $isObject);

        $expected = "value";
        $this->assertEquals($expected, StringUtility::toString($value));
    }

    public function test_addEol()
    {
        $doc = "Hallo";
        StringUtility::addEolIfNotPresent($doc);
        $this->assertEquals("Hallo".DOKU_LF,$doc);

    }


}
