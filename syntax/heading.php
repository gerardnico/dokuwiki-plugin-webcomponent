<?php
/**
 * DokuWiki Syntax Plugin Web Component.
 *
 */
if (!defined('DOKU_INC')) {
    die();
}

require_once(__DIR__ . '/../webcomponent.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 * ie:
 *    syntax_plugin_PluginName_ComponentName
 *
 * An attempt to add markdown header
 *
 */
class syntax_plugin_webcomponent_heading extends DokuWiki_Syntax_Plugin
{


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
     * All
     */
    public function getAllowedTypes()
    {
        // array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
        return array();
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
     *
     * the mode with the lowest sort number will win out
     * the container (parent) must then have a lower number than the child
     */
    function getSort()
    {
        // same as parser.php#Doku_Parser_Mode_header
        return 50;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @see Doku_Parser_Mode::connectTo() to see the implemented function interface
     * @see Doku_Parser_Mode_header::connectTo() to see where the code is borrowed for.
     *
     * @param string $mode
     */
    function connectTo($mode)
    {

        $headerCharacter = self::getHeadingCharacter();
        $pattern = '^' . $headerCharacter . '[^'.DOKU_LF.']+$';
        $this->Lexer->addSpecialPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

    }

    public function postConnect()
    {

        // None

    }

    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @see DokuWiki_Syntax_Plugin::handle()
     * @see Doku_Handler::header() to see the original Dokuwiki code
     *
     * @param string $match
     * @param int $state
     * @param int $pos
     * @param Doku_Handler $handler
     * @return array|bool
     *
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_SPECIAL :
                // Must return $text, $level, $pos
                // get level and title
                $title = trim($match);
                $level = strspn($title, self::getHeadingCharacter());
                if ($level < 1) $level = 1;
                $title = trim($title, self::getHeadingCharacter());
                $title = trim($title);

                /* seems to manage the renderer call */
                if ($handler->status['section']) $handler->_addCall('section_close', array(), $pos);
                $handler->_addCall('header', array($title, $level, $pos), $pos);
                $handler->_addCall('section_open', array($level), $pos);
                $handler->status['section'] = true;

                return array($state, array($title, $level, $pos));

        }
        return array();

    }

    /**
     * Call the underlying dokuwiki renderer
     *
     * @see DokuWiki_Syntax_Plugin::render() for the interface method
     * @see Doku_Renderer_xhtml::header() for the original method
     *
     * @param string $mode
     * @param Doku_Renderer $renderer
     * @param array $data - what the function handle() return'ed
     * @return bool
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {

        /** @var Doku_Renderer_xhtml $renderer */
//        list($state, $parameters) = $data;
//        switch ($state) {
//
//            case DOKU_LEXER_SPECIAL :
//                list($title, $level, $pos) = $parameters;
//                $renderer->header($title, $level, $pos);
//                break;
//
//        }
//        return true;


    }


    public static function getHeadingCharacter()
    {
        return '#';
    }

}
