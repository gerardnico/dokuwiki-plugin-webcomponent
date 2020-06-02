<?php
/**
 * DokuWiki Syntax Plugin Web Component.
 *
 */
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

/**
 * Are used everywhere in the plugin and the last upgrade just kill them
 * I just add them here
 */
define("DOKU_LEXER_ENTER", 1);
define("DOKU_LEXER_MATCHED", 2);
define("DOKU_LEXER_UNMATCHED", 3);
define("DOKU_LEXER_EXIT", 4);
define("DOKU_LEXER_SPECIAL", 5);

/**
 * Static Utility class
 */
class webcomponent
{


    // Plugin Name
    const PLUGIN_NAME = 'webcomponent';

    // Where to create test pages
    const DOKU_DATA_DIR = '/dokudata/pages';
    const DOKU_CACHE_DIR = '/dokudata/cache';


    /**
     * @param $match
     * @return array
     *
     * Parse the matched text and return the parameters
     */
    public static function parseMatch($match): array
    {

        $parameters = array();

        // /i not case sensitive
        $attributePattern = "\\s*(\w+)\\s*=\\s*[\'\"]{1}([^\`\"]*)[\'\"]{1}\\s*";
        $result = preg_match_all('/' . $attributePattern . '/i', $match, $matches);
        if ($result != 0) {
            foreach ($matches[1] as $key => $parameterKey) {
                $parameters[strtolower($parameterKey)] = $matches[2][$key];
            }
        }
        return $parameters;

    }

    /**
     * @param $get_called_class - the plugin class
     * @return array
     */
    public static function getTags($get_called_class)
    {
        $elements = array();
        $elementName = self::getTagName($get_called_class);
        $elements[] = $elementName;
        $elements[] = strtoupper($elementName);
        return $elements;
    }

    /**
     * @param $get_called_class
     * @return string
     */
    public static function getTagName($get_called_class)
    {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', $get_called_class, 4);
        return (isset($c) ? $c : '');
    }

    public static function getNameSpace()
    {
        // No : at the begin of the namespace please
        return self::PLUGIN_NAME . ':';
    }

    /**
     * @param $tag
     * @return string
     * Create a lookahead pattern used to enter in a mode
     */
    public static function getLookAheadPattern($tag)
    {
        return '<' . $tag . '.*?>(?=.*?</' . $tag . '>)';
    }

    public static function getIncludeTagPattern($tag)
    {
        return '<' . $tag . '*?>.*?</' . $tag . '>';
    }

    public static function render($doku_text)
    {
        $instructions = p_get_instructions($doku_text);
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
     * This function can be added in a setUp function of a test that creates pages
     * in order to get the created pages in the dokuwiki and not in a temp space
     * in order to be able to visualise them
     */
    public static function setUpPagesLocation()
    {
        // Otherwise the page are created in a tmp dir
        // ie C:\Users\gerard\AppData\Local\Temp/dwtests-1550072121.2716/data/
        // and we cannot visualize them
        // This is not on the savedir conf value level because it has no effect on the datadir value
        $conf['datadir'] = getcwd() . self::DOKU_DATA_DIR;
        // Create the dir
        if (!file_exists($conf['datadir'])) {
            mkdir($conf['datadir'], $mode = 0777, $recursive = true);
        }
        $conf['cachetime'] = -1;
        $conf['allowdebug'] = 1; // log in cachedir+debug.log
        $conf['cachedir'] = getcwd() . self::DOKU_CACHE_DIR;
        if (!file_exists($conf['cachedir'])) {
            mkdir($conf['cachedir'], $mode = 0777, $recursive = true);
        }
    }
}
