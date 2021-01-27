<?php
/**
 * Copyright (c) 2021. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */


use ComboStrap\PluginUtility;


require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');

class syntax_plugin_combo_preformattedTest extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;

        parent::setUp();

    }

    public function testRenderPreformattedEnabled()
    {
        global $conf;
        $conf['plugin'][PluginUtility::PLUGIN_BASE_NAME][syntax_plugin_combo_preformatted::CONF_PREFORMATTED_ENABLE]=1;
        $text = "  Hallo\n";
        $html = TestUtility::renderText2Xhtml($text);
        $expected = "<pre class=\"code\">Hallo</pre>\n";
        $this->assertEquals($expected, $html);
    }

    /**
     * Default
     */
    public function testRenderPreformattedDisabled()
    {
        $text = "  Hallo";
        $html = TestUtility::renderText2Xhtml($text);
        $expected = '
  Hallo';
        $this->assertEquals($expected, $html);
    }

}
