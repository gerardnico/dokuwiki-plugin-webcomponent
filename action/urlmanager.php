<?php

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
// Needed for the page lookup
//require_once(DOKU_INC . 'inc/fulltext.php');
// Needed to get the redirection manager
// require_once(DOKU_PLUGIN . 'action.php');

require_once(__DIR__ . '/../class/PageRules.php');
require_once(__DIR__ . '/../class/UrlCanonical.php');
require_once(__DIR__ . '/urlmessage.php');

/**
 * Class action_plugin_webcomponent_url
 *
 * The actual URL manager
 *
 *
 */
class action_plugin_webcomponent_urlmanager extends DokuWiki_Action_Plugin
{


    // The redirect type
    const REDIRECT_HTTP = 'Http';
    const REDIRECT_ID = 'Id';

    // Where the target id value comes from
    const TARGET_ORIGIN_PAGE_RULES = 'pageRules';
    const TARGET_ORIGIN_CANONICAL = 'canonical';
    const TARGET_ORIGIN_START_PAGE = 'startPage';
    const TARGET_ORIGIN_BEST_PAGE_NAME = 'bestPageName';
    const TARGET_ORIGIN_BEST_NAMESPACE = 'bestNamespace';
    const TARGET_ORIGIN_SEARCH_ENGINE = 'searchEngine';


    // The constant parameters
    const GO_TO_SEARCH_ENGINE = 'GoToSearchEngine';
    const GO_TO_BEST_NAMESPACE = 'GoToBestNamespace';
    const GO_TO_BEST_PAGE_NAME = 'GoToBestPageName';
    const GO_TO_BEST_END_PAGE_NAME = 'GoToBestEndPageName';
    const GO_TO_NS_START_PAGE = 'GoToNsStartPage';
    const GO_TO_EDIT_MODE = 'GoToEditMode';
    const NOTHING = 'Nothing';

    /** @var string - a name used in log and other places */
    const NAME = 'Url Manager';
    const CANONICAL = 'url/manager';


    /**
     * @var
     */
    private $sqlite;
    /**
     * @var UrlCanonical
     */
    private $canonicalManager;
    /**
     * @var PageRules
     */
    private $pageRules;


    function __construct()
    {
        // enable direct access to language strings
        // ie $this->lang
        $this->setupLocale();

    }


    function register(Doku_Event_Handler $controller)
    {


        /* This will call the function _handle404 */
        $controller->register_hook('DOKUWIKI_STARTED',
            'AFTER',
            $this,
            '_handle404',
            array());

    }

