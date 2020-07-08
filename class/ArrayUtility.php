<?php


namespace ComboStrap;


use DateTime;

class ArrayUtility
{

    /**
     * Print recursively an array as an HTML list
     * @param array $toPrint
     * @param string $content - [Optional] - append to this variable if given
     * @return string - an array as an HTML list or $content if given as variable
     */
    public static function formatAsHtmlList(array $toPrint, &$content = "")
    {
        /**
         * Sort it on the key
         */
        ksort($toPrint);

        $content .= '<ul>';
        foreach ($toPrint as $key => $value) {
            $keyProcessed = str_replace("_", " ", ucfirst($key));
            if (is_array ( $value )){
                $content .= '<li>' . $keyProcessed . ' : ' ;
                self::formatAsHtmlList($value, $content );
                $content .= '</li>';
            } else {
                if (preg_match('/date|created|modified/i',  $key ) && is_numeric($value)){
                    $value = date(DateTime::ISO8601, $value);
                }
                $content .= '<li>' . $keyProcessed . ' : ' . $value . '</li>';
            }
        }
        $content .= '</ul>';
        return $content;
    }
}
