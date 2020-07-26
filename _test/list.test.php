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
class plugin_combo_list_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        global $conf;
        parent::setUp();
        $conf['template'] = 'strap';
    }


    /**
     * @throws Exception
     */
    public function test_list()
    {

        $text = "<list>" . DOKU_LF
            . "<li>" . DOKU_LF
            . "Title" . DOKU_LF
            . "</li>" . DOKU_LF
            . "</list>";
        $expected = "<ul><li>Title</li></ul>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeComboXml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

        $text = "<list>" . DOKU_LF
            . "<list-item>" . DOKU_LF
            . "Title" . DOKU_LF
            . "</list-item>" . DOKU_LF
            . "</list>";
        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeComboXml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }


}
