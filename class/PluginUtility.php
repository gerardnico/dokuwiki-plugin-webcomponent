<?php


namespace ComboStrap;


use RuntimeException;
use TestRequest;

/**
 * Are used everywhere in the plugin and the last upgrade just kill them
 * I just add them here
 */
if (!defined("DOKU_LEXER_ENTER")) {
    define("DOKU_LEXER_ENTER", 1);
    define("DOKU_LEXER_MATCHED", 2);
    define("DOKU_LEXER_UNMATCHED", 3);
    define("DOKU_LEXER_EXIT", 4);
    define("DOKU_LEXER_SPECIAL", 5);
}

/**
 * Class url static
 * List of static utilities
 */
class PluginUtility
{

    /**
     * Constant for the function {@link msg()}
     * -1 = error, 0 = info, 1 = success, 2 = notify
     */
    const LVL_MSG_ERROR = -1;
    const LVL_MSG_INFO = 0;
    const LVL_MSG_SUCCESS = 1;
    const LVL_MSG_WARNING = 2;
    const LVL_MSG_DEBUG = 3;
    const DOKU_DATA_DIR = '/dokudata/pages';
    const DOKU_CACHE_DIR = '/dokudata/cache';


    /**
     * The URL base of the documentation
     */
    static $URL_BASE;


    /**
     * @var string - the plugin base name (ie the directory)
     */
    static $PLUGIN_BASE_NAME;

    /**
     * @var array
     */
    static $INFO_PLUGIN;

    static $PLUGIN_LANG;

    /**
     * @var string
     */
    static $DIR_RESOURCES;

    /**
     * The plugin name
     * (not the same than the base as it's not related to the directory
     * @var string
     */
    public static $PLUGIN_NAME;


    /**
     * Init the data store
     * Sqlite cannot be static because
     * between two test classes
     * the data dir where the database is saved is deleted.
     *
     * You need to store the variable in your plugin
     *
     * @return helper_plugin_sqlite $sqlite
     */
    static function getSqlite()
    {

        /** @var helper_plugin_sqlite $sqlite */
        $sqlite = plugin_load('helper', 'sqlite');
        if ($sqlite == null) {
            # TODO: Man we cannot get the message anymore ['SqliteMandatory'];
            $sqliteMandatoryMessage = "The Sqlite Plugin is mandatory. Some functionalities of the Combostraps Plugin may not work.";
            msg($sqliteMandatoryMessage, self::LVL_MSG_ERROR, $allow = MSG_MANAGERS_ONLY);
            return null;
        }
        $sqlite->getAdapter()->setUseNativeAlter(true);

        // The name of the database (on windows, it should be
        $dbname = strtolower(self::$PLUGIN_BASE_NAME);
        global $conf;

        $oldDbName = '404manager';
        $oldDbFile = $conf['metadir'] . "/{$oldDbName}.sqlite";
        $oldDbFileSqlite3 = $conf['metadir'] . "/{$oldDbName}.sqlite3";
        if (file_exists($oldDbFile) || file_exists($oldDbFileSqlite3)) {
            $dbname = $oldDbName;
        }

        $init = $sqlite->init($dbname, DOKU_PLUGIN . PluginUtility::$PLUGIN_BASE_NAME . '/db/');
        if (!$init) {
            # TODO: Message 'SqliteUnableToInitialize'
            $message = "Unable to initialize Sqlite";
            self::msg($message, MSG_MANAGERS_ONLY);
        }
        return $sqlite;

    }


    /**
     * Initiate the static variable
     * See the call after this class
     */
    static function init()
    {

        $pluginInfoFile = __DIR__ . '/../plugin.info.txt';
        self::$INFO_PLUGIN = confToHash($pluginInfoFile);
        self::$PLUGIN_BASE_NAME = self::$INFO_PLUGIN['base'];
        self::$PLUGIN_NAME = 'ComboStrap';
        global $lang;
        self::$PLUGIN_LANG = $lang[self::$PLUGIN_BASE_NAME];
        self::$DIR_RESOURCES = __DIR__ . '/../_testResources';
        self::$URL_BASE = "https://" . parse_url(self::$INFO_PLUGIN['url'], PHP_URL_HOST);

    }

    /**
     * @param $inputExpression
     * @return false|int 1|0
     * returns:
     *    - 1 if the input expression is a pattern,
     *    - 0 if not,
     *    - FALSE if an error occurred.
     */
    static function isRegularExpression($inputExpression)
    {

        $regularExpressionPattern = "/(\\/.*\\/[gmixXsuUAJ]?)/";
        return preg_match($regularExpressionPattern, $inputExpression);

    }

