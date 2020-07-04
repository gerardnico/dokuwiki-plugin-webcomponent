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

require_once(__DIR__ . '/../class/PLuginUtility.php');

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


    // The > in the pattern below is to be able to handle pluggin
    // that uses a pattern such as {{changes>.}} from the change plugin
    // https://github.com/cosmocode/changes/blob/master/syntax.php
    const IMAGE_PATTERN = "\{\{(?:[^>\}]|(?:\}[^\}]))+\}\}";


    // The elements of a teaser
    // because they are assembled at the end
    private $startElement;
    private $text;
    private $header;
    private $image;
    const body_html = DOKU_TAB . '<div class="card-body">';


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
     * @see Doku_Parser_Mode::connectTo()
     * @param string $mode
     */
    function connectTo($mode)
    {

        foreach (self::getTags() as $tag) {

            $pattern = PluginUtility::getContainerTagPattern($tag);
            $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());
        }


    }

    public function postConnect()
    {

        foreach (self::getTags() as $tag) {
            $this->Lexer->addExitPattern('</' . $tag . '>', 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());
        }

        // Image
        $this->Lexer->addPattern(self::IMAGE_PATTERN, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

    }

    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     * @param string $match
     * @param int $state
     * @param int $pos
     * @param Doku_Handler $handler
     * @return array|bool
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER:

                // Suppress the component name
                // Suppress the <>
                $match = substr($match, 1, -1);
                // Suppress the tag name
                foreach (self::getTags() as $tag) {
                    $match = str_replace($tag, "", $match);
                }
                $parameters = PluginUtility::parseMatch($match);
                return array($state, $parameters);

            case DOKU_LEXER_UNMATCHED :

                return array($state, $match);

            case DOKU_LEXER_MATCHED :

                $parameters = array();

                if (preg_match('/' . self::IMAGE_PATTERN . '/msSi', $match . DOKU_LF)) {
                    // We have an image, we parse it (Doku_Handler_Parse_Media in handler.php)
                    $parameters['image'] = Doku_Handler_Parse_Media($match);
                }

                return array($state, $parameters);

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
            list($state, $parameters) = $data;
            switch ($state) {

                case DOKU_LEXER_ENTER :

                    $renderer->doc .= '<div class="card"';
                    foreach ($parameters as $key => $value) {
                        $renderer->doc .= ' ' . $key . '="' . $value . '"';
                    }
                    $renderer->doc .= '>'. DOKU_LF ;
                    $renderer->doc .= self::body_html.DOKU_LF;
                    break;

                case DOKU_LEXER_UNMATCHED :

                    $renderer->doc .= $renderer->_xmlEntities($parameters);

                    break;

                case DOKU_LEXER_MATCHED:


                    if (array_key_exists('image', $parameters)) {

                        $renderer->doc = substr ($renderer->doc, 0, strlen($renderer->doc)-strlen(self::body_html)-strlen(DOKU_LF));
                        $src = $parameters['image']['src'];
                        $width = $parameters['image']['width'];
                        $height = $parameters['image']['height'];
                        $title = $parameters['image']['title'];
                        //Snippet taken from $renderer->doc .= $renderer->internalmedia($src, $linking = 'nolink');
                        $renderer->doc .= DOKU_TAB . '<img class="card-img-top" src="' . ml($src, array('w' => $width, 'h' => $height, 'cache' => true)) . '" alt="' . $title . '" width="' . $width . '">' .DOKU_LF;
                        $renderer->doc .= self::body_html;
                    }
                    break;

                case DOKU_LEXER_EXIT :

                    $renderer->doc .= DOKU_TAB . '</div>' . DOKU_LF;
                    $renderer->doc .= "</div>" . DOKU_LF;

                    break;
            }
            return true;
        }
        return false;
    }


    public static function getTag()
    {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', get_called_class(), 4);
        return (isset($c) ? $c : '');
    }

    public
    static function getTags()
    {
        $elements[] = PluginUtility::getTagName(get_called_class());
        $elements[] = 'teaser';
        return $elements;
    }


}
