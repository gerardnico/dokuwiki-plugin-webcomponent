<?php



// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class syntax_plugin_webcomponent_note
 * Implementation of a note
 * called an alert in <a href="https://getbootstrap.com/docs/4.0/components/alerts/">bootstrap</a>
 */
class syntax_plugin_webcomponent_note extends DokuWiki_Syntax_Plugin {

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

    function getTag(){
        return $this->getPluginComponent();
    }

    function connectTo($mode) {

        $pattern = webcomponent::getContainerTagPattern($this->getTag());
        $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

    }

    function postConnect() {

        $this->Lexer->addExitPattern('</' . $this->getTag() . '>', 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

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
            list($state, $attributes) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $renderer->doc .= '<div class="alert alert-primary" role="alert">';
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $renderer->_xmlEntities($attributes);
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '</div>';
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }


}

