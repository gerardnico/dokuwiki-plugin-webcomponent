<?php

use ComboStrap\PluginUtility;
use ComboStrap\StringUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/StringUtility.php');

/**
 * Test the component plugin {@link ComboStrap\PluginUtility} class
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_plugin_utility_test extends DokuWikiTest
{


    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function test_parse_parameters_base()
    {
        $match = ' class="nico"';
        $parameters = PluginUtility::parse2HTMLAttributes($match);
        $this->assertEquals("nico", $parameters["class"]);

    }

    public function test_getAdminPage()
    {
        $expectedAdminPage = 'pluginname_page';
        $class = 'admin_plugin_' . $expectedAdminPage . '';
        $adminPageName = PluginUtility::getAdminPageName($class);
        $this->assertEquals($expectedAdminPage, $adminPageName,"The admin page name from the class is the good one");

    }

    public function test_parse_parameters_no_content()
    {

        $match = '<NOTE tip>';
        $attributes = PluginUtility::getTagAttributes($match);
        $this->assertArrayHasKey("type", $attributes);
        $this->assertEquals("tip", $attributes["type"]);

        $match = '<NOTE >';
        $attributes = PluginUtility::getTagAttributes($match);
        $this->assertArrayNotHasKey("type", $attributes);


    }

    public function test_get_request_script()
    {


        // A call to the web server set that
        $_SERVER["SCRIPT_NAME"]="/doku.php";
        $requestScript = PluginUtility::getRequestScript();
        $this->assertEquals("doku.php", $requestScript);


        // A call to the test framework set that
        $_SERVER["DOCUMENT_URI"]="/doku.php";
        $requestScript = PluginUtility::getRequestScript();
        $this->assertEquals("doku.php", $requestScript);

        // With css
        $_SERVER["DOCUMENT_URI"]="/lib/exe/css.php";
        $requestScript = PluginUtility::getRequestScript();
        $this->assertEquals("css.php", $requestScript);

        // With test property
        TestUtility::setTestProperty("SCRIPT_NAME", "css2.php");
        $requestScript = PluginUtility::getRequestScript();
        $this->assertEquals("css2.php", $requestScript);

    }

    public function test_get_property()
    {

        // A call to the web server set that
        $name = "wahtever";
        $value = PluginUtility::getPropertyValue($name);
        $this->assertEquals(null, $value);

        // A call to the web server set that

        $expectedValue = "value";
        TestUtility::setTestProperty($name, $expectedValue);
        $value = PluginUtility::getPropertyValue($name);
        $this->assertEquals($expectedValue, $value);


    }

    public function test_get_url()
    {
        global $conf;
        $conf['template']='strap';
        $html = PluginUtility::getUrl("", PluginUtility::$PLUGIN_NAME);
        $strpos = strpos($html,"<svg");
        $this->assertNotFalse($strpos,"There is an icon");

    }


    public function test_parse_parameters_type()
    {

        $match = ' class="mx-auto" background-color="purple"';
        $parameters = PluginUtility::parse2HTMLAttributes($match);
        $this->assertEquals("mx-auto", $parameters["class"]);
        $this->assertEquals("purple", $parameters["background-color"]);
        $this->assertEquals(true, array_key_exists("class",$parameters));

    }


    public function test_get_attributes()
    {

        /**
         * Leaf tag
         */
        $classes = "class1 class2";
        $style = 'width:12rem; height:13rem';
        $match = '<icon class="'.$classes. '" style="' . $style . '" whatever="yolo"/>';
        $parameters = PluginUtility::getTagAttributes($match);

        $this->assertEquals($classes, $parameters["class"]);
        $this->assertEquals($style, $parameters["style"]);
        $this->assertEquals("yolo", $parameters["whatever"]);

        /**
         * Container tag
         */
        $match = '<icon class="'.$classes. '" style="' . $style . '" whatever="yolo">';
        $parameters = PluginUtility::getTagAttributes($match);

        $this->assertEquals($classes, $parameters["class"]);
        $this->assertEquals($style, $parameters["style"]);
        $this->assertEquals("yolo", $parameters["whatever"]);


        /**
         * Leaf Container tag
         */
        $match = '<math class="'.$classes. '" style="' . $style . '" whatever="yolo">x^2</math>';
        $parameters = PluginUtility::getTagAttributes($match);

        $this->assertEquals($classes, $parameters["class"]);
        $this->assertEquals($style, $parameters["style"]);
        $this->assertEquals("yolo", $parameters["whatever"]);


        /**
         * No attributes
         */
        $attributes = PluginUtility::getTagAttributes('<cite>');
        $this->assertEquals(0, sizeof($attributes),"No attributes, array is null");



    }

    public function test_get_content()
    {

        $expectedContent = "x^2";
        $match = '<math whatever="yolo">'.$expectedContent.'</math>';
        $content = PluginUtility::getTagContent($match);
        $this->assertEquals($expectedContent, $content);

        // A substituion leaf tag has no content
        $error = null;
        try {
            $match = '<math whatever="yolo"/>';
            PluginUtility::getTagContent($match);
        } catch (Exception $e){
            $error = $e;
        }
        $this->assertNotNull($error);

        // A Passing only the first tag will not work also
        $error = null;
        try {
            $match = '<math whatever="yolo">';
            PluginUtility::getTagContent($match);
        } catch (Exception $e){
            $error = $e;
        }
        $this->assertNotNull($error);


    }

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
