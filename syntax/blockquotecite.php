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
        if ($mode == "plugin_combo_blockquote") {
            $pattern = '<' . self::getTag() . '.*?>(?=.*?</' . self::getTag() . '>)';
            $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());
        }
    }


    function postConnect()
    {

        $this->Lexer->addExitPattern('</' . self::getTag() . '>', 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

    }

    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER :
                return array($state, '');

            // As this is a container, this cannot happens but yeah, now, you know
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
            list($state, $parameters) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $renderer->doc .= DOKU_TAB . DOKU_TAB . '<footer class="blockquote-footer text-right"><cite>';
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $renderer->_xmlEntities($parameters);
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

    public
    static function getTag()
    {
        return 'cite';
    }

}

