<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;


require_once(__DIR__ . '/../class/TestUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');
/**
 * Test the related plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class dokuwiki_plugin_combo_related_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;

        // Config changes have only effect in function setUpBeforeClass()
        global $conf;

        parent::setUp();


        // Be sure to run as a super user
        $_SERVER['REMOTE_USER'] = $conf['superuser'];

        //
        $conf ['plugin'][PluginUtility::PLUGIN_BASE_NAME][syntax_plugin_combo_related::EXTRA_PATTERN_CONF] = self::EXTRA_PATTERN_VALUE;


    }


    // Namespace where all test page will be added
    const TEST_PAGE_NAMESPACE = 'test:';
    const REFERENT_PAGE_ID = self::TEST_PAGE_NAMESPACE . 'referent';
    public static $referrers = array();

    const REFERRERS_COUNT = 4;

    // for the extra pattern test
    const EXTRA_PATTERN_VALUE = '{{backlinks>.}}';
    public static $extraPatternPage;

    // The value of the REFERRERS_ID_TOP is the referrers that will
    // got two backlinks and should therefore be on the top in a list of related page
    // This number should be less that REFERRERS_COUNT
    const REFERRERS_ID_TOP = 3;

    // Create the pages
    // and configure
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        // Config changes in function setUpBeforeClass() have no effect set setup

        // Create the referent page
        $referentPageId = self::REFERENT_PAGE_ID;
        TestUtility::addPage($referentPageId,
            '======  A referent page ====== ' . DW_LF . DW_LF .
            '=====  Articles Related ====== ' . DW_LF .
            '<' . syntax_plugin_combo_related::getElementName() . '>');


        // Create the referrers page with a link to the referent page
        for ($i = 1; $i <= self::REFERRERS_COUNT; $i++) {
            self::createReferrerPage($referentPageId);
        }

        // Create a referrer page that links to the first referrers
        // It should then be the first one in the related list
        if (self::REFERRERS_ID_TOP > self::REFERRERS_COUNT) {
            throw new Exception("The value of the REFERRERS_ID_TOP (" . self::REFERRERS_ID_TOP . ") should be less than the value of REFERRERS_COUNT (" . self::REFERRERS_COUNT . ")");
        }
        self::createReferrerPage(self::$referrers[self::REFERRERS_ID_TOP]);

        // Extra Pattern Page
        $PageId = 'extraPatternTest';
        self::$extraPatternPage = self::TEST_PAGE_NAMESPACE . $PageId;
        TestUtility::addPage(self::$extraPatternPage,
            '======  ' . $PageId . ' ======' . DW_LF . DW_LF .
            self::EXTRA_PATTERN_VALUE . DW_LF . DW_LF .
            '<' . syntax_plugin_combo_related::getElementName() . '>');
        self::createReferrerPage(self::$extraPatternPage);



        dbglog("\nTest Plugin" . PluginUtility::PLUGIN_BASE_NAME .".".syntax_plugin_combo_related::getElementName() . ': Start Page was created at ' . wikiFN($startId));


    }

    /**
     * @param $referentPageId - The Full referrant page id
     * @return string
     */
    public static function createReferrerPage($referentPageId)
    {
        $referrerId = sizeof(self::$referrers) + 1;
        $referrerPageId = self::TEST_PAGE_NAMESPACE . 'referrer' . $referrerId;
        TestUtility::addPage($referrerPageId,
            '======   Referrer ' . $referrerId . ' to ' . $referentPageId . ' ======' . DW_LF . DW_LF .
            '  * [[' . $referentPageId . ']]');
        self::$referrers[] = $referrerPageId;
        return $referrerPageId;
    }





    /**
     * Test the {@link  ft_backlinks() backlinks function}
     */
    public function test_doku_backlinks()
    {

        $backlinks = ft_backlinks(self::REFERENT_PAGE_ID, $ignore_perms = true);
        $this->assertEquals(self::REFERRERS_COUNT, sizeof($backlinks), "The dokuwiki ft_baclinks function is working");

    }

    /**
     * Test the related features of the related function
     * default, max and order
     */
    public function test_BaseRelated()
    {
        // Without max
        $referentPageId = self::REFERENT_PAGE_ID;
        $relatedPlugin = new syntax_plugin_combo_related();
        // Without max, it will take the conf default (10)
        $referrers = $relatedPlugin->related($referentPageId);
        $this->assertEquals(self::REFERRERS_COUNT, sizeof($referrers));
        // The first one must be the one that had two backlinks
        $this->assertEquals(self::$referrers[self::REFERRERS_ID_TOP], $referrers[0][syntax_plugin_combo_related::RELATED_PAGE_ID_PROP]);

        // With a max via argument
        $max = 1;
        $referrers = $relatedPlugin->related($referentPageId, $max);
        $expected = $max + 1; // +1 for the more page
        $this->assertEquals($expected, sizeof($referrers));

        // With a max via the conf
        global $conf;
        $oldMaxLinksValue = $conf ['plugin'][PluginUtility::PLUGIN_BASE_NAME][syntax_plugin_combo_related::MAX_LINKS_CONF];
        $conf ['plugin'][PluginUtility::PLUGIN_BASE_NAME][syntax_plugin_combo_related::MAX_LINKS_CONF] = $max;
        $referrers = $relatedPlugin->related($referentPageId);
        $this->assertEquals($expected, sizeof($referrers));
        $conf ['plugin'][PluginUtility::PLUGIN_BASE_NAME][syntax_plugin_combo_related::MAX_LINKS_CONF] = $oldMaxLinksValue;

    }

    public function test_extraPattern()
    {

        $request = new TestRequest();
        $response = $request->get(array('id' => self::$extraPatternPage));

        $idElements = $response->queryHTML('#' . syntax_plugin_combo_related::getElementId());
        $length = $idElements->length;
        $this->assertEquals(2, $length,"There should be two links");

    }


}
