<?php
/**
 *
 * Test the {@link action_plugin_webcomponent_urlmessage}
 *
 * @group plugin_webcomponent
 * @group plugins
 *
 */
require_once(__DIR__ . '/../action/urlmessage.php');

class plugin_webcomponent_url_message_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginStatic::$PLUGIN_BASE_NAME;
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
            action_plugin_webcomponent_urlmessage::ORIGIN_PAGE => 'page that does not exist',
            action_plugin_webcomponent_urlmessage::ORIGIN_TYPE => action_plugin_webcomponent_urlmanager::TARGET_ORIGIN_BEST_PAGE_NAME
        ), '/doku.php');


        $href = $response->queryHTML('.' . action_plugin_webcomponent_urlmessage::REDIRECT_MANAGER_BOX_CLASS)->count();
        $this->assertEquals(1, $href, "A box message should be present");

        $href = $response->queryHTML('.managerreference > a' )->attr('href');
        $this->assertEquals("https://combostrap.com/url/manager", $href, "Good link");

    }


}
