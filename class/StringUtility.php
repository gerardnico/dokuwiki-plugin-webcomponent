<?php

namespace ComboStrap;

/**
 * Class StringUtility
 * @package ComboStrap
 * A class with string utility
 */
class StringUtility
{


    /**
     * Generate a text with a max length of $length
     * and add ... if above
     * @param $myString
     * @param $length
     * @return string
     */
    static function truncateString($myString, $length)
    {
        if (strlen($myString) > $length) {
            $myString = substr($myString, 0, $length) . ' ...';
        }
        return $myString;
    }

    /**
     * @param $string
     * @return string - the string without any carriage return
     * Used to compare string without worrying about carriage return
     */
    public static function normalized($string)
    {
        return str_replace("\n","", $string);
    }

    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function contain($needle, $haystack)
    {
        $pos = strpos($haystack,$needle);
        if ($pos === FALSE){
            return false;
        } else {
            return true;
        }
    }

    public static function toString($value)
    {
        $string = var_export($value,true);

        // An array value gets command in var_export
        $lastCharacterIndex = strlen($string) - 1;
        if ($string[0]==="'" && $string[$lastCharacterIndex]==="'"){
            $string = substr($string,1, strlen($string)-2);
        }
        return $string;

    }

    /**
     * Add an EOL if not present at the end of the string
     * @param $doc
     */
    public static function addEolIfNotPresent(&$doc)
    {
        if ($doc[strlen($doc) - 1] != DOKU_LF) {
            $doc .= DOKU_LF;
        }
    }

    /**
     * Delete the Length from the end
     * @param $doc
     * @param $var
     */
    public static function deleteFromEnd(&$doc, $var)
    {
        if (is_numeric($var)){
            $length = strlen($doc)-$var;
        } else {
            $length = strlen($doc)-strlen($var);
        }
        $doc = substr($doc,0,$length);
    }
}
