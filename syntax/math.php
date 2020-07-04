<?php

use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();
require_once(__DIR__ . '/../class/PLuginUtility.php');


/**
 * Class syntax_plugin_combo_math
 */
class syntax_plugin_combo_math extends DokuWiki_Syntax_Plugin
{

    const MATH_EXPRESSION = 'math_expression';




    /**
     * Syntax Type
     *
     * Protected in order to say that we don't want it to be modified
     * The mathjax javascript will take care of the rendering
     *
     * @return string
     */
    public function getType()
    {
        return 'protected';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     */
    public function getAllowedTypes()
    {
        return array();
    }

    /**
     * How Dokuwiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs
     *  * 'block'  - Open paragraphs need to be closed before plugin output - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     */
    function getPType()
    {
        return 'normal';
    }

    /**
     *
     * @return int
     */
    public function getSort()
    {
        return 195;
    }

    /**
     *
     * @param string $mode
     */
    public function connectTo($mode)
    {

        // Add the entry patterns
        foreach (self::getElements() as $element) {

            $pattern = PluginUtility::getContainerTagPattern($element);
            $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

        }



    }

    public function postConnect()
    {

        foreach (self::getElements() as $element) {
            $this->Lexer->addExitPattern('</' . $element . '>', 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());
        }

    }

    /**
     *
     * @param   string $match The text matched by the patterns
     * @param   int $state The lexer state for the match
     * @param   int $pos The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {

        // A metadata to tell if the page has a math expression or not
        // Use in the action plugin to add or not the library
        // Reset a math tag was not found
//        global $ID;
//        p_set_metadata(
//            $ID,
//            array(syntax_plugin_combo_math::MATH_EXPRESSION => false),
//            $render = false,
//            $persistent = true
//        );

        // The element is also needed by mathjax
        // pass the whole thing
        return array(
            0 => $match
        );
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

        $content = $data[0];
        switch ($format) {
            case 'xhtml':
            case 'odt':
                /** @var Doku_Renderer_xhtml $renderer */
                $renderer->doc .= $renderer->_xmlEntities($content);
                break;

            case 'latexport':
                // Pass math expressions to latexport renderer
                $renderer->mathjax_content($content);
                break;

            case 'metadata':
                // Adding a meta to say that there is a math expression
                global $ID;
                /** @var Doku_Renderer_metadata $renderer */
                // No persistence, only for the run
                if (isset($renderer->persistent[self::MATH_EXPRESSION])) {
                    unset($renderer->persistent[self::MATH_EXPRESSION]);
                }
                $renderer->meta[self::MATH_EXPRESSION] = true;

                break;

            default:
                $renderer->doc .= $renderer->$data;
                break;

        }

        return true;

    }

    static public function getElements()
    {
        return PluginUtility::getTags(get_called_class());
    }

    public static function getComponentName()
    {
        return PluginUtility::getTagName(get_called_class());
    }

}

