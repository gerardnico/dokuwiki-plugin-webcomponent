<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/code

// must be run within Dokuwiki
use ComboStrap\StringUtility;
use ComboStrap\Tag;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/StringUtility.php');

if (!defined('DOKU_INC')) die();


class syntax_plugin_combo_code extends DokuWiki_Syntax_Plugin
{

    /**
     * Enable or disable the code component
     */
    const CONF_CODE_ENABLE = 'codeEnable';
    const CODE_TAG = "code";
    const SCRIPT_ID = 'combo_prism';
    const SCRIPT_CONTENT = <<<EOD
<style>
@import url("https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/themes/prism-tomorrow.min.css");
@import url("https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/toolbar/prism-toolbar.min.css");
/*https://prismjs.com/plugins/command-line/*/
@import url("https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/command-line/prism-command-line.min.css");
/*https://prismjs.com/plugins/line-numbers/*/
@import url("https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/line-numbers/prism-line-numbers.min.css");
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/toolbar/prism-toolbar.min.js"></script>
<!--https://prismjs.com/plugins/normalize-whitespace/-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/normalize-whitespace/prism-normalize-whitespace.min.js"></script>
<!--https://prismjs.com/plugins/show-language/-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/show-language/prism-show-language.min.js"></script>
<!--https://prismjs.com/plugins/command-line/-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/command-line/prism-command-line.min.js"></script>
<!--https://prismjs.com/plugins/line-numbers/-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
<script type="application/javascript">
document.addEventListener('DOMContentLoaded', (event) => {
  document.querySelectorAll('pre code').forEach((block) => {
    //
  });
});
(function(){
	if (typeof self === 'undefined' || !self.Prism || !self.document) {
		return;
	}


	Prism.plugins.NormalizeWhitespace.setDefaults({
        'remove-trailing': true,
        'remove-indent': true,
        'left-trim': true,
        'right-trim': true,
    });

	if (!Prism.plugins.toolbar) {
		console.warn('Copy to Clipboard plugin loaded before Toolbar plugin.');

		return;
	}

	let ClipboardJS = window.ClipboardJS || undefined;

	if (!ClipboardJS && typeof require === 'function') {
		ClipboardJS = require('clipboard');
	}

	var callbacks = [];

	if (!ClipboardJS) {
		var script = document.createElement('script');
		var head = document.querySelector('head');

		script.onload = function() {
			ClipboardJS = window.ClipboardJS;

			if (ClipboardJS) {
				while (callbacks.length) {
					callbacks.pop()();
				}
			}
		};

		script.src = 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js';
		head.appendChild(script);
	}

	Prism.plugins.toolbar.registerButton('copy-to-clipboard', function (env) {
		var linkCopy = document.createElement('button');
		linkCopy.textContent = 'Copy';
		linkCopy.setAttribute('type', 'button');

		var element = env.element;

		if (!ClipboardJS) {
			callbacks.push(registerClipboard);
		} else {
			registerClipboard();
		}

		return linkCopy;

		function registerClipboard() {
			var clip = new ClipboardJS(linkCopy, {
				'text': function () {
					return element.textContent;
				}
			});

			clip.on('success', function() {
				linkCopy.textContent = 'Copied!';

				resetText();
			});
			clip.on('error', function () {
				linkCopy.textContent = 'Press Ctrl+C to copy';

				resetText();
			});
		}

		function resetText() {
			setTimeout(function () {
				linkCopy.textContent = 'Copy';
			}, 5000);
		}
	});
})();
</script>
EOD;
    /**
     * To retain the information if the prism library was already added
     * @var array
     */
    private $indicatorsCodeAlreadyAdded = array();

    /**
     * @var string[] https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/themes/prism-{theme}.min.css
     *
     * or default
     *
     * https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/themes/prism.min.css
     *
     * or
     *
     * https://github.com/PrismJS/prism-themes
     */
    private $themes = array(
        "coy",
        "dark",
        "funky",
        "okaidia",
        "solarizedlight",
        "tomorrow",
        "twilight"
    );


    function getType()
    {
        /**
         * You can't write in a code block
         */
        return 'protected';
    }

    /**
     * How DokuWiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs
     *  * 'block'  - Open paragraphs need to be closed before plugin output - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     */
    function getPType()
    {
        return 'block';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('baseonly','container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     *
     * Return an array of one or more of the mode types {@link $PARSER_MODES} in Parser.php
     */
    function getAllowedTypes()
    {
        return array();
    }

    function getSort()
    {
        /**
         * Should be less than the code syntax plugin
         * which is 200
         **/
        return 199;
    }


    function connectTo($mode)
    {

        if ($this->getConf(self::CONF_CODE_ENABLE)) {
            $pattern = PluginUtility::getContainerTagPattern(self::CODE_TAG);
            $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }

    }


    function postConnect()
    {

        $this->Lexer->addExitPattern('</' . self::CODE_TAG . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));

    }

    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @param string $match
     * @param int $state
     * @param int $pos - byte position in the original source file
     * @param Doku_Handler $handler
     * @return array|bool
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER :
                $tagAttributes = PluginUtility::getTagAttributes($match);
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::ATTRIBUTES => $tagAttributes
                );

            case DOKU_LEXER_UNMATCHED :
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => $match
                );

            case DOKU_LEXER_EXIT :
                return array(PluginUtility::STATE => $state);


        }
        return array();

    }

    /**
     * Render the output
     * @param string $format
     * @param Doku_Renderer $renderer
     * @param array $data - what the function handle() return'ed
     * @return boolean - rendered correctly? (however, returned value is not used at the moment)
     * @see DokuWiki_Syntax_Plugin::render()
     *
     *
     */
    function render($format, Doku_Renderer $renderer, $data)
    {

        if ($format == 'xhtml') {

            /** @var Doku_Renderer_xhtml $renderer */
            $state = $data [PluginUtility::STATE];
            switch ($state) {
                case DOKU_LEXER_ENTER :

                    /**
                     * Add prism
                     */

                    if (!PluginUtility::htmlSnippetAlreadyAdded($this->indicatorsCodeAlreadyAdded)) {
                        $renderer->doc .= '<div id="' . self::SCRIPT_ID . '">' . DOKU_LF;
                        $renderer->doc .= self::SCRIPT_CONTENT;
                        $renderer->doc .= '</div>' . DOKU_LF;
                    }

                    /**
                     * Add HTML
                     */
                    $attributes = $data[PluginUtility::ATTRIBUTES];
                    $language = $attributes["type"];
                    StringUtility::addEolIfNotPresent($renderer->doc);
                    PluginUtility::addClass2Attributes('language-' . $language, $attributes);
                    PluginUtility::addClass2Attributes('line-numbers', $attributes);
                    $htmlCode = '<pre>' . DOKU_LF;
                    $htmlCode .= '<code ';
                    $inlineAttributes = PluginUtility::array2HTMLAttributes($attributes);
                    $htmlCode .= $inlineAttributes . ' >' . DOKU_LF;
                    $renderer->doc .= $htmlCode;
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= PluginUtility::escape($data[PluginUtility::PAYLOAD]) . DOKU_LF;
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '</code>' . DOKU_LF . '</pre>' . DOKU_LF;
                    break;

            }
            return true;
        }

        // unsupported $mode
        return false;
    }


}

