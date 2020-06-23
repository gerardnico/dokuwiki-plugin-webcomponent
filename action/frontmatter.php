<?php
/**
 * Take the metadata description
 * https://github.com/lupo49/plugin-description/blob/master/action.php
 *
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . 'action.php');

define('KEYWORD_SOURCE_ABSTRACT', 'abstract');
define('KEYWORD_SOURCE_GLOBAL', 'global');
define('KEYWORD_SOURCE_SYNTAX', 'syntax');

class action_plugin_webcomponent_frontmatter extends DokuWiki_Action_Plugin
{

    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'meta_modification', array());
    }

    /**
     * Add a meta-data description
     */
    function meta_modification(&$event, $param)
    {

        if (empty($event->data) || empty($event->data['meta'])) return;

        global $ID;

        /**
         * Description
         * https://www.dokuwiki.org/devel:metadata
         */
        $description = p_get_metadata($ID, 'description');
        if (empty($description)) return;

        // Get the abstract and suppress the carriage return
        $abstract = str_replace("\n", " ", $description['abstract']);
        if (empty($abstract)) return;

        // Suppress the title
        $title = p_get_metadata($ID, 'title');
        $meta = str_replace($title, "", $abstract);
        // Suppress the star, the tab, About
        $meta = preg_replace('/(\*|\t|About)/im', "", $meta);
        // Suppress all double space and trim
        $meta = trim(preg_replace('/  /m', " ", $meta));

        // Add it to the meta
        $event->data['meta'][] = array("name" => "description", "content" => $meta);

        $this->metaCanonicalProcessing($event);

    }

    /**
     * Dokuwiki has already a canonical methodology
     * https://www.dokuwiki.org/canonical
     *
     * @param $event
     */
    private function metaCanonicalProcessing($event)
    {
        global $ID;
        global $conf;


        /**
         * When we will support
         */
        $supportCanonicalRender = false;
        if ($supportCanonicalRender) {

            /**
             * Canonical from meta
             *
             * FYI: The creation of the link was extracted from
             * {@link wl()} that call {@link idfilter()} that performs just a replacement
             * Calling the wl function will not work because
             * {@link wl()} use the constant DOKU_URL that is set before any test via getBaseURL(true)
             */
            $canonical = p_get_metadata($ID, syntax_plugin_webcomponent_frontmatter::CANONICAL_PROPERTY);
            $canonicalUrl = getBaseURL(true) . strtr($canonical, ':', '/');

        } else {

            /**
             * Methodology taken from {@link tpl_metaheaders()}
             */
            $canonicalUrl = wl($ID, '', true, '&');
            if ($ID == $conf['start']) {
                $canonicalUrl = DOKU_URL;
            }

        }

        // Search the key
        $canonicalKey = "";
        $canonicalRelArray = array("rel" => "canonical", "href" => $canonicalUrl);
        foreach ($event->data['link'] as $key => $link) {
            if ($link["rel"] == "canonical") {
                $canonicalKey = $key;
            }
        }
        if ($canonicalKey != "") {
            // Update
            $event->data['link'][$canonicalKey] = $canonicalRelArray;
        } else {
            // Add
            $event->data['link'][] = $canonicalRelArray;
        }

        /**
         * Add the Og canonical meta
         * https://developers.facebook.com/docs/sharing/webmasters/getting-started/versioned-link/
         */
        $canonicalOgKeyKey = "";
        $canonicalPropertyKey = "og:url";
        $canonicalOgArray = array("property" => $canonicalPropertyKey, "content" => $canonicalUrl);
        foreach ($event->data['meta'] as $key => $meta) {
            if ($meta["property"] == $canonicalPropertyKey) {
                $canonicalOgKeyKey = $key;
            }
        }
        if ($canonicalOgKeyKey != "") {
            // Update
            $event->data['meta'][$canonicalOgKeyKey] = $canonicalOgArray;
        } else {
            // Add
            $event->data['meta'][] = $canonicalOgArray;
        }

    }
}