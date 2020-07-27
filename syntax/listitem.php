<?php


use ComboStrap\AdsUtility;
use ComboStrap\PluginUtility;


/**
 * Class syntax_plugin_combo_list
 * Implementation of a list
 */
class syntax_plugin_combo_listitem extends DokuWiki_Syntax_Plugin
{

    const TAGS = array("list-item", "li");


    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getType()
    {
        return 'formatting';
    }

    /**
     * How Dokuwiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs (inline or inside)
     *  * 'block'  - Open paragraphs need to be closed before plugin output (box) - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     * @see https://www.dokuwiki.org/devel:syntax_plugins#ptype
     */
    function getPType()
    {
        return 'normal';
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
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    /**
     * @see Doku_Parser_Mode::getSort()
     * the mode with the lowest sort number will win out
     * Higher than {@link syntax_plugin_combo_list}
     */
    function getSort()
    {
        return 201;
    }


    function connectTo($mode)
    {

        /**
         * This selection helps also because
         * the pattern for the li tag could also catch a list tag
         */
        if ($mode == PluginUtility::getModeForComponent(syntax_plugin_combo_list::TAG)) {
            foreach (self::TAGS as $tag) {
                $pattern = PluginUtility::getContainerTagPattern($tag);
                $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
            }
        }


    }

    public function postConnect()
    {
        foreach (self::TAGS as $tag) {
            $this->Lexer->addExitPattern('</' . $tag . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));
        }

    }


    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @param string $match
     * @param int $state
     * @param int $pos - byte position in the original source file
     * @param Doku_Handler $handler
     * @return array|bool
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER :
                $attributes = PluginUtility::getTagAttributes($match);

                PluginUtility::addStyleProperty('position', 'relative',$attributes); // Why ?
                //PluginUtility::addStyleProperty('height', '36px',$attributes); // Why ?
                PluginUtility::addStyleProperty('display', 'flex',$attributes);
                PluginUtility::addStyleProperty('align-items', 'center',$attributes);
                PluginUtility::addStyleProperty('justify-content', 'flex-start',$attributes);
                PluginUtility::addStyleProperty('padding', '8px 16px',$attributes); // Padding at the left and right
                PluginUtility::addStyleProperty('overflow', 'hidden',$attributes);

                $html = '<li';
                if (sizeof($attributes)) {
                    $html .= ' '.PluginUtility::array2HTMLAttributes($attributes);
                }
                $html .= '>';
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::ATTRIBUTES => $attributes,
                    PluginUtility::PAYLOAD => $html);

            case DOKU_LEXER_UNMATCHED :

                $attributes = array();
                PluginUtility::addStyleProperty('letter-spacing','.009375em',$attributes);
                PluginUtility::addStyleProperty('font-weight','400',$attributes);
                PluginUtility::addStyleProperty('font-size','1rem',$attributes);
                $inlineAttributes = PluginUtility::array2HTMLAttributes($attributes);
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => "<span $inlineAttributes>".PluginUtility::escape($match).'</span>');

            case DOKU_LEXER_EXIT :

                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => '</li>');


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
            $state = $data[PluginUtility::STATE];
            switch ($state) {
                case DOKU_LEXER_ENTER :
                case DOKU_LEXER_EXIT :
                    $renderer->doc .= $data[PluginUtility::PAYLOAD] . DOKU_LF;
                    break;
                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $data[PluginUtility::PAYLOAD];
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }


}

