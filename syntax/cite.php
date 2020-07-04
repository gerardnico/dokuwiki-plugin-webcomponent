<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
use ComboStrap\PluginUtility;

if(!defined('DOKU_INC')) die();


class syntax_plugin_combo_cite extends DokuWiki_Syntax_Plugin {

    CONST TAG = "cite";

    function getType() {
        return 'formatting';
    }

    function getPType() {
        return 'block';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     */
    function getAllowedTypes() {
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    function getSort() {
        return 201;
    }



    function connectTo($mode) {

        $pattern = PluginUtility::getContainerTagPattern(self::TAG);
        $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));

    }

    function postConnect() {

        $this->Lexer->addExitPattern('</' . self::TAG . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));

    }

    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     * @param string $match
     * @param int $state
     * @param int $pos
     * @param Doku_Handler $handler
     * @return array|bool
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {

        switch ($state) {

            case DOKU_LEXER_ENTER :

                $attributes = PluginUtility::getAttributes($match);
                return array($state, $attributes);

            case DOKU_LEXER_UNMATCHED :
                return array ($state, $match);

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

                    $renderer->doc .= "<cite";
                    if (sizeof($payload)>0) {
                        $inlineAttributes = PluginUtility::array2HTMLAttributes($payload);
                        $renderer->doc .= " $inlineAttributes";
                    }
                    $renderer->doc .= ">";
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $renderer->_xmlEntities($payload);
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '</cite>';
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }



}

