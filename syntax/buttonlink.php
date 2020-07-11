<?php


require_once(__DIR__ . "/../class/PluginUtility.php");
require_once(__DIR__ . "/../class/LinkUtility.php");
require_once(__DIR__ . "/../class/HtmlUtility.php");

use ComboStrap\HtmlUtility;
use ComboStrap\LinkUtility;
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();

/**
 * Class syntax_plugin_combo_buttonlink
 * A link pattern to take over the link in the button
 * because the a element got a color
 * and can't therefore inherit the value of the button
 */
class syntax_plugin_combo_buttonlink extends DokuWiki_Syntax_Plugin
{



    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     */
    function getType()
    {
        return 'substition';
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
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('container', 'baseonly', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     */
    function getAllowedTypes()
    {
        return array('substition', 'formatting', 'disabled');
    }

    /**
     * @see Doku_Parser_Mode::getSort()
     * The mode with the lowest sort number will win out
     */
    function getSort()
    {
        return 100;
    }


    function connectTo($mode)
    {
        // Only inside a button
        if ($mode == PluginUtility::getModeForComponent(syntax_plugin_combo_button::getTag())) {
            $this->Lexer->addSpecialPattern(LinkUtility::LINK_PATTERN, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }
    }


    /**
     * The handler for an internal link
     * based on `internallink` in {@link Doku_Handler}
     * The handler call the good renderer in {@link Doku_Renderer_xhtml} with
     * the parameters (ie for instance internallink)
     * @param string $match
     * @param int $state
     * @param int $pos
     * @param Doku_Handler $handler
     * @return array|bool
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        /**
         * Because we use the specialPattern, there is only one state ie DOKU_LEXER_SPECIAL
         */
        return LinkUtility::getAttributes($match);


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
        // The data
        switch ($format) {
            case 'xhtml':

                /** @var Doku_Renderer_xhtml $renderer */

                $htmlLink = LinkUtility::renderHTML($renderer, $data);
                $htmlLink = LinkUtility::inheritColorFromParent($htmlLink);



                $renderer->doc .= $htmlLink;

                return true;
                break;


            case 'metadata':

                /**
                 * Keep track of the backlinks ie meta['relation']['references']
                 * @var Doku_Renderer_metadata $renderer
                 */
                LinkUtility::handleMetadata($renderer, $data);

                return true;
                break;
        }
        // unsupported $mode
        return false;
    }


}

