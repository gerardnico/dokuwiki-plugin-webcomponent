<?php


// must be run within Dokuwiki
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();


class syntax_plugin_combo_noteheader extends DokuWiki_Syntax_Plugin
{

    // Header pattern that we expect in a card (teaser) ie  ==== Hello =====
    const HEADER_PATTERN = '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)';

    // The topbar strike again
    const STYLE_ATTRIBUTE = 'style="color:inherit!important;margin-left:unset!important;margin-top:unset!important;padding-top:unset!important"';

    function getType()
    {
        return 'formatting';
    }

    function getPType()
    {
        return 'stack';
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
        // Only inside a note
        if ($mode == PluginUtility::getModeForComponent(syntax_plugin_combo_note::TAG)) {
            $this->Lexer->addSpecialPattern(self::HEADER_PATTERN, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }
    }


    function postConnect()
    {


    }

    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            // As this is a container, this cannot happens but yeah, now, you know
            case DOKU_LEXER_SPECIAL :

                $title = trim($match);
                $level = 7 - strspn($title, '=');
                if ($level < 1) $level = 1;
                $title = trim($title, '=');
                $title = trim($title);
                $parameters['header']['title'] = $title;
                $parameters['header']['level'] = $level;
                return array($state, $parameters);

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

                case DOKU_LEXER_SPECIAL :
                    $title = $parameters['header']['title'];
                    $level = $parameters['header']['level'];
                    $renderer->doc .= DOKU_TAB. DOKU_TAB. '<h' . $level . ' class="alert-heading" ' . self::STYLE_ATTRIBUTE . '>';
                    $renderer->doc .= $renderer->_xmlEntities($title);
                    $renderer->doc .= "</h$level>";

                    break;

            }
        }
        // unsupported $mode
        return false;
    }



}

