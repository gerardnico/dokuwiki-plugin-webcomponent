<?php
/**
* Test the class {@link \ComboStrap\ArrayUtility}
*
* @group plugin_combo
* @group plugins
*
*/

use ComboStrap\ArrayUtility;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/ArrayUtility.php');
class plugin_combo_array_test extends DokuWikiTest
{
    public function test_filter_array()
    {
        $keyToNotFilter = "toNotFilter";
        $keyToFilter = "toFilter";
        $arrayToFilter = array(
            "key"=>array(
                $keyToFilter =>"value",
                $keyToNotFilter =>"valueToNotFilter",
            ),
            "key2"=>"valueKey2"
        );
        ArrayUtility::filterArrayByKey($arrayToFilter, "toFi");
        $this->assertArrayHasKey($keyToNotFilter, $arrayToFilter["key"]);
        $this->assertArrayNotHasKey($keyToFilter, $arrayToFilter["key"]);

    }


}
