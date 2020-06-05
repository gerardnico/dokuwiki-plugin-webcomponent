<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();


class syntax_plugin_webcomponent_brand extends DokuWiki_Syntax_Plugin {

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
     * array('container', 'baseonly', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     *
     */
    function getAllowedTypes() {
        return array('container', 'baseonly', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    function getSort() {
        return 201;
    }



    function connectTo($mode) {

        $pattern = webcomponent::getLookAheadPattern(self::getTag());
        $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

    }

    function postConnect() {

        $this->Lexer->addExitPattern('</' . self::getTag() . '>', 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

    }

    function handle($match, $state, $pos, Doku_Handler $handler) {

        switch ($state) {

            case DOKU_LEXER_ENTER :
                $match = utf8_substr($match, strlen($this->getPluginComponent()) + 1, -1);
                $parameters = webcomponent::parseMatch($match);
                return array($state, $parameters);

            case DOKU_LEXER_UNMATCHED :
                return array ($state, $match);

            case DOKU_LEXER_EXIT :

                // Important otherwise we don't get an exit in the render
                return array($state, '');


        }
        return array();

    }

    function render($mode, Doku_Renderer $renderer, $data) {

        if ($mode == 'xhtml') {

            /** @var Doku_Renderer_xhtml $renderer */
            list($state, $parameters) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $renderer->doc .= '<a href="'.wl().'" accesskey="h"';

                    $title = ' title="';
                    if (array_key_exists("title", $parameters)) {
                        $title .= $parameters["title"];
                    } else {
                        global $conf;
                        $title .= $conf['title'];
                    }
                    $title .='"';
                    $renderer->doc .= $title;

                    $class = ' class="navbar-brand';
                    if (array_key_exists("class", $parameters)) {
                        $class .= ' '.$parameters["class"];
                    }
                    $class .='"';
                    $renderer->doc .= $class.'>';
                    break;

                case DOKU_LEXER_UNMATCHED :
                    // What about:
                    //   * the title of the website ? $conf['title']
                    //   * the logo ? $logo = tpl_getMediaFile(array(':wiki:logo.png', ':logo.png', 'images/logo.png'), false, $logoSize);
                    $renderer->doc .= $renderer->_xmlEntities($parameters);
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '</a>';
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }

    public static function getTag()
    {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', get_called_class(), 4);
        return (isset($c) ? $c : '');
    }

}

