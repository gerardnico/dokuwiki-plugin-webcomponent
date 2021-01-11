<?php

namespace ComboStrap;


use Doku_Renderer_xhtml;

class Prism
{

    const SNIPPET_NAME = 'prism';
    /**
     * The class used to mark the added prism code
     */
    const SCRIPT_CLASS = 'combo_prism';
    const BASE_PRISM_CDN = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/";
    const PRISM_THEME = "prism";

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
     *
     * from https://cdnjs.com/libraries/prism
     */
    const THEMES_INTEGRITY = [
        Prism::PRISM_THEME => "sha512-tN7Ec6zAFaVSG3TpNAKtk4DOHNpSwKHxxrsiw4GHKESGPs5njn/0sMCUMl2svV4wo4BK/rCP7juYz+zx+l6oeQ==",
        "coy" => "sha512-CKzEMG9cS0+lcH4wtn/UnxnmxkaTFrviChikDEk1MAWICCSN59sDWIF0Q5oDgdG9lxVrvbENSV1FtjLiBnMx7Q==",
        "dark" => "sha512-Njdz7T/p6Ud1FiTMqH87bzDxaZBsVNebOWmacBjMdgWyeIhUSFU4V52oGwo3sT+ud+lyIE98sS291/zxBfozKw==",
        "funky" => "sha512-q59Usnbm/Dz3MeqoMEATHqIwozatJmXr/bFurDR7hpB5e2KxU+j2mp89Am9wq9jwZRaikpnKGHw4LP/Kr9soZQ==",
        "okaidia" => "sha512-mIs9kKbaw6JZFfSuo+MovjU+Ntggfoj8RwAmJbVXQ5mkAX5LlgETQEweFPI18humSPHymTb5iikEOKWF7I8ncQ==",
        "solarizedlight" => "sha512-fibfhB71IpdEKqLKXP/96WuX1cTMmvZioYp7T6I+lTbvJrrjEGeyYdAf09GHpFptF8toQ32woGZ8bw9+HjZc0A==",
        "tomorrow" => "sha512-vswe+cgvic/XBoF1OcM/TeJ2FW0OofqAVdCZiEYkd6dwGXthvkSFWOoGGJgS2CW70VK5dQM5Oh+7ne47s74VTg==",
        "twilight" => "sha512-akb4nfKzpmhujLUyollw5waBPeohuVf0Z5+cL+4Ngc4Db+V8szzx6ZTujguFjpmD076W8LImVIbOblmQ+vZMKA=="
    ];

    /**
     * The theme
     */
    const CONF_PRISM_THEME = "prismTheme";
    const PRISM_THEME_DEFAULT = "tomorrow";

