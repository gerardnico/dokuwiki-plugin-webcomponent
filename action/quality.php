<?php


use ComboStrap\PluginUtility;
use ComboStrap\TplConstant;

/**
 * Class action_plugin_combo_quality
 */
class action_plugin_combo_quality extends DokuWiki_Action_Plugin
{


    public function register(Doku_Event_Handler $controller)
    {
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
    }

    function handleAclCheck(&$event, $param)
    {
        /**
         * Low page Rank ?
         */
        $id = $event->data['id'];
        $lowPageRank = $this->isLowQualityPage($id);

        /**
         * Logged in ?
         */
        $user = $event->data['user'];
        $loggedIn = $this->isLoggedIn($user);

        /**
         * If low page rank and not logged in,
         * no authorization
         */
        if (!$loggedIn && $lowPageRank) {
            return $event->result = AUTH_NONE;
        }

    }

    /**
     * @param $id
     * @return bool true if this is a low internal page rank
     */
    private function isLowQualityPage($id)
    {
        if ($id == "lowpage") {
            return true;
        } else {
            return false;
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
     * @param $user
     * @return boolean
     */
    private function isLoggedIn($user)
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
     * @param $event
     */
    private function excludeLowQualityPageFromSearch(&$event)
    {
        foreach (array_keys($event->result) as $idx) {
            if ($this->isLowQualityPage($idx)) {
                unset($event->result[$idx]);
            }
        }
    }


}
