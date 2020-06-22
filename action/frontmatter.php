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
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'description', array());
    }

    /**
     * Add a meta-data description
     */
    function description(&$event, $param)
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

        /**
         * Canonical
         *
         * FYI: The creation of the link was extracetd from
         * {@link wl()} that call {@link idfilter()} that performs just a replacement
         */
        $canonical = p_get_metadata($ID, syntax_plugin_webcomponent_frontmatter::CANONICAL_PROPERTY);
        $canonicalHref = getBaseURL(true) . strtr($canonical, ':', '/');
        // Search the key
        $canonicalKey = "";
        $canonicalRelArray = array("rel" => "canonical", "href" => $canonicalHref);
        foreach ($event->data['link'] as $key => $link) {
            if ($link["rel"]=="canonical"){
                $canonicalKey = $key;
            }
        }
        if ($canonicalKey!=""){
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
        $canonicalOgArray = array("property" => $canonicalPropertyKey, "content" => $canonicalHref);
        foreach ($event->data['meta'] as $key => $meta) {
            if ($meta["property"]== $canonicalPropertyKey){
                $canonicalOgKeyKey = $key;
            }
        }
        if ($canonicalOgKeyKey!=""){
            // Update
            $event->data['meta'][$canonicalOgKeyKey] = $canonicalOgArray;
        } else {
            // Add
            $event->data['meta'][] = $canonicalOgArray;
        }

    }
}