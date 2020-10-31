<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;

/**
 * Class PageUtility
 * @package ComboStrap
 * See also {@link pageutils.php}
 */
class FsWikiUtility
{


    /**
     * Determine if the current page is a sidebar (a bar)
     * @return bool
     */
    public static function isSideBar()
    {
        global $INFO;
        global $ID;
        $isSidebar = false;
        if ($INFO != null) {
            $id = $INFO['id'];
            if ($ID != $id) {
                $isSidebar = TRUE;
            }
        }
        return $isSidebar;
    }

    /**
     * Return the main page id
     * Not the sidebar
     * @return mixed|string
     */
    public static function getMainPageId()
    {
        global $INFO;
        global $ID;
        $id = $ID;
        if ($INFO != null) {
            $id = $INFO['id'];
        }
        return $id;
    }

    /**
     * Return all pages and/of sub-namespaces (subdirectory) of a namespace (ie directory)
     * Adapted from feed.php
     *
     * @param string $id The container of the pages
     * @return array An array of the pages for the namespace
     */
    static function getChildren($id)
    {
        require_once(__DIR__ . '/../../../../inc/search.php');
        global $conf;

        $ns = ':' . cleanID($id);
        // ns as a path
        $ns = utf8_encodeFN(str_replace(':', '/', $ns));

        $data = array();

        // Options of the callback function search_universal
        // in the search.php file
        $search_opts = array(
            'depth' => 1,
            'pagesonly' => true,
            'listfiles' => true,
            'listdirs' => true,
            'firsthead' => true
        );
        // search_universal is a function in inc/search.php that accepts the $search_opts parameters
        search($data, // The returned data
            $conf['datadir'], // The root
            'search_universal', // The recursive function (callback)
            $search_opts, // The options given to the recursive function
            $ns, // The current directory
            $lvl = 1 // Only one level in the tree
        );

        return $data;
    }

    /**
     * Return the page index of a namespace of null if it does not exist
     * ie the index.html
     * @param $id
     * @return string|null
     */
    public static function getIndex($id)
    {
        global $conf;

        $id = $id . ":";

        $startPageName = $conf['start'];
        if (page_exists($id . $startPageName)) {
            // start page inside namespace
            return $id . $startPageName;
        } elseif (page_exists($id . noNS(cleanID($id)))) {
            // page named like the NS inside the NS
            return $id . noNS(cleanID($id));
        } elseif (page_exists($id)) {
            // page like namespace exists
            return substr($id, 0, -1);
        } else {
            return null;
        }
    }

    public static function getTitle($pageId)
    {
        $name = $pageId;
        // The title of the page
        if (useHeading('navigation')) {
            // $title = $page['title'] can not be used to retrieve the title
            // because it didn't encode the HTML tag
            // for instance if <math></math> is used, the output must have &lgt ...
            // otherwise browser may add quote and the math plugin will not work
            // May be a solution was just to encode the output

            // Don't use the below procedure, it will return
            // PHP Fatal error:  Allowed memory size of 134217728 bytes exhausted (tried to allocate 98570240 bytes) in /inc/Cache/CacheInstructions.php on line 44
            //$name = tpl_pagetitle($pageId, true);

            $name = p_get_first_heading($pageId);

        }
        return $name;
    }
}
