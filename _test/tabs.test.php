<?php

use ComboStrap\TitleUtility;
use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_tabs_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }


    /**
     * Basic
     * @throws Exception
     */
    public function test_base()
    {

        $text = "<tabs>" . DOKU_LF
            . "<tab panel=\"home\" selected=\"true\">Home</tab>" . DOKU_LF
            . "<tab panel=\"profile\">Profile</tab>" . DOKU_LF
            . "</tabs>" . DOKU_LF
            . "<tabpanels>" . DOKU_LF
            . "<tabpanel id=\"home\">First</tabpanel>" . DOKU_LF
            . "<tabpanel id=\"profile\">Second</tabpanel>" . DOKU_LF
            . "</tabpanels>";
        $expected = "<ul class=\"nav nav-tabs\" role=\"tablist\">
  <li panel=\"home\" class=\"nav-item\">
    <a class=\"nav-link active\" aria-selected=\"true\" id=\"home-tab\" data-toggle=\"tab\" aria-controls=\"home\" href=\"#home\">Home</a>
  </li>
  <li panel=\"profile\" class=\"nav-item\">
    <a class=\"nav-link\" id=\"profile-tab\" data-toggle=\"tab\" aria-controls=\"profile\" href=\"#profile\">Profile</a>
  </li>
</ul>
<div class=\"tab-content\" id=\"myTabContent\">
  <div id=\"home\" class=\"tab-pane fade show active\" role=\"tabpanel\" aria-labelledby=\"home-tab\">First</div>
  <div id=\"profile\" class=\"tab-pane fade\" role=\"tabpanel\" aria-labelledby=\"profile-tab\">Second</div>
</div>
";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }

    public function test_tabs_alone()
    {

        $text = "<tabs>" . DOKU_LF
            . "<tab panel=\"home\">" . DOKU_LF
            . "Home" . DOKU_LF
            . "</tab>" . DOKU_LF
            . "<tab panel=\"profile\">" . DOKU_LF
            . "Profile" . DOKU_LF
            . "</tab>" . DOKU_LF
            . "</tabs>";
        $expected = "<ul class=\"nav nav-tabs\" role=\"tablist\">
  <li panel=\"home\" class=\"nav-item\">
    <a class=\"nav-link\" id=\"home-tab\" data-toggle=\"tab\" aria-controls=\"home\" href=\"#home\">Home</a>
  </li>
  <li panel=\"profile\" class=\"nav-item\">
    <a class=\"nav-link\" id=\"profile-tab\" data-toggle=\"tab\" aria-controls=\"profile\" href=\"#profile\">Profile</a>
  </li>
</ul>
";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }

    /**
     * Panel is a mandatory attribute
     */
    public function test_tabs_panel_mandatory()
    {

        $text = "<tabs>" . DOKU_LF
            . "<tab>Home</tab>" . DOKU_LF
            . "<tab>Profile</tab>" . DOKU_LF
            . "</tabs>" . DOKU_LF;
        $error = false;
        try {
            PluginUtility::render($text);
        } catch (Exception $e) {
            $error = true;
        }

        $this->assertEquals(
            true,
            $error
        );

    }

    /**
     * A test with a dokuwiki table
     * @throws Exception
     */
    public function test_with_table()
    {

        $text = "<tabs>" . DOKU_LF
            . "<tab panel=\"home\" selected=\"true\">Home</tab>" . DOKU_LF
            . "<tab panel=\"profile\">Profile</tab>" . DOKU_LF
            . "</tabs>" . DOKU_LF
            . "<tabpanels>" . DOKU_LF
            . "<tabpanel id=\"home\">" . DOKU_LF
            . "^ Header 1 ^ Header 2 ^" . DOKU_LF
            . "| Cell 11 | Cell12 |" . DOKU_LF
            . "</tabpanel>" . DOKU_LF
            . "<tabpanel id=\"profile\">Second</tabpanel>" . DOKU_LF
            . "</tabpanels>";
        $expected = "<ul class=\"nav nav-tabs\" role=\"tablist\">
  <li panel=\"home\" class=\"nav-item\">
    <a class=\"nav-link active\" aria-selected=\"true\" id=\"home-tab\" data-toggle=\"tab\" aria-controls=\"home\" href=\"#home\">Home</a>
  </li>
  <li panel=\"profile\" class=\"nav-item\">
    <a class=\"nav-link\" id=\"profile-tab\" data-toggle=\"tab\" aria-controls=\"profile\" href=\"#profile\">Profile</a>
  </li>
</ul>
<div class=\"tab-content\" id=\"myTabContent\">
  <div id=\"home\" class=\"tab-pane fade show active\" role=\"tabpanel\" aria-labelledby=\"home-tab\">
    <div class=\"table sectionedit1\">
      <table class=\"inline\">
        <thead>
          <tr class=\"row0\"><th class=\"col0\"> Header 1 </th><th class=\"col1\"> Header 2 </th>	</tr>
        </thead>
        <tr class=\"row1\"><td class=\"col0\"> Cell 11 </td><td class=\"col1\"> Cell12 </td>	</tr>
      </table>
    </div>
    <!-- EDIT{&quot;target&quot;:&quot;table&quot;,&quot;name&quot;:&quot;&quot;,&quot;hid&quot;:&quot;table&quot;,&quot;secid&quot;:1,&quot;range&quot;:&quot;129-173&quot;} -->
  </div>
  <div id=\"profile\" class=\"tab-pane fade\" role=\"tabpanel\" aria-labelledby=\"profile-tab\">Second</div>
</div>
";
        $xhtmlLi = PluginUtility::render($text);

        $this->assertEquals(
            $expected,
            TestUtility::normalizeDokuWikiHtml($xhtmlLi)
        );

    }



}
