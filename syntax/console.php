<?php
/**
 * Plugin Webcode: Show webcode (Css, HTML) in a iframe
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Nicolas GERARD
 */

// must be run within Dokuwiki
use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();
require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * Format
 *
 * syntax_plugin_PluginName_PluginComponent
 */
class syntax_plugin_combo_console extends DokuWiki_Syntax_Plugin
{


    /*
     * What is the type of this plugin ?
     * This a plugin categorization
     * This is only important for other plugin
     * See @getAllowedTypes
     */
    public function getType()
    {
        return 'formatting';
    }


    // Sort order in which the plugin are applied
    public function getSort()
    {
        return 168;
    }

    /**
     *
     * @return array
     * The plugin type that are allowed inside
     * this node (ie nested)
     * Otherwise the node that are in the matched content are not processed
     */
    function getAllowedTypes() {
        return array();

    }

    /**
     * Handle the node
     * @return string
     * See
     * https://www.dokuwiki.org/devel:syntax_plugins#ptype
     */
    function getPType(){ return 'block';}

    // This where the addEntryPattern must bed defined
    public function connectTo($mode)
    {
        // This define the DOKU_LEXER_ENTER state
        $pattern = PluginUtility::getContainerTagPattern(self::getElementName());
        $this->Lexer->addEntryPattern($pattern, $mode, 'plugin_' . PluginUtility::PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

    }

    public function postConnect()
    {
        // We define the DOKU_LEXER_EXIT state
        $this->Lexer->addExitPattern('</' . self::getElementName() . '>', 'plugin_' . PluginUtility::PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());
    }


    /**
     * Handle the match
     * You get the match for each pattern in the $match variable
     * $state says if it's an entry, exit or match pattern
     *
     * This is an instruction block and is cached apart from the rendering output
     * There is two caches levels
     * This cache may be suppressed with the url parameters ?purge=true
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {

            case DOKU_LEXER_ENTER :

                break;

            case DOKU_LEXER_UNMATCHED:
                return array($state,$match);
                break;

            case DOKU_LEXER_EXIT:

                break;

        }

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
        // The $data variable comes from the handle() function
        //
        // $mode = 'xhtml' means that we output html
        // There is other mode such as metadata, odt
        if ($format == 'xhtml') {

            $state = $data[0];
            // No Unmatched because it's handled in the handle function
            switch ($state) {

                case DOKU_LEXER_UNMATCHED:
                    $text=$data[1];
                    /**
                     * @var Doku_Renderer_xhtml
                     * See code in Doku_Renderer_xhtml
                     * with lang, filename, highlight,... parameters
                     */
                    $renderer->code($text);
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
