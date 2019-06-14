<?php
/**
 * DokuWiki Plugin Math Action
 *
 */

if (!defined('DOKU_INC')) die();
require_once(__DIR__ . '/../webcomponent.php');
require_once(__DIR__ . '/../syntax/math.php');

/**
 * Math
 */
class action_plugin_webcomponent_math extends DokuWiki_Action_Plugin
{


    /**
     * Registers our handler
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller)
    {

        $controller->register_hook(
            'TPL_DOCUMENT_CLOSING',
            'BEFORE',
            $this,
            'handle_closing',
            array()
        );


    }

    /**
     *      *
     *
     * @param Doku_Event $event
     * @param            $param
     */
    public function handle_closing(Doku_Event &$event, $param)
    {
        // config=TeX-MML-AM_CHTML
        // where:
        // Tex = TeX and LaTeX
        // MML = MathML - http://www.w3.org/TR/MathML3
        // AM = AsciiMath - http://asciimath.org/
        // CHTML = output using HTML with CSS

        // Check metadata to see if there is a math syntax
        // https://www.dokuwiki.org/devel:metadata#metadata_index
        global $ID;
        $isMathExpression = p_get_metadata($ID, syntax_plugin_webcomponent_math::MATH_EXPRESSION);
        if ($isMathExpression) {

            $math_div_id = webcomponent::PLUGIN_NAME . '_' . syntax_plugin_webcomponent_math::getComponentName();

            ptln('<div id="' . $math_div_id . '"">');
            ptln(DOKU_TAB . '<script type="text/x-mathjax-config">
                MathJax.Hub.Config({
                    showProcessingMessages: true,
                    extensions: ["tex2jax.js"],
                    jax: ["input/TeX", "output/HTML-CSS"],
                    tex2jax: {
                        inlineMath: [ ["<math>","</math>"]],
                        displayMath: [ ["<MATH>","</MATH>"] ],
                        processEscapes: true,
                        scale:120
                    },
                    "HTML-CSS": { fonts: ["TeX"] }
                });
            </script>');
            ptln(DOKU_TAB . '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.5/latest.js" async></script>');
            ptln('</div>');
        }

    }


}