    /**
     * Send a message to a manager and log it
     * Fail if in test
     * @param string $message
     * @param int $level - the level see LVL constant
     */
    public static function msg($message, $level = self::LVL_MSG_ERROR)
    {
        $msg = self::$PLUGIN_BASE_NAME . " - $message";
        if ($level!=self::LVL_MSG_DEBUG) {
            msg($msg, $level, $allow = MSG_MANAGERS_ONLY);
        }
        /**
         * Print to a log file
         * Note: {@link dbg()} dbg print to the web page
         */
        dbglog($msg);
        if (defined('DOKU_UNITTEST') && ($level == self::LVL_MSG_WARNING || $level == self::LVL_MSG_ERROR)) {
            throw new RuntimeException($msg);
        }
    }

    /**
     * @param $component
     * @return string
     */
    public static function getModeForComponent($component)
    {
        return "plugin_" . strtolower(PluginUtility::$PLUGIN_BASE_NAME) . "_" . $component;
    }

    /**
     * @param $tag
     * @return string
     *
     * Create a lookahead pattern for a container tag used to enter in a mode
     */
    public static function getContainerTagPattern($tag)
    {
        return '<' . $tag . '.*?>(?=.*?<\/' . $tag . '>)';
    }

    /**
     * Take an array  where the key is the attribute name
     * and return a HTML tag string
     *
     * The attribute name and value are escaped
     *
     * @param $attributes
     * @return string
     */
    public static function array2HTMLAttributes($attributes)
    {
        // Process the style attributes if any
        self::processStyle($attributes);

        // Then transform
        $tagAttributeString = "";
        foreach ($attributes as $name => $value) {
            $tagAttributeString .= hsc($name) . '="' . hsc($value) . '" ';
        }
        return trim($tagAttributeString);
    }

    /**
     * @param $string
     * @return array
     *
     * Parse a string to HTML attribute
     */
    public static function parse2HTMLAttributes($string)
    {

        $parameters = array();

        // /i not case sensitive
        $attributePattern = "\\s*([-\w]+)\\s*=\\s*[\'\"]{1}([^\`\"]*)[\'\"]{1}\\s*";
        $result = preg_match_all('/' . $attributePattern . '/i', $string, $matches);
        if ($result != 0) {
            foreach ($matches[1] as $key => $parameterKey) {
                $parameters[hsc(strtolower($parameterKey))] = hsc($matches[2][$key]);
            }
        }
        return $parameters;

    }

    /**
     * Return the attribute of a tag
     * Because they are users input, they are all escaped
     * @param $match
     * @return array
     */
    public static function getTagAttributes($match)
    {

        // Until the first >
        $pos = strpos($match,">");
        if ($pos == false){
            self::msg("The match does not contain any tag. Match: {$match}",self::LVL_MSG_ERROR);
            return array();
        }
        $match = substr($match,0,$pos);


        // Trim to start clean
        $match = trim($match);

        // Suppress the <
        if ($match[0] == "<") {
            $match = substr($match, 1);
        }

        // Suppress the / for a leaf tag
        if ($match[strlen($match)] == "/") {
            $match = substr($match, 0, strlen($match) - 1);
        }

        // Suppress the tag name (ie until the first blank)
        $spacePosition = strpos($match, " ");
        if (!$spacePosition) {
            // No space, meaning this is only the tag name
            return array();
        }
        $match = trim(substr($match, $spacePosition));
        if ($match == "") {
            return array();
        }

        // Do we have a type as first argument ?
        $attributes = array();
        $spacePosition = strpos($match, " ");
        if ($spacePosition) {
            $firstArgument = substr($match, 0, $spacePosition);
        } else {
            $firstArgument = $match;
        }
        if (!strpos($firstArgument, "=")) {
            $attributes["type"] = $firstArgument;
            // Suppress the type
            $match = substr($match, strlen($firstArgument));
        }

        // Parse the remaining attributes
        $parsedAttributes = self::parse2HTMLAttributes($match);

        // Merge and return
        $attributes = array_merge($attributes, $parsedAttributes);;
        return $attributes;

    }

