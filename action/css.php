<?php


use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();

/**
 * Class action_plugin_combo_css
 * Delete Backend CSS for front-end
 *
 * Bug:
 *   * https://gerardnico.com/web/browser/lighthouse - no interwiki
 *
 * A call to /lib/exe/css.php?t=template&tseed=time()
 *
 *    * t is the template
 *
 *    * tseed is md5 of modified time of the below config file set at {@link tpl_metaheaders()}
 *
 *        * conf/dokuwiki.php
 *        * conf/local.php
 *        * conf/local.protected.php
 *        * conf/tpl/strap/style.ini
 *
 */
class action_plugin_combo_css extends DokuWiki_Action_Plugin
{

    /**
     * Front end or backend
     */
    const END_KEY = 'end';
    const VALUE_FRONT = 'front';
    const VALUE_BACK = 'back';

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     *
     * To fire this event
     *   * Ctrl+Shift+R to disable browser cache
     *
     */
    public function register(Doku_Event_Handler $controller)
    {

        $urlPropertyValue = PluginUtility::getPropertyValue(self::END_KEY, self::VALUE_BACK);
        if (PluginUtility::getRequestScript() == "css.php" && $urlPropertyValue == self::VALUE_FRONT) {
            /**
             * The process follows the following steps:
             *     * With CSS_STYLES_INCLUDED, you choose the file that you want
             *     * then with CSS_CACHE_USE, you can change the cache key name
             */
            $controller->register_hook('CSS_STYLES_INCLUDED', 'BEFORE', $this, 'handle_css_styles');
            $controller->register_hook('CSS_CACHE_USE', 'BEFORE', $this, 'handle_css_cache');
        }


        /**
         * Add a property to the URL to create two CSS file:
         *   * one public
         *   * one private (logged in)
         */
        if (PluginUtility::getRequestScript() == "doku.php") {
            $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_css_metaheader');
        }

    }

    /**
     * @param Doku_Event $event
     * @param $param
     *
     * Add query parameter to the CSS header call. ie
     * <link rel="preload" href="/lib/exe/css.php?t=template&tseed=8e31090353c8fcf80aa6ff0ea9bf3746" as="style">
     * to indicate if the page that calls the css is from a user that is logged in or not:
     *   * public vs private
     *   * ie frontend vs backend
     */
    public function handle_css_metaheader(Doku_Event &$event, $param)
    {
        if (empty($_SERVER['REMOTE_USER'])) {
            $links = &$event->data['link'];
            foreach ($links as &$link) {
                $pos = strpos($link['href'], 'css.php');
                if ($pos !== false) {
                    $link['href'] .= '&' . self::END_KEY . '=' . self::VALUE_FRONT . '';
                }
            }
        }

    }

    /**
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     *
     * Change the key of the cache.
     *
     * The default key can be seen in the {@link css_out()} function
     * when a new cache is created (ie new cache(key,ext)
     *
     * This is only called when this is a front call, see {@link register()}
     *
     * @see <a href="https://github.com/i-net-software/dokuwiki-plugin-lightweightcss/blob/master/action.php#L122">Credits</a>
     */
    public function handle_css_cache(Doku_Event &$event, $param)
    {
        /**
         * Trick to be able to test
         * The {@link register()} function is called only once when a test
         * is started
         * we change the value to see if the payload is less big
         */
        $propertyValue = PluginUtility::getPropertyValue(self::END_KEY);
        if ($propertyValue == self::VALUE_FRONT) {
            $event->data->key .= self::VALUE_FRONT;
            $event->data->cache = getCacheName($event->data->key, $event->data->ext);
        }

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
        /**
         * Trick to be able to test
         * The {@link register()} function is called only once when a test
         * is started
         * we change the value to see if the payload is less big
         */
        $propertyValue = PluginUtility::getPropertyValue(self::END_KEY);
        if ($propertyValue == self::VALUE_BACK) {
            return;
        }

        /**
         * There is one call by:
         *   * mediatype (ie scree, all, print, speech)
         *   * and one call for the dokuwiki default
         */
        $excludedPlugins = array(
            "acl",
            "authplain",
            "changes",
            "config",
            "extension",
            "info",
            "move",
            "popularity",
            "revert",
            "safefnrecode",
            "searchindex",
            "sqlite",
            "upgrade",
            "usermanager"
        );

        switch ($event->data['mediatype']) {

            case 'print':
            case 'screen':
            case 'all':
                $filteredDataFiles = array();
                $files = $event->data['files'];
                foreach ($files as $file => $fileDirectory) {
                    // lib styles
                    if (strpos($fileDirectory, 'lib/styles')) {
                        // Geshi (syntax highlighting) and basic style of doku, we keep.
                        $filteredDataFiles[$file] = $fileDirectory;
                        continue;
                    }
                    // No Css from lib scripts
                    // Jquery is here
                    if (strpos($fileDirectory, 'lib/scripts')) {
                        continue;
                    }
                    // Excluded
                    $isExcluded = false;
                    foreach ($excludedPlugins as $plugin) {
                        if (strpos($file, 'lib/plugins/' . $plugin)) {
                            $isExcluded = true;
                            break;
                        }
                    }
                    if (!$isExcluded) {
                        $filteredDataFiles[$file] = $fileDirectory;
                    }
                }

                $event->data['files'] = $filteredDataFiles;

                break;

            case 'speech':
                $event->preventDefault();
                break;
            case 'DW_DEFAULT':
                // Interwiki styles are here, we keep (in the lib/css.php file)
                break;

        }
    }
}


