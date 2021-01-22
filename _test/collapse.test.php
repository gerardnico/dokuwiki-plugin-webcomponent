<?php
/**
* Test the {@link syntax_plugin_combo_navbarcollapse}
*
* @group plugin_combo
* @group plugins
*
*/

use ComboStrap\ArrayUtility;
use ComboStrap\PluginUtility;
use ComboStrap\StringUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');

class plugin_combo_collapse_test extends DokuWikiTest
{
    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function test_collapse_navbar()
    {

        $content = "<navbar><collapse></collapse></navbar>";
        $instructions = p_get_instructions($content);
        $xhtml = p_render('xhtml', $instructions, $info);
        $expected = '<nav class="navbar navbar-expand-lg navbar-light" style="background-color:var(--light)"><div class="container"><button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarcollapse" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation""><span class="navbar-toggler-icon"></span></button><div id="navbarcollapse" class="collapse navbar-collapse"></div></div></nav>';
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml));

    }

    public function test_collapse_navbar_link()
    {

        $content = "<navbar><collapse>[[a:link]]</collapse></navbar>";
        $instructions = p_get_instructions($content);
        $xhtml = p_render('xhtml', $instructions, $info);
        $expected = '<nav class="navbar navbar-expand-lg navbar-light" style="background-color:var(--light)"><div class="container"><button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarcollapse" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation""><span class="navbar-toggler-icon"></span></button><div id="navbarcollapse" class="collapse navbar-collapse"><div class="navbar-nav"><a href="/./doku.php?id=a:link" class="wikilink2 nav-link active" title="a:link" rel="nofollow" data-wiki-id="a:link">link</a></div></div></div></nav>';
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }

    public function test_collapse_attribute()
    {

        $content = "<button collapse=\"#collapseExample\">Button with data-target</button>";
        $result = TestUtility::renderText2Xhtml($content);
        $expected = '<button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample">Button with data-target</button>';
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($result)
        );

    }


}
