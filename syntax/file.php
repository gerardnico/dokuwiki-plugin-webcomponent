<?php

// implementation of
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/code

// must be run within Dokuwiki
use ComboStrap\Prism;
use ComboStrap\StringUtility;
use ComboStrap\Tag;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/StringUtility.php');
require_once(__DIR__ . '/../class/Prism.php');

if (!defined('DOKU_INC')) die();


class syntax_plugin_combo_file extends DokuWiki_Syntax_Plugin
{


    /**
     * Enable or disable the file component
     */
    const CONF_FILE_ENABLE = 'fileEnable';

    /**
     * The tag of the ui component
     */
    const FILE_TAG = "file";


    function getType()
    {
        /**
         * You can't write in a code block
         */
        return 'protected';
    }

    /**
     * How DokuWiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs
     *  * 'block'  - Open paragraphs need to be closed before plugin output - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     */
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
        return array();
    }

    function getSort()
    {
        /**
         * Should be less than the code syntax plugin
         * which is 200
         **/
        return 199;
    }


    function connectTo($mode)
    {

        if ($this->getConf(self::CONF_FILE_ENABLE)) {
            $pattern = PluginUtility::getContainerTagPattern(self::FILE_TAG);
            $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }

    }


    function postConnect()
    {
        if ($this->getConf(self::CONF_FILE_ENABLE)) {
            $this->Lexer->addExitPattern('</' . self::FILE_TAG . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));
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
                $tagAttributes = PluginUtility::getTagAttributes($match);
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::ATTRIBUTES => $tagAttributes
                );

            case DOKU_LEXER_UNMATCHED :
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => $match
                );

            case DOKU_LEXER_EXIT :
                return array(PluginUtility::STATE => $state);


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
            $state = $data [PluginUtility::STATE];
            switch ($state) {
                case DOKU_LEXER_ENTER :

                    /**
                     * The added class to the output
                     */
                    $class = 'combo_' . self::FILE_TAG;

                    /**
                     * Styling extra for file
                     * Disable for now
                     */
                    if (false && !PluginUtility::htmlSnippetAlreadyAdded($renderer->info, self::FILE_TAG)) {
                        $renderer->doc .= <<<EOF
<style>
code[class*=$class]{
   color: #333;
}
pre[class*=$class]{
   background-color: #e9ecef;
}
</style>
EOF;
                    }
                    $attributes = $data[PluginUtility::ATTRIBUTES];
                    $theme = $this->getConf(Prism::CONF_PRISM_THEME);
                    Prism::htmlEnter($renderer, $attributes, $theme, $class);
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= PluginUtility::escape($data[PluginUtility::PAYLOAD]) . DOKU_LF;
                    break;

                case DOKU_LEXER_EXIT :
                    Prism::htmlExit($renderer);
                    break;

            }
            return true;
        }

        // unsupported $mode
        return false;

    }


}
