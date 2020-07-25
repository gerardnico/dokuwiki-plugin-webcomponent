<?php


use ComboStrap\AdsUtility;
use ComboStrap\PageUtility;
use ComboStrap\PluginUtility;


/**
 * Class syntax_plugin_combo_ad
 * Implementation of a ad
 */
class syntax_plugin_combo_ad extends DokuWiki_Syntax_Plugin
{

    const TAG = "ad";


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

    function getSort()
    {
        return 201;
    }


    function connectTo($mode)
    {

        $pattern = PluginUtility::getEmptyTagPattern(self::TAG);
        $this->Lexer->addSpecialPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));

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

            case DOKU_LEXER_SPECIAL :
                $attributes = PluginUtility::getTagAttributes($match);
                $htmlAttributes = array();
                $html = "";
                $wrapWithDiv = false;
                if (!isset($attributes["name"])){
                    $html = "The name attribute is mandatory to render an ad";
                    $wrapWithDiv = true;
                    $htmlAttributes["color"]="red";
                } else {
                    $name = $attributes["name"];
                    $adsPageId = AdsUtility::getAdPage($name);
                    if (page_exists($adsPageId)) {
                        $html .= PageUtility::renderId2Xhtml($adsPageId);
                    } else {
                        $html .= "The ad page (".$adsPageId.") does not exist";
                        $wrapWithDiv = true;
                        $htmlAttributes["color"]="red";
                    }

                    if (sizeof($attributes) > 1) {
                        // if there is more than the name attributes, this is styling attributes, we
                        // wrap then the ads with a div
                        $wrapWithDiv = true;
                    }

                }

                /**
                 * When an error occurs or there is styling attributes,
                 * we wrap the ad with a div
                 */
                if ($wrapWithDiv) {
                    $divHtmlWrapper = "<div";
                    $htmlAttributes = PluginUtility::mergeAttributes($attributes,$htmlAttributes);
                    unset($htmlAttributes["name"]);
                    $divHtmlWrapper .= " " . PluginUtility::array2HTMLAttributes($htmlAttributes);
                    $html = $divHtmlWrapper .">". $html . '</div>';
                }

                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::ATTRIBUTES => $attributes,
                    PluginUtility::PAYLOAD => $html);


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
                case DOKU_LEXER_SPECIAL :
                    $renderer->doc .= $data[PluginUtility::PAYLOAD];
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }


}