    /**
     * Verify if there is a 404
     * Inspiration comes from <a href="https://github.com/splitbrain/dokuwiki-plugin-notfound/blob/master/action.php">Not Found Plugin</a>
     * @param $event Doku_Event
     * @param $param
     * @return bool not required
     * @throws Exception
     */
    function _handle404(&$event, $param)
    {

        // We are instantiating them here and not in the constructor
        // because otherwise the sqlite plugin would become mandatory for all test
        // because dokuwiki instantiate all action class first
        if ($this->sqlite == null) {
            $this->sqlite = PluginStatic::getSqlite();
            if ($this->sqlite == null) {
                return false;
            } else {
                $this->canonicalManager = new UrlCanonical($this->sqlite);
                $this->pageRules = new PageRules($this->sqlite);
            }
        }

        global $INFO;
        if ($INFO['exists']) {
            action_plugin_webcomponent_urlmessage::unsetNotification();
            // Check if there is a canonical meta
            $this->canonicalManager->processCanonicalMeta();
            return false;
        }


        global $ACT;
        if ($ACT != 'show') return false;


        // Global variable needed in the process
        global $ID;
        global $conf;

        // Do we have a canonical ?
        $targetPage = $this->canonicalManager->getPageIdFromCanonical($ID);
        if ($targetPage) {

            if (page_exists($targetPage)) {
                $this->IdRedirect($targetPage, self::TARGET_ORIGIN_CANONICAL);
                return true;
            } else {
                //TODO: log warning
            }

        }

        // If there is a redirection defined in the redirection table
        $result = $this->processingPageRules();
        if ($result) {
            // A redirection has occurred
            // finish the process
            return true;
        }

        /**
         *
         * There was no redirection found, redirect to edit mode if writer
         *
         */
        if ($this->userCanWrite() && $this->getConf(self::GO_TO_EDIT_MODE) == 1) {

            $this->gotToEditMode($event);
            // Stop here
            return true;

        }

        /*
         *  We are still a reader, the redirection does not exist the user is not allowed to edit the page (public of other)
         */
        if ($this->getConf('ActionReaderFirst') == self::NOTHING) {
            return true;
        }

        // We are reader and their is no redirection set, we apply the algorithm
        $readerAlgorithms = array();
        $readerAlgorithms[0] = $this->getConf('ActionReaderFirst');
        $readerAlgorithms[1] = $this->getConf('ActionReaderSecond');
        $readerAlgorithms[2] = $this->getConf('ActionReaderThird');

        $i = 0;
        while (isset($readerAlgorithms[$i])) {

            switch ($readerAlgorithms[$i]) {

                case self::NOTHING:
                    return true;
                    break;

                case self::GO_TO_BEST_END_PAGE_NAME:

                    break;

                case self::GO_TO_NS_START_PAGE:

                    // Start page with the conf['start'] parameter
                    $startPage = getNS($ID) . ':' . $conf['start'];
                    if (page_exists($startPage)) {
                        $this->httpRedirect($startPage, self::TARGET_ORIGIN_START_PAGE);
                        return true;
                    }

                    // Start page with the same name than the namespace
                    $startPage = getNS($ID) . ':' . curNS($ID);
                    if (page_exists($startPage)) {
                        $this->httpRedirect($startPage, self::TARGET_ORIGIN_START_PAGE);
                        return true;
                    }
                    break;

                case self::GO_TO_BEST_PAGE_NAME:

                    $bestPageId = null;

                    $bestPage = $this->getBestPage($ID);
                    $bestPageId = $bestPage['id'];
                    $scorePageName = $bestPage['score'];

                    // Get Score from a Namespace
                    $bestNamespace = $this->scoreBestNamespace($ID);
                    $bestNamespaceId = $bestNamespace['namespace'];
                    $namespaceScore = $bestNamespace['score'];

                    // Compare the two score
                    if ($scorePageName > 0 or $namespaceScore > 0) {
                        if ($scorePageName > $namespaceScore) {
                            $this->httpRedirect($bestPageId, self::TARGET_ORIGIN_BEST_PAGE_NAME);
                        } else {
                            $this->httpRedirect($bestNamespaceId, self::TARGET_ORIGIN_BEST_PAGE_NAME);
                        }
                        return true;
                    }
                    break;

                case self::GO_TO_BEST_NAMESPACE:

                    $scoreNamespace = $this->scoreBestNamespace($ID);
                    $bestNamespaceId = $scoreNamespace['namespace'];
                    $score = $scoreNamespace['score'];

                    if ($score > 0) {
                        $this->httpRedirect($bestNamespaceId, self::TARGET_ORIGIN_BEST_NAMESPACE);
                        return true;
                    }
                    break;

                case self::GO_TO_SEARCH_ENGINE:

                    $this->redirectToSearchEngine();

                    return true;
                    break;

                // End Switch Action
            }

            $i++;
            // End While Action
        }
        // End if not connected

        return true;

    }