    /**
     * @param array $styleProperties - an array of CSS properties with key, value
     * @return string - the value for the style attribute (ie all rules where joined with the comma)
     */
    public static function array2InlineStyle(array $styleProperties)
    {
        $inlineCss = "";
        foreach ($styleProperties as $key => $value) {
            $inlineCss .= "$key:$value;";
        }
        // Suppress the last ;
        if ($inlineCss[strlen($inlineCss) - 1] == ";") {
            $inlineCss = substr($inlineCss, 0, -1);
        }
        return $inlineCss;
    }

    /**
     * @param $tag
     * @return string
     * Create a pattern used where the tag is not a container.
     * ie
     * <br/>
     * <icon/>
     * This is generatlly used with a subtition plugin
     * where the tag is just replaced
     */
    public static function getLeafTagPattern($tag)
    {
        return '<' . $tag . '.*?/>';
    }

    /**
     * Just call this function from a class like that
     *     getTageName(get_called_class())
     * to get the tag name (ie the component plugin)
     * of a syntax plugin
     *
     * @param $get_called_class
     * @return string
     */
    public static function getTagName($get_called_class)
    {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', $get_called_class, 4);
        return (isset($c) ? $c : '');
    }

    /**
     * Just call this function from a class like that
     *     getAdminPageName(get_called_class())
     * to get the page name of a admin plugin
     *
     * @param $get_called_class
     * @return string - the admin page name
     */
    public static function getAdminPageName($get_called_class)
    {
        $names = explode('_', $get_called_class);
        $names = array_slice($names, -2);
        return implode('_', $names);
    }

    public static function getNameSpace()
    {
        // No : at the begin of the namespace please
        return self::$PLUGIN_BASE_NAME . ':';
    }

    /**
     * @param $get_called_class - the plugin class
     * @return array
     */
    public static function getTags($get_called_class)
    {
        $elements = array();
        $elementName = PluginUtility::getTagName($get_called_class);
        $elements[] = $elementName;
        $elements[] = strtoupper($elementName);
        return $elements;
    }

    /**
     * Render a text
     * @param $pageContent
     * @return string|null
     */
    public static function render($pageContent)
    {
        $instructions = p_get_instructions($pageContent);
        $lastPBlockPosition = sizeof($instructions) - 2;
        if ($instructions[1][0] == 'p_open') {
            unset($instructions[1]);
        }
        if ($instructions[$lastPBlockPosition][0] == 'p_close') {
            unset($instructions[$lastPBlockPosition]);
        }
        return p_render('xhtml', $instructions, $info);
    }

    /**
     * Set the environment to be able to
     * run a {@link TestRequest} as admin
     * @param TestRequest $request
     */
    public static function runAsAdmin($request)
    {
        global $conf;
        $conf['useacl'] = 1;
        $user = 'admin';
        $conf['superuser'] = $user;

        // $_SERVER[] = $user;
        $request->setServer('REMOTE_USER', $user);


        // global $USERINFO;
        // $USERINFO['grps'] = array('admin', 'user');

        // global $INFO;
        // $INFO['ismanager'] = true;

    }

    /**
     * This method will takes attributes
     * and process the plugin styling attribute such as width and height
     * to put them in a style HTML attrbute
     * @param $attributes
     */
    public static function processStyle(&$attributes)
    {
        // Style (color)
        $styleAttributeName = "style";
        $styleProperties = array();
        if (array_key_exists($styleAttributeName, $attributes)) {
            foreach (explode(";", $attributes[$styleAttributeName]) as $property) {
                list($key, $value) = explode(":", $property);
                if ($key != "") {
                    $styleProperties[$key] = $value;
                }
            }
        }
        $colorAttributes = ["color", "background-color", "border-color"];
        foreach ($colorAttributes as $colorAttribute) {
            if (array_key_exists($colorAttribute, $attributes)) {
                $styleProperties[$colorAttribute] = self::getColorValue($attributes[$colorAttribute]);
                unset($attributes[$colorAttribute]);
            }
        }

        $widthName = "width";
        if (array_key_exists($widthName, $attributes)) {
            $styleProperties[$widthName] = trim($attributes[$widthName]);
            unset($attributes[$widthName]);
        }

        $heightName = "height";
        if (array_key_exists($heightName, $attributes)) {
            $styleProperties[$heightName] = trim($attributes[$heightName]);
            unset($attributes[$heightName]);
        }

        if (sizeof($styleProperties) != 0) {
            $attributes[$styleAttributeName] = PluginUtility::array2InlineStyle($styleProperties);
        }

    }

