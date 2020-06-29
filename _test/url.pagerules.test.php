<?php
/**
 * This tests are testing the function that uses the redirect tables
 * that describes rewrite rules
 *
 * ie the {@link PageRules} class
 *
 * @group plugin_webcomponent
 * @group plugins
 *
 */
require_once(__DIR__ . '/../class/PageRules.php');
require_once(__DIR__ . '/../action/urlmanager.php');
class plugin_webcomponent_url_rewrite_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginStatic::$PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'sqlite';
        parent::setUp();
    }


    /**
     * Test a redirect to an external Web Site
     * without pattern
     */
    public function test_externalRedirect_without_pattern()
    {

        $pageRules = (new PageRules(PluginStatic::getSqlite()));
        $pageRules->deleteAll();

        $pattern = "ToBeRedirected";
        $externalURL = 'http://gerardnico.com';

        /**
         * Test the database manipulation
         */
        $pageRuleId = $pageRules->addRule($pattern, $externalURL,0);

        $patternExist = $pageRules->patternExists($pattern);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(true, $patternExist,"The redirection is present");
        $ruleExists = $pageRules->ruleExists($pageRuleId);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(true, $ruleExists,"The rule is present");

        $rule = $pageRules->getRule($pageRuleId);
        /** @noinspection PhpUndefinedMethodInspection */
        $actualTarget = $rule['TARGET'];
        $this->assertEquals($externalURL, $actualTarget,"The target is the good one");

        /**
         * Test the URL navigation
         */
        // Read only otherwise you are redirected to the Edit Mode
        global $AUTH_ACL;
        $aclReadOnlyFile = PluginStatic::$DIR_RESOURCES . '/acl.auth.read_only.php';
        $AUTH_ACL = file($aclReadOnlyFile);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pattern), '/doku.php');

        $locationHeader = $response->getHeader("Location");

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals("Location: " . $externalURL, $locationHeader, "The page was redirected");

    }


    /**
     * Test a redirect to an internal page that exist
     *
     * Test a rewrite rule that redirect a page to another page
     * (Using a canonical should be the right way but yeah)
     */
    public function test_internalRedirectToExistingPage()
    {

        $redirectManager = new PageRules(PluginStatic::getSqlite());

        // in the $ID value, the first : is suppressed
        $sourcePageId = "an:page:that:does:not:exist";
        saveWikiText($sourcePageId, "", 'Without content the page is deleted');
        $targetPage = "an:existing:page";
        saveWikiText($targetPage, 'EXPLICIT_REDIRECT_PAGE_TARGET', 'Test initialization');


        // Clean test state
        if ($redirectManager->isPageRulePresent($sourcePageId)) {
            $redirectManager->deleteRule($sourcePageId);
        }
        $redirectManager->addRule($sourcePageId, $targetPage);


        // Set to search engine first but because of order of precedence, this should not happens
        $conf ['plugin'][PluginStatic::$PLUGIN_BASE_NAME]['ActionReaderFirst'] = action_plugin_webcomponent_urlmanager::GO_TO_SEARCH_ENGINE;

        // Read only otherwise, you go in edit mode
        global $AUTH_ACL;
        $aclReadOnlyFile = PluginStatic::$DIR_RESOURCES . '/acl.auth.read_only.php';
        $AUTH_ACL = file($aclReadOnlyFile);


        $request = new TestRequest();
        $response = $request->get(array('id' => $sourcePageId), '/doku.php');

        // Check the canonical value
        $canonical = $response->queryHTML('link[rel="canonical"]')->attr('href');
        $canonicalPageId = UrlCanonical::toDokuWikiId($canonical);
        $this->assertEquals($targetPage, $canonicalPageId, "The page was redirected");

        // No message for rewrite

    }









    /**
     * Test basic redirections operations
     *
     */
    public function testRedirectionsOperations()
    {
        $targetPage = 'testRedirectionsOperations:test';
        saveWikiText($targetPage, 'Test ', 'but without any common name (namespace) in the path');
        idx_addPage($targetPage);

        $redirectManager = new PageRules(PluginStatic::getSqlite());


        $redirectManager->deleteAll();
        $count = $redirectManager->count();
        $this->assertEquals(0, $count, "The number of redirection is zero");
        $sourcePageId = "source";
        $redirectManager->addRule($sourcePageId, $targetPage);
        $count = $redirectManager->count();
        $this->assertEquals(1, $count, "The number of redirection is one");
        $bool = $redirectManager->isPageRulePresent($sourcePageId);
        $this->assertEquals(true, $bool, "The redirection is present");


    }



    /**
     * Test if an expression is a regular expression pattern
     */
    public function test_expressionIsRegular()
    {

        // Not an expression
        $inputExpression = "Hallo";
        $isRegularExpression = PageRules::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(0,$isRegularExpression,"The term (".$inputExpression.") is not a regular expression");

        // A basic expression
        $inputExpression = "/Hallo/";
        $isRegularExpression = PageRules::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(true,$isRegularExpression,"The term (".$inputExpression.") is a regular expression");

        // A complicated expression
        $inputExpression = "/(/path1/path2/)(.*)/";
        $isRegularExpression = PageRules::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(true,$isRegularExpression,"The term (" . $inputExpression . ") is a regular expression");

    }

}