    /**
     * getBestNamespace
     * Return a list with 'BestNamespaceId Score'
     * @param $id
     * @return array
     */
    private
    function scoreBestNamespace($id)
    {

        global $conf;

        // Parameters
        $pageNameSpace = getNS($id);

        // If the page has an existing namespace start page take it, other search other namespace
        $startPageNameSpace = $pageNameSpace . ":";
        $dateAt = '';
        // $startPageNameSpace will get a full path (ie with start or the namespace
        resolve_pageid($pageNameSpace, $startPageNameSpace, $exists, $dateAt, true);
        if (page_exists($startPageNameSpace)) {
            $nameSpaces = array($startPageNameSpace);
        } else {
            $nameSpaces = ft_pageLookup($conf['start']);
        }

        // Parameters and search the best namespace
        $pathNames = explode(':', $pageNameSpace);
        $bestNbWordFound = 0;
        $bestNamespaceId = '';
        foreach ($nameSpaces as $nameSpace) {

            $nbWordFound = 0;
            foreach ($pathNames as $pathName) {
                if (strlen($pathName) > 2) {
                    $nbWordFound = $nbWordFound + substr_count($nameSpace, $pathName);
                }
            }
            if ($nbWordFound > $bestNbWordFound) {
                // Take only the smallest namespace
                if (strlen($nameSpace) < strlen($bestNamespaceId) or $nbWordFound > $bestNbWordFound) {
                    $bestNbWordFound = $nbWordFound;
                    $bestNamespaceId = $nameSpace;
                }
            }
        }

        $startPageFactor = $this->getConf('WeightFactorForStartPage');
        $nameSpaceFactor = $this->getConf('WeightFactorForSameNamespace');
        if ($bestNbWordFound > 0) {
            $bestNamespaceScore = $bestNbWordFound * $nameSpaceFactor + $startPageFactor;
        } else {
            $bestNamespaceScore = 0;
        }


        return array(
            'namespace' => $bestNamespaceId,
            'score' => $bestNamespaceScore
        );

    }

    /**
     * @param $event
     */
    private
    function gotToEditMode(&$event)
    {
        global $ID;
        global $conf;


        global $ACT;
        $ACT = 'edit';

        // If this is a side bar no message.
        // There is always other page with the same name
        $pageName = noNS($ID);
        if ($pageName != $conf['sidebar']) {

            action_plugin_webcomponent_urlmessage::notify($ID, self::GO_TO_EDIT_MODE);

        }


    }

