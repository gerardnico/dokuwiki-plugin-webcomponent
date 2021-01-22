<?php
/**
 * Copyright (c) 2021. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;


use Doku_Renderer_metadata;
use Doku_Renderer_xhtml;

/**
 * Class SeoUtility
 * @package ComboStrap
 *
 */
class SeoUtility
{

    const CONF_PRIVATE_LOW_QUALITY_PAGE_ENABLED = "privateLowQualityPageEnabled";

    /**
     * Is logged in
     * @param $user
     * @return boolean
     */
    static function isLoggedIn($user)
    {
        $loggedIn = false;
        if (!empty($user)) {
            $loggedIn = true;
        } else {
            global $INPUT;
            if ($INPUT->server->has('REMOTE_USER')) {
                $loggedIn = true;
            }
        }
        return $loggedIn;
    }

    /**
     * Low page quality
     * @param $id
     * @return bool true if this is a low internal page rank
     */
    static function isLowQualityPage($id)
    {
        if ($id == "lowpage" || $id == ":lowpage") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * If low page rank and not logged in,
     * no authorization
     * @param $id
     * @param $user
     * @return bool
     */
    public static function isPageToExclude($id, $user = '')
    {
        if (!self::isLoggedIn($user)) {
            if (self::isLowQualityPage($id)) {
                /**
                 * Low quality page should not
                 * be public and readable for the search engine
                 */
                return true;
            } else {
                /**
                 * Do not cache high quality page
                 */
                return false;
            }
        } else {
            /**
             * Logged in, no exclusion
             */
            return false;
        }

    }

}
