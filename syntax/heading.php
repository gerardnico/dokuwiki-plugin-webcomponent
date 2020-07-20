<?php


use ComboStrap\StringUtility;
use ComboStrap\Tag;
use ComboStrap\HeaderUtility;
use ComboStrap\HeadingUtility;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/HeadingUtility.php');

if (!defined('DOKU_INC')) die();


class syntax_plugin_combo_heading extends DokuWiki_Syntax_Plugin
{


    // Could be also title
    const TAG = "heading";

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
            PluginUtility::getModeForComponent(syntax_plugin_combo_blockquote::TAG),
            PluginUtility::getModeForComponent(syntax_plugin_combo_card::TAG),
            PluginUtility::getModeForComponent(syntax_plugin_combo_note::TAG)
        ];
        if (in_array($mode, $modes)) {
            $this->Lexer->addSpecialPattern(HeadingUtility::HEADING_PATTERN, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }
    }


    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {


            case DOKU_LEXER_SPECIAL :

                $attributes = HeadingUtility::parse($match);
                $tag = new Tag(self::TAG, $attributes, $state, $handler->calls);
                $parentTag = $tag->getParent()->getName();
                if (in_array($parentTag,[syntax_plugin_combo_blockquote::TAG, syntax_plugin_combo_card::TAG])){
                    $linkClass = "card-title";
                }
                $title = $attributes[HeadingUtility::TITLE];
                $level = $attributes[HeadingUtility::LEVEL];
                $html = '<h' . $level;
                if (isset($linkClass)) {
                    $html .= ' class="' . $linkClass . '"';
                }
                $html .= ' '.HeadingUtility::COMPONENT_TITLE_STYLE . '>';
                $html .= PluginUtility::escape($title);
                $html .= "</h$level>".DOKU_LF;
                if ($parentTag == syntax_plugin_combo_blockquote::TAG){
                    $html .= syntax_plugin_combo_blockquote::BLOCKQUOTE_OPEN_TAG;
                }

                return array(
                    PluginUtility::STATE=> $state,
                    PluginUtility::ATTRIBUTES=> $attributes,
                    PluginUtility::PAYLOAD=> $html,
                    PluginUtility::PARENT_TAG => $parentTag
                );

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
            $state= $data[PluginUtility::STATE];
            switch ($state) {

                case DOKU_LEXER_SPECIAL :
                    if($data[PluginUtility::PARENT_TAG]== syntax_plugin_combo_blockquote::TAG){
                        StringUtility::rtrim($renderer->doc,syntax_plugin_combo_blockquote::BLOCKQUOTE_OPEN_TAG);
                    }
                    $renderer->doc .= $data[PluginUtility::PAYLOAD];
                    break;

            }
        }
        // unsupported $mode
        return false;
    }


}

