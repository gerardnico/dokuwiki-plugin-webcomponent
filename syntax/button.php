<?php
/**
 * DokuWiki Syntax Plugin Web Component.
 *
 */
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}


require_once(DOKU_PLUGIN . 'syntax.php');
require_once(DOKU_INC . 'inc/parserutils.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 * ie:
 *    syntax_plugin_PluginName_ComponentName
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!! The component name must be the name of the php file !!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
class syntax_plugin_webcomponent_button extends DokuWiki_Syntax_Plugin
{


    const INTERNAL_LINK_PATTERN = "\[\[.*?\]\](?!\])";


    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getType()
    {
        return 'formatting';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
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
     * the mode with the lowest sort number will win out
     * the lowest in the tree must have the lowest sort number
     * No idea why it must be low but inside a teaser, it will work
     * https://www.dokuwiki.org/devel:parser#order_of_adding_modes_important
     */
    function getSort()
    {
        return 10;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @see Doku_Parser_Mode::connectTo()
     * @param string $mode
     */
    function connectTo($mode)
    {

        foreach (self::getTags() as $tag) {

            $pattern = webcomponent::getContainerTagPattern($tag);
            $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

        }

    }

    public function postConnect()
    {

        foreach (self::getTags() as $tag) {
            $this->Lexer->addExitPattern('</' . $tag . '>', 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());
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
     * @throws Exception
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER:

                // Suppress the tag name
                $tag = self::getTagInString($match);
                $match = utf8_substr($match, strlen($tag) + 1, -1);
                $parameters = webcomponent::parseMatch($match);
                return array($state, $parameters);

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

        switch ($format) {

            case 'xhtml': {

                /** @var Doku_Renderer_xhtml $renderer */

                list($state, $parameters) = $data;
                switch ($state) {

                    case DOKU_LEXER_ENTER :
                        $class = "";
                        if (array_key_exists("class", $parameters)) {
                            $class = $parameters["class"];
                        }
                        if ($class != "") {
                            $class = " " . $class;
                        }
                        $renderer->doc .= '<button type="button" class="btn btn-primary' . $class . '">';
                        break;

                    case DOKU_LEXER_UNMATCHED :

                        $renderer->doc .= $renderer->_xmlEntities($parameters);
                        break;


                    case DOKU_LEXER_EXIT :
                        $renderer->doc .= '</button>';
                        break;
                }
                return true;
            }

        }
        return false;
    }


    public static function getTag()
    {
        return webcomponent::getTagName(get_called_class());
    }

    public static function getTags()
    {
        $elements[] = self::getTag();
        $elements[] = 'btn';
        return $elements;
    }

    /**
     * @param $string
     * @return mixed
     * @throws Exception
     */
    public static function getTagInString($string)
    {
        foreach (self::getTags() as $tag){
            if (strpos($string, $tag) !== false){
                return $tag;
            }
        }
        throw new Exception('No tag was found in the string ('.$string.')');

    }


}
