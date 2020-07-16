<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite

// must be run within Dokuwiki
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();


class syntax_plugin_combo_cite extends DokuWiki_Syntax_Plugin
{
    const TAG = "cite";


    /**
     * @var mixed
     */
    private $closingTag;

    function getType()
    {
        return 'formatting';
    }

    function getPType()
    {
        return 'block';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('baseonly','container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     *
     * Return an array of one or more of the mode types {@link $PARSER_MODES} in Parser.php
     */
    function getAllowedTypes()
    {
        return array('baseonly','container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
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
            $pattern = PluginUtility::getContainerTagPattern(self::TAG);
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
                $parentTag = PluginUtility::getParentTag($handler);
                $tagAttributes = PluginUtility::getTagAttributes($match);
                return array($state, $tagAttributes,$parentTag);

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
            list($state, $payload, $parent) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER :

                    if ($parent["tag"] == "blockquote") {
                        $parentAttributes = $parent["attributes"];
                        /**
                         * Hack in case there is no unmatched text in a blockquote
                         */
                        $type = "card";
                        if (isset($parentAttributes["type"])){
                            $type = $parentAttributes["type"];
                        }
                        if ($type == "card") {
                            $renderer->doc .= '<div class="card-body">' . DOKU_LF;
                            $this->closingTag = "</div>".DOKU_LF;
                            $renderer->doc .= '<blockquote class="blockquote mb-0">' . DOKU_LF;
                            $this->closingTag = "</blockquote>".DOKU_LF.$this->closingTag;
                        }
                        $renderer->doc .= "<footer class=\"blockquote-footer\"><cite";
                        if (sizeof($payload) > 0) {
                            $inlineAttributes = PluginUtility::array2HTMLAttributes($payload);
                            $renderer->doc .= $inlineAttributes . '>';
                        } else {
                            $renderer->doc .= '>';
                        }
                        $this->closingTag = "</footer>".DOKU_LF.$this->closingTag;
                    } else {
                        $renderer->doc .= "<cite";
                        if (sizeof($payload)>0) {
                            $inlineAttributes = PluginUtility::array2HTMLAttributes($payload);
                            $renderer->doc .= " $inlineAttributes";
                        }
                        $renderer->doc .= ">";
                    }
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= PluginUtility::escape($payload);
                    break;

                case DOKU_LEXER_EXIT :

                    $renderer->doc .= '</cite>'.$this->closingTag;
                    $this->closingTag = "";

                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }



}

