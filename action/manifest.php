<?php
/**
 * DokuWiki Plugin Manifest Action
 *
 */

if (!defined('DOKU_INC')) die();

/**
 * Header
 */
class action_plugin_webcomponent_manifest extends DokuWiki_Action_Plugin {

    /**
     * Registers our handler for the MANIFEST_SEND event
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('MANIFEST_SEND', 'BEFORE', $this, 'handle_manifest');

    }

    /**
     *      *
     *
     * @param Doku_Event $event
     * @param            $param
     */
    public function handle_manifest(Doku_Event &$event, $param) {

            $event = $event;
    }

}

