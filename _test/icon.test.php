<?php


/**
 * Tests over the icon syntax {@link syntax_plugin_combo_icon}
 *
 * @group plugin_combo
 * @group plugins
 */

use ComboStrap\IconUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once (__DIR__ . '/../class/PluginUtility.php');
require_once (__DIR__.'/../syntax/icon.php');

final class plugin_combo_icon_test extends DokuWikiTest
{

    public function setUp()
    {

        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        global $conf;
        parent::setUp();

        // Disable cache to test if a icon is no more downloaded
        $conf['cachetime'] = -1;
    }

    /**
     *
     * Icon in media library
     */
    public function test_iconInMediaLibrary()
    {

        $iconPage = "icon:test";

        $expectedClassValue = "btn-dark";
        $expectedStyleValue = "color:red";
        $widthValue = '96px';
        $heightValue = '64px';
        $mediaDir = dirname(DOKU_CONF) . '/data/media';
        TestUtils::rcopy($mediaDir, TestConstant::$DIR_RESOURCES . '/logo.svg');

        TestUtility::addPage($iconPage, '<icon name="logo.svg" width="' . $widthValue . '" height="' . $heightValue . '" class="' .$expectedClassValue.'" style="'.$expectedStyleValue.'"/>', '');




        $request = new TestRequest();
        $response = $request->get(array('id' => $iconPage), '/doku.php');
        $svgElements = $response->queryHTML('svg[data-name="logo.svg"]');
        $this->assertEquals(1,$svgElements->count(),"The icon is present");
        $classValue = $svgElements->attr("class");
        $this->assertEquals($expectedClassValue ,$classValue,"The class is present");
        $styleValue = $svgElements->attr("style");
        $this->assertEquals($expectedStyleValue ,$styleValue,"The style is present");

    }

    /**
     *
     * A illustrator icon given by a user that was
     * creating problem
     */
    public function test_iconIllustratorLibrary()
    {

        $iconPage = "icon:test-bad";

        $name = 'icon-illustrator.svg';

        $expectedClassValue = "btn-dark";
        $expectedStyleValue = "color:red";
        $widthValue = '96px';
        $heightValue = '64px';
        $mediaDir = dirname(DOKU_CONF) . '/data/media';
        TestUtils::rcopy($mediaDir, TestConstant::$DIR_RESOURCES . '/'.$name);


        TestUtility::addPage($iconPage, '<icon name="' . $name . '" width="' . $widthValue . '" height="' . $heightValue . '" class="' .$expectedClassValue.'" style="'.$expectedStyleValue.'"/>', '');




        $request = new TestRequest();
        $response = $request->get(array('id' => $iconPage), '/doku.php');
        $svgElements = $response->queryHTML("svg[data-name='$name']");
        $this->assertEquals(1,$svgElements->count(),"The icon is present");
        $classValue = $svgElements->attr("class");
        $this->assertEquals($expectedClassValue ,$classValue,"The class is present");
        $styleValue = $svgElements->attr("style");
        $this->assertEquals($expectedStyleValue ,$styleValue,"The style is present");

    }

    public function test_iconFromMaterialDesign()
    {

        $iconPage = "icon:test";

        //https://materialdesignicons.com/icon/archive-arrow-up-outline
        $name = 'archive-arrow-up-outline';
        $expectedClassValue = "btn-dark";
        $expectedStyleValue = "color:red";
        $widthValue = '96px';
        $heightValue = '64px';
        TestUtility::addPage($iconPage, '<icon name="'.$name.'" width="' . $widthValue . '" height="' . $heightValue . '" class="' .$expectedClassValue.'" style="'.$expectedStyleValue.'"/>', '');

        TestUtils::rcopy(dirname(DOKU_CONF).'/data/media', TestConstant::$DIR_RESOURCES . '/logo.svg');

        $request = new TestRequest();
        $response = $request->get(array('id' => $iconPage), '/doku.php');
        $svgElements = $response->queryHTML('svg[data-name="' . $name . '"]');
        $this->assertEquals(1,$svgElements->count(),"The icon is present");
        $classValue = $svgElements->attr("class");
        $this->assertEquals($expectedClassValue ,$classValue,"The class is present");
        $styleValue = $svgElements->attr("style");
        $this->assertEquals($expectedStyleValue ,$styleValue,"The style is present");

        // The file should exist
        global $conf;
        $iconNameSpace = $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][IconUtility::CONF_ICONS_MEDIA_NAMESPACE];
        $mediaId = $iconNameSpace . ":" . $name . ".svg";
        $mediaFile = mediaFN($mediaId);
        $fileExist = file_exists($mediaFile);
        $this->assertEquals(true ,$fileExist,"The file exists");

        // An other call should not download the file again
        // The cache was disable at setup
        $stat = stat($mediaFile);
        $expectedModificationTime = $stat['mtime'];
        $request = new TestRequest();
        $response = $request->get(array('id' => $iconPage), '/doku.php');
        $svgElements = $response->queryHTML('svg[data-name="' . $name . '"]');
        $this->assertEquals(1,$svgElements->count(),"The icon is present");
        $stat = stat($mediaFile);
        $modificationTime = $stat['mtime'];
        $this->assertEquals($expectedModificationTime,$modificationTime,"The icon was not modified");

    }

}
