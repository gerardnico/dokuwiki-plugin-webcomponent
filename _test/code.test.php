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

class dokuwiki_plugin_combo_code_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function testCodeRequest()
    {

        $pageId = "code";
        $codeSnippet = '<code html><code></code></code>';
        /**
         * We add it two times to verify that the script is added only once
         */
        $text = $codeSnippet;
        $text .= $codeSnippet;

        TestUtility::addPage($pageId, $text);

        // In a request
        $request = new TestRequest();
        $testResponse = $request->get(array('id' => $pageId), '/doku.php');
        $div = $testResponse->queryHTML("." . Prism::SCRIPT_CLASS);
        $scriptIdCount = $div->count();
        $this->assertEquals(1, $scriptIdCount, "The number of script  should be one");

        $preElement = $testResponse->queryHTML("pre");
        $this->assertEquals(2, $preElement->count(), "One pre element");

        $codeElement = $testResponse->queryHTML("code");
        $this->assertEquals(2, $codeElement->count(), "One code element");
        $this->assertEquals(true, $codeElement->hasClass("language-html"), "One code element");


    }

    public function testCodeOutput()
    {
        $extra = 'After';
        $text = '<code html line-numbers="true"><file></file></code>';
        $text .= $extra;
        $expected = Prism::getSnippet(Prism::PRISM_THEME_DEFAULT);
        $expected .= '<pre class="combo_code" data-download-link="true" data-src="file.html"><code class="language-html line-numbers combo_code">&lt;file&gt;&lt;/file&gt;</code></pre>';
        $expected .= '<p>'.$extra.'</p>';

        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }

    public function testPromptCodeOutput()
    {
        $extra = 'After';
        $text = '<code bash prompt="#">ls -a</code>';
        $text .= $extra;
        $expected = Prism::getSnippet(Prism::PRISM_THEME_DEFAULT);
        $expected .= '<pre class="combo_code command-line" data-prompt="#" data-download-link="true" data-src="file.bash"><code class="language-bash combo_code">ls -a</code></pre>';
        $expected .= '<p>'.$extra.'</p>';

        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }

    public function testFilePathCodeOutput()
    {
        $extra = 'After';
        $text = '<code bash foo.sh>ls -a</code>';
        $text .= $extra;
        $expected = Prism::getSnippet(Prism::PRISM_THEME_DEFAULT);
        $expected .= '<pre class="combo_code command-line" data-prompt="#" data-download-link="true" data-src="foo.sh" data-download-link-label="Download foo.sh"><code class="language-bash combo_code">ls -a</code></pre>';
        $expected .= '<p>'.$extra.'</p>';

        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }




}
