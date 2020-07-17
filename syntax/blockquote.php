<?php
/**
 * DokuWiki Syntax Plugin Combostrap.
 * Implementatiojn of https://getbootstrap.com/docs/4.1/content/typography/#blockquotes
 *
 */

use ComboStrap\ComponentNode;
use ComboStrap\HeadingUtility;
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) {
    die();
}

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/HeadingUtility.php');
require_once(__DIR__ . '/../class/ComponentNode.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 * ie:
 *    syntax_plugin_PluginName_ComponentName
 */
class syntax_plugin_combo_blockquote extends DokuWiki_Syntax_Plugin
{

    const TAG = "blockquote";


    /**
     * @var mixed|string
     */
    static public $type = "card";



    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     */
    function getType()
    {
        return 'container';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('container', 'baseonly', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     *
     * Return an array of one or more of the mode types {@link $PARSER_MODES} in Parser.php
     */
    public function getAllowedTypes()
    {
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }


    /**
     * How Dokuwiki will add P element
     *
     * * 'normal' - The plugin can be used inside paragraphs
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
     * @see Doku_Parser_Mode::getSort()
     * Higher number than the teaser-columns
     * because the mode with the lowest sort number will win out
     */
    function getSort()
    {
        return 200;
    }


    /**
     * Create a pattern that will called this plugin
     *
     * @param string $mode
     * @see Doku_Parser_Mode::connectTo()
     */
    function connectTo($mode)
    {

        $pattern = PluginUtility::getContainerTagPattern(self::TAG);
        $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));

    }

    public function postConnect()
    {

        $this->Lexer->addExitPattern('</' . self::TAG . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));


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

            case DOKU_LEXER_ENTER:
                // Suppress the component name
                $defaultAttributes = array("type"=>"card");
                $tagAttributes = PluginUtility::getTagAttributes($match);
                $tagAttributes = PluginUtility::mergeAttributes($tagAttributes,$defaultAttributes);
                return array(
                    PluginUtility::STATE=> $state,
                    PluginUtility::ATTRIBUTES => $tagAttributes);

            case DOKU_LEXER_UNMATCHED :
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => $match,
                    PluginUtility::TREE => $handler->calls);


            case DOKU_LEXER_EXIT :
                // Important to get an exit in the render phase
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::TREE => $handler->calls
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
            $state = $data[PluginUtility::STATE];
            switch ($state) {

                case DOKU_LEXER_ENTER :

                    $attributes = $data[PluginUtility::ATTRIBUTES];
                    if (array_key_exists("type", $attributes)) {
                        self::$type = $attributes["type"];
                        unset($attributes["type"]);
                        if (self::$type=="typo") {
                            $class = "blockquote";
                        } else {
                            $class = "card";
                        }
                    } else {
                        $class = "card";
                    }

                    PluginUtility::addClass2Attributes($class, $attributes);

                    $inlineAttributes = PluginUtility::array2HTMLAttributes($attributes);
                    if (self::$type == "typo") {
                        $renderer->doc .= "<blockquote {$inlineAttributes}>" . DOKU_LF;
                    } else {
                        $renderer->doc .= "<div {$inlineAttributes}>" . DOKU_LF;
                    }
                    break;

                case DOKU_LEXER_UNMATCHED:

                    $node = new ComponentNode("cdata",array(),$data[PluginUtility::TREE]);
                    if ($node->getParent()->getType()=="card"){
                        $previousTags = ["heading"];
                        if (!in_array($node->getFirstSibling()->getName(),$previousTags)) {
                            $renderer->doc .= "<div class=\"card-body\">" . DOKU_LF;
                        }
                        $renderer->doc .= "<blockquote class=\"blockquote mb-0\">" . DOKU_LF;
                    }

                    $payload = $data[PluginUtility::PAYLOAD];
                    $renderer->doc .= PluginUtility::render($payload).DOKU_LF;
                    break;


                case DOKU_LEXER_EXIT :

                    $node = new ComponentNode(self::TAG,array(),$data[PluginUtility::TREE]);
                    if ($node->getOpeningTag()->getType()=="card"){

                        $renderer->doc .= "</blockquote>" . DOKU_LF;
                        $renderer->doc .= "</div>" . DOKU_LF;
                        $renderer->doc .= "</div>" . DOKU_LF;

                    } else {

                        $renderer->doc .= "</blockquote>" . DOKU_LF;

                    }


                    break;
            }
            return true;
        }
        return true;
    }


}
