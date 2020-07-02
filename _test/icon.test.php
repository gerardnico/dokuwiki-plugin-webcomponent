<?php


/**
 * Tests over the icon syntax {@link syntax_plugin_webcomponent_icon}
 *
 * @group plugin_webcomponent
 * @group plugins
 */
require_once (__DIR__.'/../class/PluginStatic.php');

final class plugin_webcomponent_icon_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginStatic::$PLUGIN_BASE_NAME;
        parent::setUp();
    }

    /**
     *
     *
     */
    public function test_iconTest()
    {

        $iconPage = "icon:test";

        $expectedClassValue = "btn-dark";
        $expectedStyleValue = "color:red";
        saveWikiText($iconPage, '<icon name="logo.svg" class="'.$expectedClassValue.'" style="'.$expectedStyleValue.'"/>', '');
        idx_addPage($iconPage);


        TestUtils::rcopy(dirname(DOKU_CONF).'/data/media', PluginStatic::$DIR_RESOURCES . '/logo.svg');

        $request = new TestRequest();
        $response = $request->get(array('id' => $iconPage), '/doku.php');
        $svgElements = $response->queryHTML('div[data-name="logo.svg"]');
        $this->assertEquals(1,$svgElements->count(),"The icon is present");
        $classValue = $svgElements->attr("class");
        $this->assertEquals($expectedClassValue ,$classValue,"The class is present");
        $styleValue = $svgElements->attr("style");
        $this->assertEquals($expectedStyleValue ,$styleValue,"The style is present");

    }

}
