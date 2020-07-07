<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

require_once(__DIR__ . "/../class/PluginUtility.php");
require_once(__DIR__ . "/../class/XmlUtility.php");

use ComboStrap\PluginUtility;
use ComboStrap\XmlUtility;

if (!defined('DOKU_INC')) die();

/**
 * Class syntax_plugin_combo_buttonlink
 * A link pattern to take over the link in the button
 * because the a element got a color
 * and can't therefore inherit the value of the button
 */
class syntax_plugin_combo_buttonlink extends DokuWiki_Syntax_Plugin
{

    //
    /**
     * Link pattern
     * Found in {@link \dokuwiki\Parsing\ParserMode\Internallink}
     */
    const LINK_PATTERN = "\[\[.*?\]\](?!\])";

    /**
     * The extra style for the link
     */
    const STYLE_VALUE = ";background-color:inherit;border-color:inherit;color:inherit";


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
            $this->Lexer->addSpecialPattern(self::LINK_PATTERN, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
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

        /**
         * Code adapted from See {@link $handler->internallink($match,$state,$pos)}
         */
        // Strip the opening and closing markup
        $link = preg_replace(array('/^\[\[/', '/\]\]$/u'), '', $match);

        // Split title from URL
        $link = explode('|', $link, 2);
        if (!isset($link[1])) {
            $link[1] = null;
        } else if (preg_match('/^\{\{[^\}]+\}\}$/', $link[1])) {
            // If the title is an image, convert it to an array containing the image details
            $link[1] = Doku_Handler_Parse_Media($link[1]);
        }
        $link[0] = trim($link[0]);

        return $link;


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
        $link = $data;
        $id = $link[0];
        $title = $link[1];
        switch ($format) {
            case 'xhtml':

                /** @var Doku_Renderer_xhtml $renderer */

                // Always return the string
                $returnOnly = true;

                // The HTML created by DokuWiki
                $html = "";
                if (link_isinterwiki($id)) {
                    // Interwiki
                    $interWiki = explode('>', $id, 2);
                    $wikiName = strtolower($interWiki[0]);
                    $wikiUri = $interWiki[1];
                    $html = $renderer->interwikilink($id, $title, $wikiName, $wikiUri, $returnOnly);
                } elseif (preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u', $id)) {
                    $html = $renderer->windowssharelink($id, $title);
                } elseif (preg_match('#^([a-z0-9\-\.+]+?)://#i', $id)) {
                    $html = $renderer->externallink($id, $title, $returnOnly);
                } elseif (preg_match('<' . PREG_PATTERN_VALID_EMAIL . '>', $id)) {
                    // E-Mail (pattern above is defined in inc/mail.php)
                    $html = $renderer->emaillink($id, $title, $returnOnly);
                } elseif (preg_match('!^#.+!', $id)) {
                    $html = $renderer->locallink(substr($id, 1), $title, $returnOnly);
                } else {
                    $queryUrl = null;
                    $html = $renderer->internallink($id, $title, $queryUrl, $returnOnly);
                }

                try {
                    /** @noinspection PhpComposerExtensionStubsInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    $linkDom = new SimpleXMLElement ($html);
                } catch (Exception $e) {
                    PluginUtility::msg("The HTML link ($html) is not a valid HTML element. The error returned is $e", PluginUtility::LVL_MSG_ERROR);
                    return false;
                }
                XmlUtility::addAttributeValue("style", self::STYLE_VALUE, $linkDom);

                $renderer->doc .= XmlUtility::asHtml($linkDom);

                return true;
                break;


            case
            'metadata':
                /**
                 * Keep track of the backlinks
                 */
                /** @var Doku_Renderer_metadata $renderer */
                $renderer->internallink($id);
                return true;
                break;
        }
        // unsupported $mode
        return false;
    }


}

