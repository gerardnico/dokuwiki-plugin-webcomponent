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
use ComboStrap\Prism;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/Prism.php');

class dokuwiki_plugin_combo_file_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function testFileOutput()
    {
        $extra = 'After';
        /**
         * We add file two times to check that the script is only added once
         */
        $text = '<file html><code html></code></file>';
        $text .= '<file html><code html></code></file>';
        $text .= $extra;
        $expected = Prism::getSnippet(Prism::PRISM_THEME_DEFAULT);
        $htmlProduced = '<pre><code class="language-html">&lt;code html&gt;&lt;/code&gt;</code></pre>';
        $expected .= $htmlProduced;
        $expected .= $htmlProduced;
        $expected .= '<p>'.$extra.'</p>';

        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }




}