<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
use ComboStrap\HeaderUtility;
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();

require_once(__DIR__ . '/../class/HeaderUtility.php');


class syntax_plugin_combo_blockquoteheader extends DokuWiki_Syntax_Plugin
{


    function getType()
    {
        return 'formatting';
    }

    /**
     * How Dokuwiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs (inline)
     *  * 'block'  - Open paragraphs need to be closed before plugin output - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     */
    function getPType()
    {
        /**
         * We just don't want the p
         */
        return 'normal';
    }

    function getAllowedTypes()
    {
        return array('substition', 'formatting', 'disabled');
    }

    function getSort()
    {
        return 201;
    }


    function connectTo($mode)
    {
        if ($mode == PluginUtility::getModeForComponent(syntax_plugin_combo_blockquote::TAG)) {
            $this->Lexer->addSpecialPattern(HeaderUtility::HEADER_PATTERN, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }
    }



    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_SPECIAL :
                $tagAttributes = HeaderUtility::parse($match);
                return array($state, $tagAttributes);

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
            list($state, $payload) = $data;
            switch ($state) {

                case DOKU_LEXER_SPECIAL :

                    $title = $payload['header']['title'];
                    $renderer->doc .= '<div class="card-header">' . DOKU_LF;
                    $renderer->doc .= PluginUtility::escape($title) . DOKU_LF;
                    $renderer->doc .= "</div>";

                    break;

            }
            return true;
        }

        // unsupported $mode
        return false;
    }



}

