<?php


use ComboStrap\SeoUtility;

require_once(__DIR__ . '/../class/SeoUtility.php');

/**
 * Class action_plugin_combo_quality
 *
 *
 * https://www.dokuwiki.org/plugin:qc
 * https://www.dokuwiki.org/plugin:readability
 *
 * Quality Guideline SEO
 * https://developers.google.com/search/docs/advanced/guidelines/auto-gen-content
 * https://support.google.com/webmasters/answer/9044175#thin-content
 */
class action_plugin_combo_quality extends DokuWiki_Action_Plugin
{


    public function register(Doku_Event_Handler $controller)
    {

        if ($this->getConf(SeoUtility::CONF_LOW_QUALITY_PAGE_NOT_PUBLIC_ENABLE)) {
            /**
             * https://www.dokuwiki.org/devel:event:auth_acl_check
             */
            $controller->register_hook('AUTH_ACL_CHECK', 'AFTER', $this, 'handleAclCheck', array());
            /**
             * https://www.dokuwiki.org/devel:event:search_query_pagelookup
             */
            $controller->register_hook('SEARCH_QUERY_PAGELOOKUP', 'AFTER', $this, 'handleSearchPageLookup', array());

            /**
             * https://www.dokuwiki.org/devel:event:search_query_fullpage
             */
            $controller->register_hook('SEARCH_QUERY_FULLPAGE', 'AFTER', $this, 'handleSearchFullPage', array());
            /**
             * https://www.dokuwiki.org/devel:event:feed_data_process
             */
            $controller->register_hook('FEED_DATA_PROCESS', 'AFTER', $this, 'handleRssFeed', array());
        }

    }

    function handleAclCheck(&$event, $param)
    {

        $id = $event->data['id'];
        $user = $event->data['user'];
        if (SeoUtility::isPageToExclude($id, $user)) {
            return $event->result = AUTH_NONE;
        }

    }

    /**
     * @param $event
     * @param $param
     * The autocomplete do a search on page name
     */
    function handleSearchPageLookup(&$event, $param)
    {
        $this->excludeLowQualityPageFromSearch($event);
    }

    /**
     * @param $event
     * @param $param
     * The search page do a search on page name
     */
    function handleSearchFullPage(&$event, $param)
    {

        $this->excludeLowQualityPageFromSearch($event);
    }

    /**
     *
     * @param $event
     * @param $param
     * The Rss
     * https://www.dokuwiki.org/syndication
     * Example
     * https://example.com/feed.php?type=rss2&num=5
     */
    function handleRssFeed(&$event, $param)
    {
        $this->excludeLowQualityPageFromSearch($event);
    }

    /**
     * @param $event
     */
    private
    function excludeLowQualityPageFromSearch(&$event)
    {

        foreach (array_keys($event->result) as $idx) {
            if (SeoUtility::isPageToExclude($idx)) {
                unset($event->result[$idx]);
            }
        }

    }


}
