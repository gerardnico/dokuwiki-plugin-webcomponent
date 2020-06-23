<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the front matter component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_frontmatter_test extends DokuWikiTest
{


    public function setUp()
    {

        $this->pluginsEnabled[] = webcomponent::PLUGIN_NAME;
        global $conf;

        parent::setUp();

        // To get nice url
        // https://www.dokuwiki.org/config:userewrite
        $conf['userewrite']= 1;
        // https://www.dokuwiki.org/config:useslash
        $conf['useslash']= 1;

    }



    /**
     * Test the description
     */
    public function test_frontmatter_description()
    {

        $pageId = 'description_test';
        $description = "Go see my beautiful website";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "description":"'.$description.'"' . DOKU_LF
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Created');

        $descriptionMeta = p_get_metadata($pageId, 'description', METADATA_RENDER_UNLIMITED);
        self::assertEquals($description, $descriptionMeta['abstract']);

        $description = "Go see my super beautiful website";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "description":"'.$description.'"' . DOKU_LF
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Updated meta');
        $descriptionMeta = p_get_metadata($pageId, 'description', METADATA_RENDER_UNLIMITED);
        self::assertEquals($description, $descriptionMeta['abstract']);

        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');
        $metaDescription = $response->queryHTML('meta[name="description"]')->attr('content');
        $this->assertEquals($description, $metaDescription);
    }

    /**
     * Test the canonical
     * Actually it just add the og
     * When the rendering of the canonical value will be supported by
     * 404 manager, we can switch
     * TODO: move this to 404 manager ?
     */
    public function test_frontmatter_canonical()
    {

        $metaKey = syntax_plugin_webcomponent_frontmatter::CANONICAL_PROPERTY;
        $pageId = 'description:test';
        $canonicalValue = "javascript:variable";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "'.$metaKey.'":"'.$canonicalValue.'"' . DOKU_LF
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Created');

        $canonicalMeta = p_get_metadata($pageId, $metaKey, METADATA_RENDER_UNLIMITED);
        self::assertEquals($canonicalValue, $canonicalMeta);

        // It should never occur but yeah
        $canonicalValue = "js:variable";
        $text = DOKU_LF . '---json' . DOKU_LF
            . '{' . DOKU_LF
            . '   "'.$metaKey.'":"'.$canonicalValue.'"' . DOKU_LF
            . '}' .DOKU_LF
            . '---' .DOKU_LF
            . 'Content';
        saveWikiText($pageId, $text, 'Updated meta');
        $canonicalMeta = p_get_metadata($pageId, $metaKey, METADATA_RENDER_UNLIMITED);
        self::assertEquals($canonicalValue, $canonicalMeta);

        // Do we have the description in the meta
        $request = new TestRequest(); // initialize the request
        $response = $request->get(array('id' =>$pageId), '/doku.php');

        /**
         * The domain for the test is set in the variable {@link $default_server_vars}
         * see the property SERVER_NAME (in the file _test/bootstrap.php)
         */
        $domain = "http://wiki.example.com/";
        $dokuCanonicalValue = $pageId; // Actually
        $canonicalPath = strtr($dokuCanonicalValue, ":", "/");
        $baseDir = "./"; # There is no way to change this configuration before
        $expectedCanonicalValue = $domain . $baseDir . $canonicalPath;

        // Query
        $canonicalHrefLink = $response->queryHTML('link[rel="'.$metaKey.'"]')->attr('href');
        $this->assertEquals($expectedCanonicalValue, $canonicalHrefLink,"The link canonical meta should be good");
        // Facebook: https://developers.facebook.com/docs/sharing/webmasters/getting-started/versioned-link/
        $canonicalHrefMetaOg = $response->queryHTML('meta[property="og:url"]')->attr('content');
        $this->assertEquals($expectedCanonicalValue, $canonicalHrefMetaOg,"The meta canonical property should be good");

    }

    /**
     * From
     * https://www.dokuwiki.org/devel:unittesting
     */
    function metaGeneratorTest() {
        // make a request
        $request = new TestRequest();
        $response = $request->execute();

        // get the generator name from the meta tag.
        $generator = $response->queryHTML('meta[name="generator"]')->attr('content');

        // check the result
        $this->assertEquals('DokuWiki', $generator);
    }


}
