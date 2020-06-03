<?php
/**
 * DokuWiki Syntax Plugin Related.
 *
 */
if (!defined('DOKU_INC')) {
    die();
}


require_once(DOKU_INC . 'inc/parserutils.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 */
class syntax_plugin_webcomponent_related extends DokuWiki_Syntax_Plugin
{


    // Conf property key
    const MAX_LINKS_CONF = 'maxLinks';
    // For when you come from another plugin (such as backlinks) and that you don't want to change the pattern on each page
    const EXTRA_PATTERN_CONF = 'extra_pattern';

    // This is a fake page ID that is added
    // to the related page array when the number of backlinks is bigger than the max
    // Poisoning object strategy
    const MORE_PAGE_ID = 'related_more';

    // The array key of an array of related page
    const RELATED_PAGE_ID_PROP = 'id';
    const RELATED_BACKLINKS_COUNT_PROP = 'backlinks';


    public static function getElementId()
    {
        return webcomponent::PLUGIN_NAME . "_" . self::getElementName();
    }


    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getType()
    {
        return 'substition';
    }

    /**
     * @see DokuWiki_Syntax_Plugin::getPType()
     */
    function getPType()
    {
        return 'block';
    }

    /**
     * @see Doku_Parser_Mode::getSort()
     */
    function getSort()
    {
        return 100;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @param string $mode
     * @see Doku_Parser_Mode::connectTo()
     */
    function connectTo($mode)
    {
        // The basic
        $this->Lexer->addSpecialPattern('<' . self::getElementName() . '[^>]*>', $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());

        // To replace backlinks, you may add it in the configuration
        $extraPattern = $this->getConf(self::EXTRA_PATTERN_CONF);
        if ($extraPattern != "") {
            $this->Lexer->addSpecialPattern($extraPattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());
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

        switch ($state) {

            // As there is only one call to connect to in order to a add a pattern,
            // there is only one state entering the function
            // but I leave it for better understanding of the process flow
            case DOKU_LEXER_SPECIAL :

                // Parse the parameters
                $match = utf8_substr($match, strlen(self::getElementName()), -1);
                $parameters=array();

                // /i not case sensitive
                $attributePattern = "\\s*(\w+)\\s*=\\s*[\'\"]{1}([^\`\"]*)[\'\"]{1}\\s*";
                $result = preg_match_all('/' . $attributePattern . '/i', $match, $matches);
                if ($result != 0) {
                    foreach ($matches[1] as $key => $parameterKey) {
                        $parameter = strtolower($parameterKey);
                        $value = $matches[2][$key];
                        $parameters[$parameter] = $value;
                    }
                }
                // Cache the values
                return array($state, $parameters);

        }

        // Cache the values
        return array($state);
    }

    /**
     * Render the output
     * @param string $mode
     * @param Doku_Renderer $renderer
     * @param array $data
     * @return bool
     * @see DokuWiki_Syntax_Plugin::render()
     *
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {
        global $lang;
        global $INFO;
        global $ID;

        $id = $ID;
        // If it's a sidebar, get the original id.
        if (isset($INFO)) {
            $id = $INFO['id'];
        }

        if ($mode == 'xhtml') {

            $relatedPages = $this->related($id);

            $renderer->doc .= '<div id="' . self::getElementId() . '" class="' . self::getElementName() . '-container">' . DOKU_LF;

            if (empty($relatedPages)) {

                // Dokuwiki debug
                dbglog("No Backlinks", "Related plugins: all backlinks for page: $id");
                $renderer->doc .= "<strong>Plugin " . webcomponent::PLUGIN_NAME . " - Component " . self::getElementName() . ": " . $lang['nothingfound'] . "</strong>" . DOKU_LF;

            } else {

                // Dokuwiki debug
                dbglog($relatedPages, self::getElementName() . " plugins: all backlinks for page: $id");

                $renderer->doc .= '<ul>' . DOKU_LF;

                foreach ($relatedPages as $backlink) {
                    $backlinkId = $backlink[self::RELATED_PAGE_ID_PROP];
                    $name = p_get_metadata($backlinkId, 'title');
                    if (empty($name)) {
                        $name = $backlinkId;
                    }
                    $renderer->doc .= '<li>';
                    if ($backlinkId != self::MORE_PAGE_ID) {
                        $renderer->doc .= html_wikilink(':' . $backlinkId, $name);
                    } else {
                        $renderer->doc .=
                            tpl_link(
                                wl($id) . '?do=backlink',
                                "More ...",
                                'class="" rel="nofollow" title="More..."',
                                $return = true
                            );
                    }
                    $renderer->doc .= '</li>' . DOKU_LF;
                }

                $renderer->doc .= '</ul>' . DOKU_LF;

            }

            $renderer->doc .= '</div>' . DOKU_LF;

            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @param $max
     * @return array
     */
    public function related($id, $max = NULL): array
    {
        if ($max == NULL) {
            $max = $this->getConf(self::MAX_LINKS_CONF);
        }
        // Call the dokuwiki backlinks function
        @require_once(DOKU_INC . 'inc/fulltext.php');
        // Backlinks called the indexer, for more info
        // See: https://www.dokuwiki.org/devel:metadata#metadata_index
        $backlinks = ft_backlinks($id, $ignore_perms = false);

        // To minimize the pressure on the index
        // as we asks then the backlinks of the backlinks on the next step
        if (sizeof($backlinks) > 50) {
            $backlinks = array_slice($backlinks, 0, 50);
        }

        $related = array();
        foreach ($backlinks as $backlink) {
            $page = array();
            $page[self::RELATED_PAGE_ID_PROP] = $backlink;
            $page[self::RELATED_BACKLINKS_COUNT_PROP] = sizeof(ft_backlinks($backlink, $ignore_perms = false));
            $related[] = $page;
        }

        usort($related, function ($a, $b) {
            return $b[self::RELATED_BACKLINKS_COUNT_PROP] - $a[self::RELATED_BACKLINKS_COUNT_PROP];
        });

        if (sizeof($related) > $max) {
            $related = array_slice($related, 0, $max);
            $page = array();
            $page[self::RELATED_PAGE_ID_PROP] = self::MORE_PAGE_ID;
            $related[] = $page;
        }

        return $related;

    }

    public static function getElementName()
    {
        return webcomponent::getTagName(get_called_class());
    }


}
