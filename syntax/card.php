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

require_once(__DIR__ . '/../webcomponent.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 * ie:
 *    syntax_plugin_PluginName_ComponentName
 */
class syntax_plugin_webcomponent_card extends DokuWiki_Syntax_Plugin
{

    // Pattern that we expect in a card (teaser)
    const HEADER_PATTERN = '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)';
    const IMAGE_PATTERN = "\{\{(?:[^\}]|(?:\}[^\}]))+\}\}";


    // The elements of a teaser
    // because they are assembled at the end
    private $startElement;
    private $text;
    private $header;
    private $image;


    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getType()
    {
        return 'protected';
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

            $pattern = webcomponent::getLookAheadPattern($tag);
            $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());
        }



    }

    public function postConnect()
    {

        foreach (self::getTags() as $tag) {
            $this->Lexer->addExitPattern('</' . $tag . '>', 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());
        }

        // Header
        $this->Lexer->addPattern(self::HEADER_PATTERN, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

        // Image
        $this->Lexer->addPattern(self::IMAGE_PATTERN, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

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
                $match = utf8_substr($match, 1, -1);
                // Suppress the tag name
                foreach (self::getTags() as $tag) {
                    $match = str_replace( $tag, "",$match);
                }
                $parameters = webcomponent::parseMatch($match);
                return array($state, $parameters);

            case DOKU_LEXER_UNMATCHED :

                return array($state, $match);

            case DOKU_LEXER_MATCHED :

                $parameters = array();
                if (preg_match('/' . self::HEADER_PATTERN . '/msSi', $match . DOKU_LF)) {
                    // We have a header
                    $title = trim($match);
                    $level = 7 - strspn($title, '=');
                    if ($level < 1) $level = 1;
                    $title = trim($title, '=');
                    $title = trim($title);
                    $parameters['header']['title'] = $title;
                    $parameters['header']['level'] = $level;
                }

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
     * @see DokuWiki_Syntax_Plugin::render()
     *
     * @param string $mode
     * @param Doku_Renderer $renderer
     * @param array $data - what the function handle() return'ed
     * @return bool
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {

        if ($mode == 'xhtml') {

            /** @var Doku_Renderer_xhtml $renderer */
            list($state, $parameters) = $data;
            switch ($state) {

                case DOKU_LEXER_ENTER :
                    $this->startElement .= '<div class="card"';
                    foreach ($parameters as $key => $value) {
                        $this->startElement .= ' ' . $key . '="' . $value . '"';
                    }
                    $this->startElement .= '>';
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $instructions = p_get_instructions($parameters);
                    $lastPBlockPosition = sizeof($instructions) - 2;
                    if ($instructions[1][0] == 'p_open') {
                        unset($instructions[1]);
                    }
                    if ($instructions[$lastPBlockPosition][0] == 'p_close') {
                        unset($instructions[$lastPBlockPosition]);
                    }
                    $this->text .= p_render('xhtml', $instructions, $info);
                    break;

                case DOKU_LEXER_MATCHED:

                    if (array_key_exists('header', $parameters)) {
                        $title = $parameters['header']['title'];
                        $level = $parameters['header']['level'];
                        $this->header .= '<h' . $level . ' class="card-title">';
                        $this->header .= $renderer->_xmlEntities($title);
                        $this->header .= "</h$level>";
                    }

                    if (array_key_exists('image', $parameters)) {

                        $this->image = $parameters['image'];

                    }
                    break;

                case DOKU_LEXER_EXIT :

                    $renderer->doc .= $this->startElement . DOKU_LF;

                    if ($this->header == "" and $this->text == "" and $this->image != ""){
                        // An image card without any content
                        $src = $this->image['src'];
                        $width = $this->image['width'];
                        $height = $this->image['height'];
                        $title = $this->image['title'];
                        //Snippet taken from $renderer->doc .= $renderer->internalmedia($src, $linking = 'nolink');
                        $renderer->doc .= '<img class="card-img" src="' . ml($src, array('w' => $width, 'h' => $height, 'cache' => true)) . '" alt="' . $title . '" width="' . $width . '">';
                    } else {
                        // A real teaser
                        if ($this->image != "") {
                            $src = $this->image['src'];
                            $width = $this->image['width'];
                            $height = $this->image['height'];
                            $title = $this->image['title'];
                            //Snippet taken from $renderer->doc .= $renderer->internalmedia($src, $linking = 'nolink');
                            $renderer->doc .= DOKU_TAB . '<img class="card-img-top" src="' . ml($src, array('w' => $width, 'h' => $height, 'cache' => true)) . '" alt="' . $title . '" width="' . $width . '">' .DOKU_LF;
                        }
                        $renderer->doc .= DOKU_TAB . '<div class="card-body">' . DOKU_LF;
                        if ($this->header != "") {
                            $renderer->doc .= DOKU_TAB . DOKU_TAB . $this->header . DOKU_LF;
                        }
                        if ($this->text != "") {
                            $renderer->doc .= DOKU_TAB . DOKU_TAB . '<p class="card-text">' . $this->text . '</p>' . DOKU_LF;
                        }
                        $renderer->doc .= DOKU_TAB . '</div>' . DOKU_LF;
                    }

                    $renderer->doc .= "</div>" . DOKU_LF;

                    $this->startElement = "";
                    $this->image = "";
                    $this->header = "";
                    $this->text = "";
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
        $elements[] = webcomponent::getTagName(get_called_class());
        $elements[] = 'teaser';
        return $elements;
    }


}
