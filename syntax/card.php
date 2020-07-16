<?php
/**
 * DokuWiki Syntax Plugin Combostrap.
 *
 */

use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 * ie:
 *    syntax_plugin_PluginName_ComponentName
 */
class syntax_plugin_combo_card extends DokuWiki_Syntax_Plugin
{


    const TAG = 'card';



    /**
     * @var bool
     */
    static public $cardBodyOpen = false;

    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getType()
    {
        return 'container';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * One of array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * 'baseonly' will run only in the base mode
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

        foreach (self::getTags() as $tag) {
            $pattern = PluginUtility::getContainerTagPattern($tag);
            $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
        }


    }

    public function postConnect()
    {

        foreach (self::getTags() as $tag) {
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
     * @param int $pos
     * @param Doku_Handler $handler
     * @return array|bool
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER:

                $attributes = PluginUtility::getTagAttributes($match);
                return array($state, $attributes);

            case DOKU_LEXER_UNMATCHED :

                return array($state, $match);


            case DOKU_LEXER_EXIT :

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
            list($state, $payload) = $data;
            switch ($state) {

                case DOKU_LEXER_ENTER :

                    $attributes = $payload;
                    PluginUtility::addClass2Attributes("card",$attributes);
                    $renderer->doc .= '<div '.PluginUtility::array2HTMLAttributes($attributes).'>' . DOKU_LF;
                    break;

                case DOKU_LEXER_UNMATCHED :

                    if (self::$cardBodyOpen==false){
                        $renderer->doc .= '<div class="card-body">'. DOKU_LF;
                        self::$cardBodyOpen=true;
                    }
                    $renderer->doc .= PluginUtility::escape($payload);

                    break;


                case DOKU_LEXER_EXIT :

                    $renderer->doc .= '</div>' . DOKU_LF;
                    $renderer->doc .= "</div>" . DOKU_LF;

                    // Reset
                    self::$cardBodyOpen = false;
                    break;
            }
            return true;
        }
        return false;
    }



    public
    static function getTags()
    {
        $elements[] = self::TAG;
        $elements[] = 'teaser';
        return $elements;
    }


}
