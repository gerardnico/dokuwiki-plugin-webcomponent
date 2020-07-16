<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();


class syntax_plugin_combo_blockquotecite extends DokuWiki_Syntax_Plugin
{


    function getType()
    {
        return 'formatting';
    }

    function getPType()
    {
        return 'block';
    }

    function getAllowedTypes()
    {
        return array('substition', 'formatting', 'disabled');
    }

    function getSort()
    {
        /**
         * Should be less than the cite syntax plugin
         **/
        return 200;
    }


    function connectTo($mode)
    {
        if ($mode == PluginUtility::getModeForComponent(syntax_plugin_combo_blockquote::TAG)) {
            $pattern = PluginUtility::getContainerTagPattern(syntax_plugin_combo_cite::TAG);
            $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }
    }


    function postConnect()
    {

        $this->Lexer->addExitPattern('</' . syntax_plugin_combo_cite::TAG . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));

    }

    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER :
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
                case DOKU_LEXER_ENTER :

                    /**
                     * Hack in case there is no unmatched text in a blockquote
                     */
                    if (syntax_plugin_combo_blockquote::$cardBodyOpen == false && syntax_plugin_combo_blockquote::$type=="card") {
                        $renderer->doc .= '<div class="card-body">' . DOKU_LF;
                        $renderer->doc .= '<blockquote class="blockquote mb-0">' . DOKU_LF;
                        syntax_plugin_combo_blockquote::$cardBodyOpen = true;
                        syntax_plugin_combo_blockquote::$blockQuoteOpen = true;
                    }
                    $renderer->doc .= "<footer class=\"blockquote-footer\"><cite";
                    if (sizeof($payload)>0){
                        $inlineAttributes = PluginUtility::array2HTMLAttributes($payload);
                        $renderer->doc .= $inlineAttributes.'>';
                    } else {
                        $renderer->doc .= '>';
                    }
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= PluginUtility::escape($payload);
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '</cite></footer>' . DOKU_LF;
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }



}

