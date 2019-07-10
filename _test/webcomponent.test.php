<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_test extends DokuWikiTest
{


    public function test_parse_parameters_base()
    {
        $match = ' class="nico"';
        $parameters = webcomponent::parseMatch($match);
        $this->assertEquals("nico", $parameters["class"]);

    }

    public function test_parse_parameters_no_content()
    {

        $match = ' class="" ';
        $parameters = webcomponent::parseMatch($match);
        $this->assertEquals("", $parameters["class"]);
        $this->assertEquals(true, array_key_exists("class",$parameters));

    }

}