    /**
     * Return a combostrap value to a web color value
     * @param string $color a color value
     * @return string
     */
    public static function getColorValue($color)
    {
        if ($color[0] == "#") {
            $colorValue = $color;
        } else {
            $colorValue = "var(--" . $color . ")";
        }
        return $colorValue;
    }

    /**
     * Return the name of the requested script
     */
    public static function getRequestScript()
    {
        $scriptPath = null;
        $testPropertyValue = self::getPropertyValue("SCRIPT_NAME");
        if (defined('DOKU_UNITTEST') && $testPropertyValue != null) {
            return $testPropertyValue;
        }
        if (array_key_exists("DOCUMENT_URI", $_SERVER)) {
            $scriptPath = $_SERVER["DOCUMENT_URI"];
        }
        if ($scriptPath == null && array_key_exists("SCRIPT_NAME", $_SERVER)) {
            $scriptPath = $_SERVER["SCRIPT_NAME"];
        }
        if ($scriptPath == null) {
            msg("Unable to find the main script", self::LVL_MSG_ERROR);
        }
        $path_parts = pathinfo($scriptPath);
        return $path_parts['basename'];
    }

    /**
     *
     * @param $name
     * @param $default
     * @return string - the value of a query string property or if in test mode, the value of a test variable
     * set with {@link self::setTestProperty}
     * This is used to test script that are not supported by the dokuwiki test framework
     * such as css.php
     */
    public static function getPropertyValue($name, $default = null)
    {
        global $INPUT;
        $value = $INPUT->str($name);
        if ($value == null && defined('DOKU_UNITTEST')) {
            global $COMBO;
            $value = $COMBO[$name];
        }
        if ($value == null) {
            return $default;
        } else {
            return $value;
        }

    }

    /**
     * Create an URL to the documentation website
     * @param $canonical - canonical id or slug
     * @param $text
     * @return string - an url
     */
    public static function getUrl($canonical, $text)
    {
        /** @noinspection SpellCheckingInspection */
        return '<a href="'.self::$URL_BASE.'/'. str_replace(":","/",$canonical).'" class="urlextern" title="'.$text.'">'.$text.'</a>';
    }

    /**
     * An utility function to not search every time which array should be first
     * @param array $inlineAttributes - the component inline attributes
     * @param array $defaultAttributes - the default configuration attributes
     * @return array - a merged array
     */
    public static function mergeAttributes(array $inlineAttributes, array $defaultAttributes = array())
    {
        return array_merge($defaultAttributes,$inlineAttributes);
    }

    /**
     * A pattern for a container tag
     * that needs to catch the content
     *
     * Use as a special pattern (substition)
     *
     * The {@link \syntax_plugin_combo_math} use it
     * @param $tag
     * @return string - a pattern
     */
    public static function getLeafContainerTagPattern($tag)
    {
        return '<' . $tag . '.*?>.*?<\/' . $tag . '>';
    }

    /**
     * Return the content of a tag
     * <math>Content</math>
     * @param $match
     * @return string the content
     */
    public static function getTagContent($match)
    {
        // From the first >
        $start = strpos($match,">");
        if ($start == false){
            self::msg("The match does not contain any opening tag. Match: {$match}",self::LVL_MSG_ERROR);
            return "";
        }
        $match = substr($match, $start + 1);
        // If this is the last character, we get a false
        if ($match == false){
            self::msg("The match does not contain any closing tag. Match: {$match}",self::LVL_MSG_ERROR);
            return "";
        }

        $end = strpos($match,"</");
        if ($end == false){
            self::msg("The match does not contain any closing tag. Match: {$match}",self::LVL_MSG_ERROR);
            return "";
        }

        return substr($match,0,$end);
    }

    /**
     *
     * Check if a HTML tag was already added for a request
     * The request id is just the timestamp
     * An indicator array should be provided
     * @param $indicators
     * @return bool
     */
    public static function htmlSnippetAlreadyAdded(&$indicators)
    {
        global $ID;
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])){
            // since php 5.4
            $requestTime = $_SERVER['REQUEST_TIME_FLOAT'];
        } else {
            // DokuWiki test framework use this
            $requestTime = $_SERVER['REQUEST_TIME'];
        }
        $uniqueId = hash( 'crc32b', $_SERVER['REMOTE_ADDR']. $_SERVER['REMOTE_PORT']. $requestTime . $ID);
        if (array_key_exists($uniqueId, $indicators)){
            return true;
        } else {
            $indicators[$uniqueId]=$requestTime;
            return false;
        }
    }



}

PluginUtility::init();
