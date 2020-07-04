<?php
/**
* Test the class {@link PluginUtility}
*
* @group plugin_combo
* @group plugins
*
*/

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
class plugin_combo_static_test extends DokuWikiTest
{
    /**
     * Test if an expression is a regular expression pattern
     */
    public function test_expressionIsRegular()
    {

        // Not an expression
        $inputExpression = "Hallo";
        $isRegularExpression = PluginUtility::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(0,$isRegularExpression,"The term (".$inputExpression.") is not a regular expression");

        // A basic expression
        $inputExpression = "/Hallo/";
        $isRegularExpression = PluginUtility::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(true,$isRegularExpression,"The term (".$inputExpression.") is a regular expression");

        // A complicated expression
        $inputExpression = "/(/path1/path2/)(.*)/";
        $isRegularExpression = PluginUtility::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(true,$isRegularExpression,"The term (" . $inputExpression . ") is a regular expression");

    }

}
