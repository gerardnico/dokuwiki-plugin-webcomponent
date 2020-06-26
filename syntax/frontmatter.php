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
if (!defined('DOKU_INC')) {
    die();
}

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_webcomponent_frontmatter extends DokuWiki_Syntax_Plugin
{

    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     *
     * baseonly
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
            $this->Lexer->addSpecialPattern('---json.*?---', $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());
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
            // strip
            //   from start `---json` + eol = 8
            //   from end   `---` + eol = 4
            $match = substr($match, 8, -4);

            // Otherwise you get an object ie $arrayFormat-> syntax
            $arrayFormat = true;
            $json = json_decode($match, $arrayFormat);

            // Trim it
            $jsonKey = array_map('trim', array_keys($json));
            $jsonValues = array_map('trim', $json);
            $json = array_combine($jsonKey, $jsonValues);

            // Process
            if (array_key_exists('description', $json)) {
                global $ID;
                $description = p_get_metadata($ID, 'description');
                $description['abstract'] = $json['description'];
                p_set_metadata($ID, array('description' => $description));
            }

            // Processing should not be defined by key
            if (array_key_exists(action_plugin_webcomponent_metacanonical::CANONICAL_PROPERTY, $json)) {
                global $ID;
                p_set_metadata($ID, array(action_plugin_webcomponent_metacanonical::CANONICAL_PROPERTY => $json[action_plugin_webcomponent_metacanonical::CANONICAL_PROPERTY]));
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

            list($json) = $data;

            if ($json == null) {
                msg("Front Matter: The json object is not valid", -1, $allow = MSG_MANAGERS_ONLY);
                return false;
            }

            /** @var Doku_Renderer_metadata $renderer */

            // do some validation / conversion for date metadata
            if (isset($json['description'])) {
                $renderer->meta['description']['abstract']=$json['description'];
            }

            // Ter info
            // $renderer->_firstimage($subvalue);

        }
        return true;
    }


}

