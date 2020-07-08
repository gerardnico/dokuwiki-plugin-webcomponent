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
class syntax_plugin_combo_dropdown extends DokuWiki_Syntax_Plugin
{

    const INTERNAL_LINK_PATTERN = "\[\[.*?\]\](?!\])";
    private $linkCounter = 0;
    private $dropdownCounter = 0;

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
        return array('formatting');
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


        $pattern = PluginUtility::getContainerTagPattern(self::getTag());
        $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

        // Link
        $this->Lexer->addPattern(self::INTERNAL_LINK_PATTERN, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());


    }

    public function postConnect()
    {


        $this->Lexer->addExitPattern('</' . self::getTag() . '>', 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

        // Link
        $this->Lexer->addPattern(self::INTERNAL_LINK_PATTERN, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

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

                // Suppress the tag name
                $match = utf8_substr($match, strlen(self::getTag()) + 1, -1);
                $parameters = PluginUtility::parse2HTMLAttributes($match);
                return array($state, $parameters);

            case DOKU_LEXER_UNMATCHED :

                // Normally we don't get any here
                return array($state, $match);

            case DOKU_LEXER_MATCHED :

                $parameters = array();

                if (preg_match('/' . self::INTERNAL_LINK_PATTERN . '/msSi', $match . DOKU_LF)) {
                    // We have a internal link, we parse it (code form the function internallink in handler.php)
                    //
                    // Strip the opening and closing markup
                    $link = preg_replace(array('/^\[\[/', '/\]\]$/u'), '', $match);

                    // Split title from URL
                    $link = explode('|', $link, 2);
                    if (!isset($link[1])) {
                        $link[1] = null;
                    }
                    $link[0] = trim($link[0]);
                    // we expect only a local link
                    $parameters['locallink'][$this->linkCounter]['pageid'] = $link[0];
                    $parameters['locallink'][$this->linkCounter]['content'] = $link[1];
                    $this->linkCounter++;

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
                    $this->dropdownCounter++;
                    $dropDownId = "dropDown".$this->dropdownCounter;
                    $name = 'Name';
                    if (array_key_exists("name", $parameters)) {
                        $name = $parameters["name"];
                    }
                    $renderer->doc .= '<li class="nav-item dropdown">'
                        . DOKU_TAB . '<a id="'.$dropDownId.'" href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="Title">'.$name.'</a>'
                        . DOKU_TAB . '<div class="dropdown-menu" aria-labelledby="'.$dropDownId.'">';
                    break;

                case DOKU_LEXER_UNMATCHED :

                    $renderer->doc .= $renderer->_xmlEntities($parameters);
                    break;

                case DOKU_LEXER_MATCHED:


                    if (array_key_exists('locallink', $parameters)) {

                        foreach ($parameters['locallink'] as $link) {
                            $pageId = $link['pageid'];
                            $content = $link['content'];

                            $renderer->doc .= '<a href="' . wl($pageId) . '" class="dropdown-item"';
                            if (!page_exists($pageId)){
                                $renderer->doc .= ' style="color: #d30;border-bottom: 1px dashed;"';
                            }
                            $renderer->doc .= '>' . $renderer->_xmlEntities($content) . '</a>';
                        }

                    }
                    break;

                case DOKU_LEXER_EXIT :

                    $renderer->doc .= '</div></li>';

                    // Counter on NULL
                    $this->linkCounter = 0;
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


}
