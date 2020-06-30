<?php





/**
 * Class url static
 * List of static utilities
 */
class PluginStatic
{

    /**
     * Constant for the function {@link msg()}
     */
    const LVL_MSG_ERROR = -1;
    const LVL_MSG_INFO = 0;
    const LVL_MSG_SUCCESS = - 1;
    const LVL_MSG_NOTIFY = 2;

    /**
     * The URL base of the documentation
     */
    const URL_BASE = "https://combostrap.com";

    /**
     * @var string - the plugin name
     */
    static $PLUGIN_BASE_NAME;

    /**
     * @var array
     */
    static $INFO_PLUGIN;

    static $lang;

    /**
     * @var string
     */
    static $DIR_RESOURCES;

    /**
     * Validate URL
     * Allows for port, path and query string validations
     * @param string $url string containing url user input
     * @return   boolean     Returns TRUE/FALSE
     */
    static function isValidURL($url)
    {
        // of preg_match('/^https?:\/\//',$url) ? from redirect plugin
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

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
            $sqliteMandatoryMessage = "The Sqlite Plugin is mandatory. Some functionalities of the Web Components Plugin may not work.";
            msg($sqliteMandatoryMessage, self::LVL_MSG_ERROR, $allow = MSG_MANAGERS_ONLY);
            return null;
        }
        $sqlite->getAdapter()->setUseNativeAlter(true);
        $init = $sqlite->init(self::$PLUGIN_BASE_NAME, DOKU_PLUGIN . self::$PLUGIN_BASE_NAME . '/db/');
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
        global $lang;
        self::$lang = $lang[self::$PLUGIN_BASE_NAME];
        self::$DIR_RESOURCES = __DIR__ . '/../_testResources';

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
    public static function msg(string $message, int $level=self::LVL_MSG_ERROR)
    {
        $msg = self::$PLUGIN_BASE_NAME." - $message";
        msg($msg,$level,$allow=MSG_MANAGERS_ONLY);
        dbg($msg);
        if (defined('DOKU_UNITTEST')) {
            throw new RuntimeException($msg);
        }
    }

    /**
     * Generate a text with a max length of $length
     * and add ... if above
     * @param $myString
     * @param $length
     * @return string
     */
    function truncateString($myString, $length)
    {
        if (strlen($myString) > $length) {
            $myString = substr($myString, 0, $length) . ' ...';
        }
        return $myString;
    }

}

PluginStatic::init();
