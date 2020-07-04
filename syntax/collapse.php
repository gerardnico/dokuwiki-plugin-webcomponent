<?php
/**
 * DokuWiki Syntax Plugin Combostrap.
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
class syntax_plugin_combo_collapse extends DokuWiki_Syntax_Plugin
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
     *
     * the mode with the lowest sort number will win out
     * the container (parent) must then have a lower number than the child
     */
    function getSort()
    {
        return 100;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @see Doku_Parser_Mode::connectTo()
     * @param string $mode
     */
    function connectTo($mode)
    {

        $pattern = PluginUtility::getContainerTagPattern(self::getElementName());
        $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

    }

    public function postConnect()
    {

        $this->Lexer->addExitPattern('</' . self::getElementName() . '>', 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

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
                $match = utf8_substr($match, strlen(self::getElementName()) + 1, -1);
                $parameters = PluginUtility::parseMatch($match);
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

                    // The button is the hamburger menu that will be shown
                    $idElementToCollapse ='navbarcollapse';
                    $renderer->doc .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#'.$idElementToCollapse.'" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation"';
                    if (array_key_exists("order", $parameters)) {
                        $renderer->doc .= ' style="order:' . $parameters["order"];
                    }
                    $renderer->doc .= '"><span class="navbar-toggler-icon"></span></button>' . DOKU_LF
                        . '<div id="'.$idElementToCollapse.'" class="collapse navbar-collapse">';
                    // All element below will collapse
                    break;

                case DOKU_LEXER_EXIT :

                    $renderer->doc .= '</div>' . DOKU_LF;
                    break;
            }
            return true;
        }
        return false;
    }


    public static function getElementName()
    {
        return PluginUtility::getTagName(get_called_class());
    }


}