    /**
     * Return if the user has the right/permission to create/write an article
     * @return bool
     */
    private
    function userCanWrite()
    {
        global $ID;

        if ($_SERVER['REMOTE_USER']) {
            $perm = auth_quickaclcheck($ID);
        } else {
            $perm = auth_aclcheck($ID, '', null);
        }

        if ($perm >= AUTH_EDIT) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Redirect to an internal page ie:
     *   * on the same domain
     *   * no HTTP redirect
     *   * id rewrite
     * @param string $targetPage - target page id or an URL
     * @param string $redirectSource the source of the redirect
     * @throws Exception
     */
    private
    function IdRedirect($targetPage, $redirectSource = 'Not Known')
    {


        //If the user have right to see the target page
        if ($_SERVER['REMOTE_USER']) {
            $perm = auth_quickaclcheck($targetPage);
        } else {
            $perm = auth_aclcheck($targetPage, '', null);
        }
        if ($perm <= AUTH_NONE) {
            return;
        }

        // Change the id
        global $ID;
        global $INFO;
        $sourceId = $ID;
        $ID = $targetPage;
        // Change the info id for the sidebar
        $INFO['id'] = $targetPage;

        // Redirection
        $this->logRedirection($sourceId, $targetPage, $redirectSource, self::REDIRECT_ID);


    }

    /**
     * An HTTP Redirect to an internal page, no external resources
     * @param string $target - a dokuwiki id or an url
     * @param $targetOrigin - the origin of the target (the algorithm used to get the target origin)
     * @param bool $permanent - true for a permanent redirection otherwise false
     */
    private
    function httpRedirect($target, $targetOrigin, $permanent = false)
    {

        global $ID;

        // No message can be shown because this is an external URL

        // Log the redirections
        $this->logRedirection($ID, $target, $targetOrigin,self::REDIRECT_HTTP);

        // Notify
        action_plugin_webcomponent_urlmessage::notify($ID, $targetOrigin);

        // An url ?
        if (PluginStatic::isValidURL($target)) {

            $targetUrl = $target;

        } else {

            // Explode the page ID and the anchor (#)
            $link = explode('#', $target, 2);

            // Query String to pass the message
            $urlParams = array(
                action_plugin_webcomponent_urlmessage::ORIGIN_PAGE => $ID,
                action_plugin_webcomponent_urlmessage::ORIGIN_TYPE => $targetOrigin
            );

            $targetUrl = wl($link[0], $urlParams, true, '&');
            if ($link[1]) {
                $targetUrl .= '#' . rawurlencode($link[1]);
            }

        }

        /*
         * The send_redirect function below send a 302
         */
        if ($permanent) {
            // Not sure
            header('HTTP/1.1 301 Moved Permanently');
        }
        send_redirect($targetUrl);


        if (defined('DOKU_UNITTEST')) return; // no exits during unit tests
        exit();

    }

    /**
     * @param $id
     * @return array
     */
    private
    function getBestPage($id)
    {

        // The return parameters
        $bestPageId = null;
        $scorePageName = null;

        // Get Score from a page
        $pageName = noNS($id);
        $pagesWithSameName = ft_pageLookup($pageName);
        if (count($pagesWithSameName) > 0) {

            // Search same namespace in the page found than in the Id page asked.
            $bestNbWordFound = 0;


            $wordsInPageSourceId = explode(':', $id);
            foreach ($pagesWithSameName as $targetPageId => $title) {

                // Nb of word found in the target page id
                // that are in the source page id
                $nbWordFound = 0;
                foreach ($wordsInPageSourceId as $word) {
                    $nbWordFound = $nbWordFound + substr_count($targetPageId, $word);
                }

                if ($bestPageId == null) {

                    $bestNbWordFound = $nbWordFound;
                    $bestPageId = $targetPageId;

                } else {

                    if ($nbWordFound >= $bestNbWordFound && strlen($bestPageId) > strlen($targetPageId)) {

                        $bestNbWordFound = $nbWordFound;
                        $bestPageId = $targetPageId;

                    }

                }

            }
            $scorePageName = $this->getConf('WeightFactorForSamePageName') + ($bestNbWordFound - 1) * $this->getConf('WeightFactorForSameNamespace');
            return array(
                'id' => $bestPageId,
                'score' => $scorePageName);
        }
        return array(
            'id' => $bestPageId,
            'score' => $scorePageName
        );

    }


    /**
     * Redirect to the search engine
     */
    private
    function redirectToSearchEngine()
    {

        global $ID;

        $replacementPart = array(':', '_', '-');
        $query = str_replace($replacementPart, ' ', $ID);

        $urlParams = array(
            "do" => "search",
            "q" => $query
        );

        $url = wl($ID, $urlParams, true, '&');

        $this->httpRedirect($url, self::TARGET_ORIGIN_SEARCH_ENGINE);

    }


    /**
     *
     *   * For a conf file, it will update the Redirection Action Data as Referrer, Count Of Redirection, Redirection Date
     *   * For a SQlite database, it will add a row into the log
     *
     * @param string $sourcePageId
     * @param $targetPageId
     * @param $algorithmic
     * @param $method - http or rewrite
     */
    function logRedirection($sourcePageId, $targetPageId, $algorithmic, $method)
    {

        $row = array(
            "TIMESTAMP" => date("c"),
            "SOURCE" => $sourcePageId,
            "TARGET" => $targetPageId,
            "REFERRER" => $_SERVER['HTTP_REFERER'],
            "TYPE" => $algorithmic,
            "METHOD" => $method
        );
        $res = $this->sqlite->storeEntry('redirections_log', $row);

        if (!$res) {
            PluginStatic::msg("An error occurred");
        }

    }

    /**
     * This function check if there is a redirection declared
     * in the redirection table
     * @return bool - true if a rewrite or redirection occurs
     * @throws Exception
     */
    private function processingPageRules()
    {
        global $ID;

        $calculatedTarget = null;
        $ruleMatcher = null; // Used in a warning message if the target page does not exist
        // Known redirection in the table
        // Get the page from redirection data
        $rules = $this->pageRules->getRules();
        foreach ($rules as $rule) {

            $ruleMatcher = strtolower($rule[PageRules::MATCHER_NAME]);
            $ruleTarget = $rule[PageRules::TARGET_NAME];

            // Glob to Rexgexp
            $regexpPattern = '/' . str_replace("*", "(.*)", $ruleMatcher) . '/';

            // Match ?
            // https://www.php.net/manual/en/function.preg-match.php
            if (preg_match($regexpPattern, $ID, $matches)) {
                $calculatedTarget = $ruleTarget;
                foreach ($matches as $key => $match) {
                    if ($key == 0){
                        continue;
                    } else {
                        $calculatedTarget = str_replace('$'.$key, $match, $calculatedTarget);
                    }
                }
                break;
            }
        }

        if ($calculatedTarget==null){
            return false;
        }

        // If this is an external redirect (other domain)
        if (PluginStatic::isValidURL($calculatedTarget)) {

            $this->httpRedirect($calculatedTarget, self::TARGET_ORIGIN_PAGE_RULES, true);
            return true;

        }

        // If the page exist
        if (page_exists($calculatedTarget)) {

            // This is DokuWiki Id and should always be lowercase
            // The page rule may have change that
            $calculatedTarget = strtolower($calculatedTarget);
            $this->IdRedirect($calculatedTarget, self::TARGET_ORIGIN_PAGE_RULES);
            return true;

        } else {

            PluginStatic::msg("The calculated target page ($calculatedTarget) (for the non-existent page `$ID` with the matcher `$ruleMatcher`) does not exist",PluginStatic::LVL_MSG_ERROR);
            return false;

        }

    }

    /**
     * Test a redirect to a namespace start page
     * (ie the start page has the name of its parent, not start as in the conf['start'] parameters )
     * It must happens when a page exists within another namespace that is completely not related to the old one.
     *
     */
    public function test_internalRedirectToNamespaceStartPageWithParentName()
    {

        global $conf;
        $pathSeparator = ':';
        $conf['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['ActionReaderFirst'] = action_plugin_webcomponent_urlmanager::GO_TO_BEST_PAGE_NAME;
        $conf['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['WeightFactorForSamePageName'] = 4;
        $conf['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['WeightFactorForStartPage'] = 3;
        $conf['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['WeightFactorForSameNamespace'] = 5;
        $conf['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['WordsSeparator'] = $pathSeparator;
        $conf['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['ShowPageNameIsNotUnique'] = 1;


        // Set of 3 pages, when a page has an homonym (same page name) but within another completly differents path (the name of the path have nothing in common)
        // the 404 manager must redirect to the start page of the namespace.
        $subName1 = "name1";
        $subName2 = "name2";
        $name = 'redirect_to_namespace_start_page';
        $sourceId = $subName1 . $pathSeparator . $name;
        $goodTarget = $subName1 . $pathSeparator . $conf['start']; // score of 8: same namespace 5 + start page 3
        $badTarget = $subName2 . $pathSeparator . $name; // score of 4: same page name score of 4


        $redirectManager = PageRules::get();
        if ($redirectManager->isRedirectionPresent($sourceId)) {
            $redirectManager->deleteRedirection($sourceId);
        }


        // Create the target Pages and add the pages to the index, otherwise, they will not be find by the ft_lookup
        saveWikiText($badTarget, 'Page with the same name', 'but without any common name (namespace) in the path');
        idx_addPage($badTarget);
        saveWikiText($goodTarget, 'The start page that has the same name that it\'s parent', 'Test initialization');
        idx_addPage($goodTarget);

        // Read only otherwise, you go in edit mode
        global $AUTH_ACL;
        $aclReadOnlyFile = constant_parameters::$DIR_RESOURCES . '/acl.auth.read_only.php';
        $AUTH_ACL = file($aclReadOnlyFile);


        $request = new TestRequest();
        $response = $request->get(array('id' => $sourceId), '/doku.php');

        $locationHeader = $response->getHeader("Location");
        $components = parse_url($locationHeader);
        parse_str($components['query'], $queryKeys);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertNull($queryKeys['do'], "The page is only shown");
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($goodTarget, $queryKeys['id'], "The Id is the target page");

        /**
         * The rewrite data are passed now via session and no more via URL
         * It's then not possible to test them automatically
         */


    }

}
