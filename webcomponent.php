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
 * Static Utility class
 */
class webcomponent  {


    // Plugin Name
    const PLUGIN_NAME = 'webcomponent';


    /**
     * @param $match
     * @return array
     *
     * Parse the matched text and return the parameters
     */
    public static function parseMatch($match):array {

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
        return self::PLUGIN_NAME.':';
    }

    /**
     * @param $tag
     * @return string
     * Create a lookahead pattern used to enter in a mode
     */
    public static function getLookAheadPattern($tag)
    {
        return '<'.$tag.'.*?>(?=.*?</'.$tag.'>)';
    }

    public static function getIncludeTagPattern($tag)
    {
        return '<'.$tag.'*?>.*?</'.$tag.'>';
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


}
