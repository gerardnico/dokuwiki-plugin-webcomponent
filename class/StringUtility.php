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
}
