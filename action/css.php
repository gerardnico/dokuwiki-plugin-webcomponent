<?php


if (!defined('DOKU_INC')) die();

/**
 * Class action_plugin_webcomponent_css
 * Delete Backend CSS for front-end
 */
class action_plugin_webcomponent_css extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('CSS_STYLES_INCLUDED', 'BEFORE', $this, 'handle_css_styles');
        $controller->register_hook('CSS_CACHE_USE', 'BEFORE', $this, 'handle_use_cache');
    }


    /**
     * This function serves debugging purposes and has to be enabled in the register phase
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_use_cache(Doku_Event &$event, $param)
    {
        global $INPUT;

        // We need different keys for each style sheet.
        $event->data->key .= $INPUT->str('f', 'style');
        $event->data->cache = getCacheName($event->data->key, $event->data->ext);

    }

    /**
     * Finally, handle the JS script list. The script would be fit to do even more stuff / types
     * but handles only admin and default currently.
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_css_styles(Doku_Event &$event, $param)
    {

        switch ($event->data['mediatype']) {

            case 'print':
            case 'screen':
            case 'all':
                $excludedPlugins = array("acl", "authplain", "changes", "config", "extension", "info", "move", "popularity", "revert", "safefnrecode", "searchindex", "sqlite", "upgrade", "usermanager");
                $filteredDataFiles = array();
                $files = $event->data['files'];
                foreach ($files as $fileKey => $file) {
                    // No Css from lib styles
                    if (strpos($file, 'lib/styles')) {
                        continue;
                    }
                    // No Css from lib scripts
                    if (strpos($file, 'lib/scripts')) {
                        continue;
                    }
                    // Excluded
                    $isExcluded = false;
                    foreach ($excludedPlugins as $plugin) {
                        if (strpos($fileKey, 'lib/plugins/' . $plugin)) {
                            $isExcluded = true;
                            break;
                        }
                    }
                    if (!$isExcluded) {
                        $filteredDataFiles[$fileKey] = $file;
                    }
                }

                $event->data['files'] = $filteredDataFiles;

                break;

            case 'speech':
            case 'DW_DEFAULT':
                $event->preventDefault();
                break;
        }
    }
}


