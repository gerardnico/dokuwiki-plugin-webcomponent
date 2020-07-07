<?php


namespace ComboStrap;



class UrlUtility
{

    /**
     * Extract the value of a property
     * @param $URL
     * @param $propertyName
     * @return string - the value of the property
     */
    public static function getPropertyValue($URL, $propertyName)
    {
        $parsedQuery = parse_url($URL,PHP_URL_QUERY);
        $parsedQueryArray = [];
        parse_str($parsedQuery,$parsedQueryArray);
        return $parsedQueryArray[$propertyName];
    }
}
