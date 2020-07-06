<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the component plugin {@link ComboStrap\PluginUtility} class
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_plugin_utility_test extends DokuWikiTest
{


    public function test_parse_parameters_base()
    {
        $match = ' class="nico"';
        $parameters = PluginUtility::parseMatch($match);
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
        $attributes = PluginUtility::getAttributes($match);
        $this->assertArrayHasKey("type", $attributes);
        $this->assertEquals("tip", $attributes["type"]);

        $match = '<NOTE >';
        $attributes = PluginUtility::getAttributes($match);
        $this->assertArrayNotHasKey("type", $attributes);


    }

    public function test_parse_parameters_type()
    {

        $match = ' class="mx-auto" background-color="purple"';
        $parameters = PluginUtility::parseMatch($match);
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
        $parameters = PluginUtility::getAttributes($match);

        $this->assertEquals($classes, $parameters["class"]);
        $this->assertEquals($style, $parameters["style"]);
        $this->assertEquals("yolo", $parameters["whatever"]);

        /**
         * Container tag
         */
        $match = '<icon class="'.$classes. '" style="' . $style . '" whatever="yolo">';
        $parameters = PluginUtility::getAttributes($match);

        $this->assertEquals($classes, $parameters["class"]);
        $this->assertEquals($style, $parameters["style"]);
        $this->assertEquals("yolo", $parameters["whatever"]);

    }

}
