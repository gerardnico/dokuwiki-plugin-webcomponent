<?php
/**
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PluginUtility;
use ComboStrap\StyleUtility;
use ComboStrap\TestUtility;
use ComboStrap\UrlCanonical;

require_once(__DIR__ . '/../class/StyleUtility.php');



class plugin_combo_style_utility_test extends DokuWikiTest
{



    /**
     * Test a internal canonical rewrite redirect
     *
     */
    public function test_canonical()
    {

        $styles = array();
        $styles['list-style-type'] = 'none';
        $styles['padding'] = '8px 0'; // Padding on list is 40px left default
        $styles['line-height'] = '1.75rem';
        $styles['border'] = '1px solid #e5e5e5';
        $rule = StyleUtility::getRule($styles,".combo-list");
        $expected = ".combo-list {
    list-style-type:none;
    padding:8px 0;
    line-height:1.75rem;
    border:1px solid #e5e5e5
}
";
        $this->assertEquals($expected,$rule);

    }




}
