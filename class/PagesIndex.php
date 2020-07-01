<?php

/**
 * Class PagesIndex
 * Function on the page index
 */
class PagesIndex
{


    /**
     * @param string $actualId - the actual page id
     * @param string $pageName
     * @return string[]
     */
    public static function pagesWithSameName($pageName, $actualId)
    {

        // The returned object
        $pagesWithSameName = array();

        // There is two much pages with the start name
        global $conf;
        if ($pageName == $conf['start']) {
            return $pagesWithSameName;
        }

        // Get them
        $pagesWithSameName = ft_pageLookup($pageName);

        // Don't add the actual ID
        if (array_key_exists($actualId, $pagesWithSameName)) {
            unset($pagesWithSameName[$actualId]);
        }

        // Return
        return $pagesWithSameName;
    }
}

