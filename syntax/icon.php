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


require_once(DOKU_PLUGIN . 'syntax.php');
require_once(DOKU_INC . 'inc/parserutils.php');
require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 *
 * The name of the class must follow a pattern (don't change it)
 * ie:
 *    syntax_plugin_PluginName_ComponentName
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!! The component name must be the name of the php file !!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 */
class syntax_plugin_combo_icon extends DokuWiki_Syntax_Plugin
{

    const CONF_ICONS_MEDIA_NAMESPACE = "icons_namespace";
    const CONF_ICONS_MEDIA_NAMESPACE_DEFAULT = ":combostrap:icons";


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
     * @return array
     * Allow which kind of plugin inside
     *
     * No one of array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs')
     * because we manage self the content and we call self the parser
     */
    public function getAllowedTypes()
    {
        // You can put anything in a icon
        return array();
    }

    /**
     * How Dokuwiki will add P element
     *
     *  * 'normal' - The plugin can be used inside paragraphs
     *  * 'block'  - Open paragraphs need to be closed before plugin output - block should not be inside paragraphs
     *  * 'stack'  - Special case. Plugin wraps other paragraphs. - Stacks can contain paragraphs
     *
     * @see DokuWiki_Syntax_Plugin::getPType()
     */
    function getPType()
    {
        return 'normal';
    }

    /**
     * @see Doku_Parser_Mode::getSort()
     * the mode with the lowest sort number will win out
     * the lowest in the tree must have the lowest sort number
     * No idea why it must be low but inside a teaser, it will work
     * https://www.dokuwiki.org/devel:parser#order_of_adding_modes_important
     */
    function getSort()
    {
        return 10;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @param string $mode
     * @see Doku_Parser_Mode::connectTo()
     */
    function connectTo($mode)
    {

        $pattern = PluginUtility::getLeafTagPattern(self::getTag());
        $this->Lexer->addSpecialPattern($pattern, $mode, 'plugin_' . PluginUtility::$PLUGIN_BASE_NAME . '_' . $this->getPluginComponent());

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
     * @throws Exception
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {

        switch ($state) {

            case DOKU_LEXER_SPECIAL:

                // Get the parameters
                $parameters = PluginUtility::getAttributes($match);
                // TODO ? Download the icon
                return array($state, $parameters);


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

        switch ($format) {

            case 'xhtml':
                {

                    /** @var Doku_Renderer_xhtml $renderer */
                    list($state, $attributes) = $data;
                    if ($state != DOKU_LEXER_SPECIAL) {
                        return false;
                    }

                    $name = "name";
                    if (!array_key_exists($name, $attributes)) {
                        PluginUtility::msg("The name attribute is mandatory for an icon.", PluginUtility::LVL_MSG_ERROR);
                        return false;
                    }
                    $iconName = $attributes[$name];

                    // Trying to find/download the icon file
                    // The name may be a media id directly
                    $mediaFile = mediaFN($iconName);
                    if (!file_exists($mediaFile)) {

                        // Is it a file ?
                        $path =  pathinfo($mediaFile);
                        if ($path['extension']!=""){
                            PluginUtility::msg("The media file ($mediaFile) could not be found. If you want an icon from the material design icon library, indicate a name without extension.", PluginUtility::LVL_MSG_ERROR);
                            return false;
                        }

                        // It may be a icon name from material design
                        $iconNameSpace = $this->getConf(self::CONF_ICONS_MEDIA_NAMESPACE);
                        $mediaId = $iconNameSpace . ":" . $iconName . ".svg";
                        $mediaFile = mediaFN($mediaId);
                        if (!file_exists($mediaFile)) {

                            // The icon was may be not downloaded ?

                            // Create the target directory if it does not exist
                            $pathinfo = pathinfo($mediaFile);
                            $iconDir = $pathinfo['dirname'];
                            if (!file_exists($iconDir)) {
                                $return = mkdir($iconDir, $mode = 0770, $recursive = true);
                                if ($return == false) {
                                    PluginUtility::msg("The icon directory ($iconDir) could not be created.", PluginUtility::LVL_MSG_ERROR);
                                    return false;
                                }
                            }

                            // First try on github
                            $gitUrl = "https://raw.githubusercontent.com/Templarian/MaterialDesign/master/svg/$iconName.svg";
                            $return = file_put_contents($mediaFile, fopen($gitUrl, 'r'));
                            if ($return != false) {
                                PluginUtility::msg("The material design icon ($attributes[$name]) was downloaded to ($mediaId)", PluginUtility::LVL_MSG_INFO);
                            } else {

                                PluginUtility::msg("The file ($gitUrl) could not be downloaded from ($mediaFile)", PluginUtility::LVL_MSG_INFO);

                                // Try the official API
                                // Read the icon meta of
                                // Meta Json file got all icons
                                //
                                //   * Available at: https://raw.githubusercontent.com/Templarian/MaterialDesign/master/meta.json
                                //   * See doc: https://github.com/Templarian/MaterialDesign-Site/blob/master/src/content/api.md)
                                $arrayFormat = true;
                                $iconMetaJson = json_decode(file_get_contents(__DIR__ . '/icon-meta.json'), $arrayFormat);
                                $iconId = null;
                                foreach ($iconMetaJson as $key => $value) {
                                    if ($value['name'] == $iconName) {
                                        $iconId = $value['id'];
                                        break;
                                    }
                                }
                                if ($iconId != null) {


                                    // Download
                                    // Call to the API
                                    // https://dev.materialdesignicons.com/contribute/site/api
                                    $downloadUrl = "https://materialdesignicons.com/api/download/icon/svg/$iconId";
                                    $return = file_put_contents($mediaFile, fopen($downloadUrl, 'r'));
                                    if ($return == false) {
                                        PluginUtility::msg("The file ($downloadUrl) could not be downloaded to ($mediaFile)", PluginUtility::LVL_MSG_ERROR);
                                        return false;
                                    } else {
                                        PluginUtility::msg("The material design icon ($attributes[$name]) was downloaded to ($mediaId)", PluginUtility::LVL_MSG_INFO);
                                    }

                                }

                            }


                        }

                    }

                    if (!file_exists($mediaFile)) {
                        PluginUtility::msg("The icon ($mediaId) could not be found as media file or material design icon", PluginUtility::LVL_MSG_ERROR);
                        return false;
                    }

                    // Build the svg Element
                    try {
                        $mediaSvgXml = simplexml_load_file($mediaFile);
                    } catch (Exception $e) {
                        PluginUtility::msg("The icon file ($mediaFile) could not be loaded as a XML SVG. The error returned is $e", PluginUtility::LVL_MSG_ERROR);
                        return false;
                    }

                    // Unset the name attribute
                    unset($attributes[$name]);
                    $mediaSvgXml->addAttribute('data-name', $iconName);


                    // Width
                    $widthName = "width";
                    $widthValue = "24px";
                    if (array_key_exists($widthName, $attributes)) {
                        $widthValue = $attributes[$widthName];
                        unset($attributes[$widthName]);
                    }
                    $this->setXmlAttribute($widthName, $widthValue, $mediaSvgXml);

                    // Height
                    $heightName = "height";
                    $heightValue = "24px";
                    if (array_key_exists($heightName, $attributes)) {
                        $heightValue = $attributes[$heightName];
                        unset($attributes[$heightName]);
                    }
                    $this->setXmlAttribute($heightName, $heightValue, $mediaSvgXml);

                    // Add fill="currentColor"
                    $pathXml = $mediaSvgXml->{'path'};
                    $this->setXmlAttribute("fill", "currentColor", $pathXml);

                    // Process the style
                    PluginUtility::processStyle($attributes);

                    foreach ($attributes as $name => $value) {
                        $mediaSvgXml->addAttribute($name, $value);
                    }

                    $renderer->doc .= $mediaSvgXml->asXML();

                    return true;
                }
                break;

        }
        return true;
    }


    public
    static function getTag()
    {
        return PluginUtility::getTagName(get_called_class());
    }

    /**
     * @param $attName
     * @param $attValue
     * @param SimpleXMLElement $mediaSvgXml
     */
    public function setXmlAttribute($attName, $attValue, SimpleXMLElement $mediaSvgXml)
    {
        $actualWidthValue = (string)$mediaSvgXml[$attName];
        if ($actualWidthValue != "") {
            $mediaSvgXml[$attName] = $attValue;
        } else {
            $mediaSvgXml->addAttribute($attName, $attValue);
        }
    }


}