    public static function getSnippet($theme)
    {
        $BASE_PRISM_CDN = self::BASE_PRISM_CDN;
        if ($theme == self::PRISM_THEME) {
            $themeStyleSheet = "prism.min.css";
        } else {
            $themeStyleSheet = "prism-$theme.min.css";
        }
        $themeIntegrity = self::THEMES_INTEGRITY[$theme];
        $script = <<<EOD
<script defer="true" src="$BASE_PRISM_CDN/components/prism-core.min.js"></script>
<script defer="true" src="$BASE_PRISM_CDN/plugins/autoloader/prism-autoloader.min.js"></script>
<script defer="true" src="$BASE_PRISM_CDN/plugins/toolbar/prism-toolbar.min.js"></script>
<!--https://prismjs.com/plugins/normalize-whitespace/-->
<script defer="true" src="$BASE_PRISM_CDN/plugins/normalize-whitespace/prism-normalize-whitespace.min.js"></script>
<!--https://prismjs.com/plugins/show-language/-->
<script defer="true" src="$BASE_PRISM_CDN/plugins/show-language/prism-show-language.min.js"></script>
<!--https://prismjs.com/plugins/command-line/-->
<script defer="true" src="$BASE_PRISM_CDN/plugins/command-line/prism-command-line.min.js"></script>
<!--https://prismjs.com/plugins/line-numbers/-->
<script defer="true" src="$BASE_PRISM_CDN/plugins/line-numbers/prism-line-numbers.min.js"></script>
<!--https://prismjs.com/plugins/autolinker/-->
<script defer="true" src="$BASE_PRISM_CDN/plugins/autolinker/prism-autolinker.min.js" integrity="sha512-/uypNVmpEQdCQLYz3mq7J2HPBpHkkg23FV4i7/WSUyEuTJrWJ2uZ3gXx1IBPUyB3qbIAY+AODbanXLkIar0NBQ==" crossorigin="anonymous"></script>
<script defer="true" type="application/javascript">
document.addEventListener('DOMContentLoaded', (event) => {

    if (typeof self === 'undefined' || !self.Prism || !self.document) {
        return;
    }

    // Loading the css from https://cdnjs.com/libraries/prism
    const head = document.querySelector('head');
    const baseCdn = "$BASE_PRISM_CDN";
    const stylesheets = [
        ["themes/$themeStyleSheet", "$themeIntegrity"],
        ["plugins/toolbar/prism-toolbar.min.css","sha512-DSAA0ziYwggOJ3QyWFZhIaU8bSwQLyfnyIrmShRLBdJMtiYKT7Ju35ujBCZ6ApK3HURt34p2xNo+KX9ebQNEPQ=="],
        /*https://prismjs.com/plugins/command-line/*/
        ["plugins/command-line/prism-command-line.min.css","sha512-4Y1uID1tEWeqDdbb7452znwjRVwseCy9kK9BNA7Sv4PlMroQzYRznkoWTfRURSADM/SbfZSbv/iW5sNpzSbsYg=="],
        /*https://prismjs.com/plugins/line-numbers/*/
        ["plugins/line-numbers/prism-line-numbers.min.css","sha512-cbQXwDFK7lj2Fqfkuxbo5iD1dSbLlJGXGpfTDqbggqjHJeyzx88I3rfwjS38WJag/ihH7lzuGlGHpDBymLirZQ=="]
    ];

    stylesheets.forEach(stylesheet => {
            let link = document.createElement('link');
            link.rel="stylesheet"
            link.href=baseCdn+"/"+stylesheet[0];
            link.integrity=stylesheet[1];
            link.crossOrigin="anonymous";
            head.append(link);
        }
    )


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

    const callbacks = [];

    if (!ClipboardJS) {
        const script = document.createElement('script');
        const head = document.querySelector('head');

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

});

</script>
EOD;

        return '<div class="' . Prism::SCRIPT_CLASS . '">' . DOKU_LF
            . $script . DOKU_LF
            . '</div>' . DOKU_LF;

    }

    /**
     * Add the first block of prism
     * @param \Doku_Renderer_xhtml $renderer
     * @param $attributes
     * @param $theme
     * @param $addedClass - this class is added to the added node
     */
    public static function htmlEnter(\Doku_Renderer_xhtml $renderer, $attributes, $theme, $addedClass)
    {
        /**
         * Add prism
         */
        if (!PluginUtility::htmlSnippetAlreadyAdded($renderer->info, Prism::SNIPPET_NAME)) {
            $renderer->doc .= Prism::getSnippet($theme);
        }

        /**
         * Add HTML
         */
        $language = $attributes["type"];
        if ($language == "dw") {
            $language = "html";
        }
        StringUtility::addEolIfNotPresent($renderer->doc);
        PluginUtility::addClass2Attributes('language-' . $language, $attributes);
        if (array_key_exists("line-numbers", $attributes)) {
            unset($attributes["line-numbers"]);
            PluginUtility::addClass2Attributes('line-numbers', $attributes);
        }

        /**
         * Pre element
         */
        $preAttributes = [];
        PluginUtility::addClass2Attributes($addedClass, $preAttributes);
        if (array_key_exists("prompt", $attributes)) {
            PluginUtility::addClass2Attributes("command-line",$preAttributes);
            $preAttributes["data-prompt"]=$attributes["prompt"];
            unset($attributes["prompt"]);
        }
        $htmlCode = '<pre ' . PluginUtility::array2HTMLAttributes($preAttributes) . '>' . DOKU_LF;

        /**
         * Code element
         */
        $htmlCode .= '<code ';
        PluginUtility::addClass2Attributes($addedClass, $attributes);
        $htmlCode .= PluginUtility::array2HTMLAttributes($attributes) . ' >' . DOKU_LF;
        $renderer->doc .= $htmlCode;

    }

    public static function htmlExit(\Doku_Renderer_xhtml $renderer)
    {
        $renderer->doc .= '</code>' . DOKU_LF . '</pre>' . DOKU_LF;
    }


}
