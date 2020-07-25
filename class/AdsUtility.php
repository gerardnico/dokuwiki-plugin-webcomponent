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
 * Class AdsUtility
 * @package ComboStrap
 *
 * TODO: Injection: Words Between Ads (https://wpadvancedads.com/manual/minimum-amount-of-words-between-ads/)
 */
class AdsUtility
{

    const CONF_ADS_MIN_LOCAL_LINE_DEFAULT = 2;
    const CONF_ADS_MIN_LOCAL_LINE_KEY = 'AdsMinLocalLine';
    const CONF_ADS_LINE_BETWEEN_DEFAULT = 13;
    const CONF_ADS_LINE_BETWEEN_KEY = 'AdsLineBetween';
    const CONF_ADS_MIN_SECTION_NUMBER_DEFAULT = 2;
    const CONF_ADS_MIN_SECTION_KEY = 'AdsMinSectionNumber';
    const CONF_ADS_SHOW_PLACEHOLDER_DEFAULT = 1;
    const CONF_ADS_SHOW_PLACEHOLDER_KEY = 'ShowPlaceholder';

    public static function showAds($sectionLineCount, $currentLineCountSinceLastAd, $sectionNumber, $adsCounter, $isLastSection)
    {
        global $ACT;
        global $ID;
        if (
            PageUtility::isSideBar() == FALSE && // No display on the sidebar
            $ACT != 'admin' && // Not in the admin page
            isHiddenPage($ID) == FALSE && // No ads on hidden pages
            (
                (
                    $sectionLineCount > self::CONF_ADS_MIN_LOCAL_LINE_DEFAULT && // Doesn't show any ad if the section does not contains this minimum number of line
                    $currentLineCountSinceLastAd > self::CONF_ADS_LINE_BETWEEN_DEFAULT && // Every N line,
                    $sectionNumber > self::CONF_ADS_MIN_SECTION_NUMBER_DEFAULT // Doesn't show any ad before
                )
                or
                // Show always an ad after a number of section
                (
                    $adsCounter == 0 && // Still not ads
                    $sectionNumber > self::CONF_ADS_MIN_SECTION_NUMBER_DEFAULT && // Above the minimum number of section
                    $sectionLineCount > self::CONF_ADS_MIN_LOCAL_LINE_DEFAULT // Minimum line in the current section (to avoid a pub below a header)
                )
                or
                // Sometimes the last section (reference) has not so much line and it avoids to show an ads at the end
                // even if the number of line (space) was enough
                (
                    $isLastSection && // The last section
                    $currentLineCountSinceLastAd > self::CONF_ADS_LINE_BETWEEN_DEFAULT  // Every N line,
                )
            )) {
            return true;
        } else {
            return false;
        }
    }

    public static function showPlaceHolder()
    {
        return self::CONF_ADS_SHOW_PLACEHOLDER_DEFAULT == 1;
    }

    /**
     * Return the full page location
     * @param $name
     * @return string
     */
    public static function getAdPage($name)
    {
        return strtolower(':combostrap:ads:'.$name);
    }
}
