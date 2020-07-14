<?php
/**
 * DokuWiki Syntax Plugin Combostrap.
 * Implementatiojn of https://getbootstrap.com/docs/4.1/content/typography/#blockquotes
 *
 */

use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) {
    die();
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
class syntax_plugin_combo_blockquote extends DokuWiki_Syntax_Plugin
{

    const TAG = "blockquote";
    const IMAGE_PATTERN = "\{\{(?:[^\}]|(?:\}[^\}]))+\}\}";


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
        return array('container', 'baseonly', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
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

        // Receive the image
        $this->Lexer->addPattern(self::IMAGE_PATTERN, PluginUtility::getModeForComponent($this->getPluginComponent()));

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
                // Suppress the component name

                $tagAttributes = PluginUtility::getTagAttributes($match);
                return array($state, $tagAttributes);

            case DOKU_LEXER_UNMATCHED :
                return array($state, $match);

            case DOKU_LEXER_MATCHED :

                $tagAttributes = array ();
                if (preg_match('/' . self::IMAGE_PATTERN . '/msSi', $match, $matches)) {
                    // We have an image, we parse it (Doku_Handler_Parse_Media in handler.php)
                    $tagAttributes['image'] = Doku_Handler_Parse_Media($match);
                }
                return array($state, $tagAttributes);


            case DOKU_LEXER_EXIT :
                // Important to get an exit in the render phase
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
                    if (array_key_exists("class", $payload)) {
                        $payload["class"] .= "card";
                    } else {
                        $payload["class"] ="card";
                    }
                    $inlineAttributes = PluginUtility::array2HTMLAttributes($payload);
                    $renderer->doc .= "<div {$inlineAttributes}>" . DOKU_LF
                        . DOKU_TAB . '<div class="card-body">' . DOKU_LF
                        . DOKU_TAB . DOKU_TAB . '<' . self::TAG . ' class="' . self::TAG . ' m-0"';
                    // m-0 on the blockquote element is to correct a bottom margin from bootstrap of 1em that we don't want
                    $renderer->doc .= '>' . DOKU_LF;
                    break;

                case DOKU_LEXER_UNMATCHED :

                    $renderer->doc .= DOKU_TAB . DOKU_TAB . hsc($payload) . DOKU_LF;
                    break;

                case DOKU_LEXER_MATCHED:

                    if (array_key_exists('cite', $payload)) {
                        $content = $payload['cite']['content'];
                        $renderer->doc .= DOKU_TAB . DOKU_TAB . '<footer class="blockquote-footer text-right"><cite>';
                        $renderer->doc .= hsc($content); //webcomponent::render($content);
                        $renderer->doc .= "</cite></footer>" . DOKU_LF;
                    }

                    if (array_key_exists('image', $payload)) {
                        $src = $payload['image']['src'];
                        $width = $payload['image']['width'];
                        $height = $payload['image']['height'];
                        $title = $payload['image']['title'];
                        //Snippet taken from $renderer->doc .= $renderer->internalmedia($src, $linking = 'nolink');
                        $renderer->doc .= '<img class="media my-3" src="' . ml($src, array('w' => $width, 'h' => $height, 'cache' => true)) . '" alt="' . hsc($title) . '" width="' . hsc($width) . '">';
                    }
                    break;

                case DOKU_LEXER_EXIT :

                    // Close the blockquote
                    $renderer->doc .= DOKU_TAB . DOKU_TAB . '</' . $this->getPluginComponent() . '>' . DOKU_LF
                        . DOKU_TAB . '</div>' . DOKU_LF;

                    // Close the card
                    $renderer->doc .= '</div>';

                    // Reinit
                    $this->content = "";
                    $this->img = "";
                    $this->cite = "";
                    break;
            }
            return true;
        }
        return true;
    }




}
