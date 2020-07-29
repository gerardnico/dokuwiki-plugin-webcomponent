<?php


use ComboStrap\AdsUtility;
use ComboStrap\FsWikiUtility;
use ComboStrap\LogUtility;
use ComboStrap\PluginUtility;
use ComboStrap\RenderUtility;
use ComboStrap\Tag;


/**
 * Class syntax_plugin_combo_ntoc
 * Implementation of a namespace toc
 */
class syntax_plugin_combo_ntoc extends DokuWiki_Syntax_Plugin
{

    const TAG = "ntoc";
    const TAGS = [self::TAG, "minimap"];
    const FILE_ITEM = "file-item";

    /**
     * Ntoc attribute
     */
    const ATTR_NAMESPACE = "ns";
    const NAMESPACE_ITEM = "ns-item";


    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getType()
    {
        return 'container';
    }

    /**
     * How Dokuwiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs (inline or inside)
     *  * 'block'  - Open paragraphs need to be closed before plugin output (box) - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     * @see https://www.dokuwiki.org/devel:syntax_plugins#ptype
     */
    function getPType()
    {
        return 'normal';
    }

    /**
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('baseonly','container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     *
     * Return an array of one or more of the mode types {@link $PARSER_MODES} in Parser.php
     */
    function getAllowedTypes()
    {
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    function getSort()
    {
        return 201;
    }


    function connectTo($mode)
    {

        $pattern = PluginUtility::getContainerTagPattern(self::TAG);
        $this->Lexer->addEntryPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));

        $this->Lexer->addPattern(PluginUtility::getLeafContainerTagPattern(self::FILE_ITEM), PluginUtility::getModeForComponent($this->getPluginComponent()));

    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</' . self::TAG . '>', PluginUtility::getModeForComponent($this->getPluginComponent()));

    }


    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @param string $match
     * @param int $state
     * @param int $pos - byte position in the original source file
     * @param Doku_Handler $handler
     * @return array|bool
     * @throws Exception
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_ENTER :
                $attributes = PluginUtility::getTagAttributes($match);
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::ATTRIBUTES => $attributes);

            case DOKU_LEXER_UNMATCHED :

                // We should not ever come here but a user does not not known that
                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => PluginUtility::escape($match));

            case DOKU_LEXER_MATCHED :

                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::ATTRIBUTES => PluginUtility::getTagAttributes($match),
                    PluginUtility::CONTENT => PluginUtility::getTagContent($match),
                    PluginUtility::TAG => PluginUtility::getTag($match)
                );

            case DOKU_LEXER_EXIT :

                $tag = new Tag(self::TAG, array(), $state, $handler->calls);

                /**
                 * Get the file item pattern
                 */
                $fileItem = $tag->getDescendant(self::FILE_ITEM);
                $fileItemContent = null;
                if ($fileItem != null) {
                    $fileItemContent = $fileItem->getData()[PluginUtility::CONTENT];
                }

                $directoryItem = $tag->getDescendant(self::NAMESPACE_ITEM);
                $dirItemContent = null;
                if ($dirItemContent != null) {
                    $dirItemContent = $directoryItem->getData()[PluginUtility::CONTENT];
                }


                /**
                 * Get the attributes
                 */
                $openingTagAttributes = $tag->getOpeningTag()->getAttributes();

                /**
                 * Get the data
                 */
                $id = FsWikiUtility::getMainPageId();
                $nameSpacePath = getNS($id);

                if (array_key_exists(self::ATTR_NAMESPACE, $openingTagAttributes)) {
                    $nameSpacePath = $openingTagAttributes[self::ATTR_NAMESPACE];
                    unset($openingTagAttributes[self::ATTR_NAMESPACE]);
                }

                if ($nameSpacePath === false) {
                    LogUtility::msg("A namespace could not be found");
                }
                $pages = FsWikiUtility::getChildren($nameSpacePath);

                /**
                 * Create the list
                 */
                $list = "<list";
                if (sizeof($openingTagAttributes) > 0) {
                    $list .= ' ' . PluginUtility::array2HTMLAttributes($openingTagAttributes);
                }
                $list .= ">";
                $pageNum = 0;
                foreach ($pages as $page) {

                    // If it's a directory
                    if ($page['type'] == "d" && !empty($dirItemContent)) {

                        $pageId = FsWikiUtility::getIndex($page['id']);
                        $pageTitle = FsWikiUtility::getTitle($pageId);
                        $list .= '<li>' . str_replace("\$title", $pageTitle, $dirItemContent) . '</li>';

                    } else {

                        if (!empty($fileItemContent)) {
                            $pageNum++;
                            $pageId = $page['id'];
                            $pageTitle = FsWikiUtility::getTitle($pageId);
                            $tpl = str_replace("\$title", $pageTitle, $fileItemContent);
                            $tpl = str_replace("\$id", $pageId, $tpl);
                            $list .= '<li>' . $tpl . '</li>';
                        }
                    }


                }
                $list .= "</list>";
                $html = RenderUtility::renderText2Xhtml($list);

                return array(
                    PluginUtility::STATE => $state,
                    PluginUtility::PAYLOAD => $html
                );


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
            $state = $data[PluginUtility::STATE];
            switch ($state) {
                case DOKU_LEXER_ENTER :
                case DOKU_LEXER_EXIT :
                    $renderer->doc .= $data[PluginUtility::PAYLOAD] . DOKU_LF;
                    break;
                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $data[PluginUtility::PAYLOAD];
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }


}

