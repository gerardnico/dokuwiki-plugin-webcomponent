<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
use ComboStrap\HeaderUtility;
use ComboStrap\HeadingUtility;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/HeaderUtility.php');

if (!defined('DOKU_INC')) die();


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
        // Only inside a card
        $modes = [
            PluginUtility::getModeForComponent(syntax_plugin_combo_blockquote::TAG)
            ];
        if (in_array($mode, $modes)) {
            $this->Lexer->addEntryPattern(PluginUtility::getContainerTagPattern(HeaderUtility::HEADER), $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</' . HeaderUtility::HEADER . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));
    }

    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER:
                $tagAttributes = PluginUtility::getTagAttributes($match);
                return array($state, $tagAttributes);

            case DOKU_LEXER_UNMATCHED :
                return array($state, $match);

            case DOKU_LEXER_EXIT :
                // Important otherwise we don't get an exit in the render
                return array($state, '');


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

                case DOKU_LEXER_ENTER:
                    PluginUtility::addClass2Attributes("card-header",$payload);
                    $inlineAttributes = PluginUtility::array2HTMLAttributes($payload);
                    $renderer->doc .= "<div {$inlineAttributes}>" . DOKU_LF;
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= PluginUtility::escape($payload).DOKU_LF;
                    break;

                case DOKU_LEXER_EXIT:
                    $renderer->doc .= "</div>";
                    break;


            }
        }
        // unsupported $mode
        return false;
    }



}

