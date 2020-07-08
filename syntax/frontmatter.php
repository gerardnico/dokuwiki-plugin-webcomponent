<?php
/**
 * Front Matter implementation to add metadata
 *
 *
 * that enhance the metadata dokuwiki system
 * https://www.dokuwiki.org/metadata
 * that use the Dublin Core Standard
 * http://dublincore.org/
 * by adding the front matter markup specification
 * https://gerardnico.com/markup/front-matter
 *
 * Inspiration
 * https://github.com/dokufreaks/plugin-meta/blob/master/syntax.php
 * https://www.dokuwiki.org/plugin:semantic
 *
 * See also structured plugin
 * https://www.dokuwiki.org/plugin:data
 * https://www.dokuwiki.org/plugin:struct
 *
 */

use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) {
    die();
}

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_combo_frontmatter extends DokuWiki_Syntax_Plugin
{

    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     *
     * Return an array of one or more of the mode types {@link $PARSER_MODES} in Parser.php
     *
     * baseonly - run only in the base
     */
    function getType()
    {
        return 'baseonly';
    }

    /**
     * @see Doku_Parser_Mode::getSort()
     * Higher number than the teaser-columns
     * because the mode with the lowest sort number will win out
     */
    function getSort()
    {
        return 99;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @param string $mode
     * @see Doku_Parser_Mode::connectTo()
     */
    function connectTo($mode)
    {
        if ($mode == "base") {
            // only from the top
            $this->Lexer->addSpecialPattern('---json.*?---', $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());
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

        if ($state == DOKU_LEXER_SPECIAL) {

            global $ID;

            // strip
            //   from start `---json` + eol = 8
            //   from end   `---` + eol = 4
            $match = substr($match, 8, -4);

            // Otherwise you get an object ie $arrayFormat-> syntax
            $arrayFormat = true;
            $json = json_decode($match, $arrayFormat);

            // Decodage problem
            if ($json == null) {
                return array($json);
            }

            // Trim it
            $jsonKey = array_map('trim', array_keys($json));
            $jsonValues = array_map('trim', $json);
            $json = array_combine($jsonKey, $jsonValues);


            $notModifiableMeta = [
                "date",
                "user",
                "last_change",
                "creator",
                "contributor"
            ];
            foreach ($json as $key => $value) {

                // Not modifiable metadata
                if (in_array($key, $notModifiableMeta)) {
                    PluginUtility::msg("Front Matter: The metadata ($key) is a protected metadata and cannot be modified", PluginUtility::LVL_MSG_WARNING);
                    continue;
                }

                // Description is special
                if ($key == "description") {
                    p_set_metadata($ID, array("description" => array("abstract" => $value)));
                    continue;
                }

                // Set the value persistently
                p_set_metadata($ID, array($key => $value));

            }

            return array($json);
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
        // TODO: https://developers.google.com/search/docs/data-types/breadcrumb#breadcrumb-list
        // News article: https://developers.google.com/search/docs/data-types/article
        // News article: https://developers.google.com/search/docs/data-types/paywalled-content
        // What is ?: https://developers.google.com/search/docs/data-types/qapage
        // How to ?: https://developers.google.com/search/docs/data-types/how-to

        if ($format == 'metadata') {


            global $ID;

            /** @var Doku_Renderer_metadata $renderer */

            list($json) = $data;
            if ($json == null) {
                PluginUtility::msg("Front Matter: The json object for the page ($ID) is not valid", PluginUtility::LVL_MSG_ERROR);
            }


        }
        return true;
    }


}

