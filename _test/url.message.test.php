<?php
/**
 *
 * Test the {@link action_plugin_combo_urlmessage}
 *
 * @group plugin_combo
 * @group plugins
 *
 */

use ComboStrap\PagesIndex;
use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../action/urlmessage.php');

class plugin_combo_url_message_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        //$this->pluginsEnabled[] = 'sqlite';
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }


    /**
     * In a HTTP redirect message, the parameters are passed
     * via query parameters
     * because the session mechanism does not work every time
     */
    public function test_http_redirect_message()
    {

        // Create the target Pages and add the pages to the index, otherwise, they will not be find by the ft_lookup
        $existingPage = "an:existing:page";
        saveWikiText($existingPage, 'A page that exists', 'Test initialization');
        idx_addPage($existingPage);

        $request = new TestRequest();
        $response = $request->get(array(
            'id' => $existingPage,
            action_plugin_combo_urlmessage::ORIGIN_PAGE => 'page that does not exist',
            action_plugin_combo_urlmessage::ORIGIN_TYPE => action_plugin_combo_urlmanager::TARGET_ORIGIN_BEST_PAGE_NAME
        ), '/doku.php');


        $href = $response->queryHTML('.' . action_plugin_combo_urlmessage::REDIRECT_MANAGER_BOX_CLASS)->count();
        $this->assertEquals(1, $href, "A box message should be present");

        $href = $response->queryHTML('.managerreference > a' )->attr('href');
        $this->assertEquals("https://combostrap.com/url/manager", $href, "Good link");

    }

    /**
     * The same page message shows the pages with the same name
     * It should not show the redirected page in the lost
     *
     */
    public function test_same_page_message()
    {

        // Create the target Pages and add the pages to the index, otherwise, they will not be find by the ft_lookup
        $name = "page_with_same_name";
        $targetPage = "an:existing:{$name}";
        saveWikiText($targetPage, 'A page that exists', 'Test initialization');
        idx_addPage($targetPage);

        $possibleTargetPage = "an:possible:target:{$name}";
        saveWikiText($possibleTargetPage, 'A possible page that will be shown', 'Test initialization');
        idx_addPage($possibleTargetPage);

        $sourceId= "another:page:{$name}";
        $pageWithSameName = PagesIndex::pagesWithSameName(noNs($sourceId), $targetPage);
        $this->assertEquals(1, sizeof($pageWithSameName),"There is no page with the same name");

    }


}
