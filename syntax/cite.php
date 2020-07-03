<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();


class syntax_plugin_webcomponent_cite extends DokuWiki_Syntax_Plugin {

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

        $tag = $this->getPluginComponent();
        $pattern = webcomponent::getContainerTagPattern($tag);
        $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

    }

    function postConnect() {

        $tag = $this->getPluginComponent();
        $this->Lexer->addExitPattern('</' . $tag . '>', 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

    }

    function handle($match, $state, $pos, Doku_Handler $handler) {

        switch ($state) {

            case DOKU_LEXER_ENTER :

                $attributes = webcomponent::getAttributes($match);
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
            list($state, $data) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER :

                    $inlineAttributes = webcomponent::array2HTMLAttributes($data);
                    $renderer->doc .= "<cite $inlineAttributes>";
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $renderer->_xmlEntities($data);
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

