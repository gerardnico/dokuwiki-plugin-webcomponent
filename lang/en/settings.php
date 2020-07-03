<?php
require_once(__DIR__ . '/../../class/UrlManagerBestEndPage.php');

/**
 * @var array
 */
$lang[syntax_plugin_webcomponent_related::MAX_LINKS_CONF] = 'Related Component - The maximum of related links shown';
$lang[syntax_plugin_webcomponent_related::EXTRA_PATTERN_CONF] = 'Related Component - Another pattern';
// disqus
$lang[syntax_plugin_webcomponent_disqus::FORUM_SHORT_NAME] = 'Disqus - The disqus forum short name. (See <a href="https://help.disqus.com/en/articles/1717084-javascript-configuration-variables">doc</a>)';


/**
 * Url Manager
 */
$lang['ActionReaderFirst'] = action_plugin_webcomponent_urlmanager::NAME.' - First redirection action for a reader';
$lang['ActionReaderSecond'] = action_plugin_webcomponent_urlmanager::NAME.' - Second redirection action for a reader if the first action don\'t success.';
$lang['ActionReaderThird'] = action_plugin_webcomponent_urlmanager::NAME.' - Third redirection action for a reader if the second action don\'t success.';
$lang['GoToEditMode'] = action_plugin_webcomponent_urlmanager::NAME.' - Switch directly in the edit mode for a writer ?';

$lang['ShowPageNameIsNotUnique'] = action_plugin_webcomponent_urlmanager::NAME.' - When redirected to the edit mode, show a message when the page name is not unique';
$lang['ShowMessageClassic'] = action_plugin_webcomponent_urlmanager::NAME.' - Show classic message when a action is performed ?';
$lang['WeightFactorForSamePageName'] = action_plugin_webcomponent_urlmanager::NAME.' - Weight factor for same page name to calculate the score for the best page.';
$lang['WeightFactorForStartPage'] = action_plugin_webcomponent_urlmanager::NAME.' - Weight factor for same start page to calculate the score for the best page.';
$lang['WeightFactorForSameNamespace'] = action_plugin_webcomponent_urlmanager::NAME.' - Weight factor for same namespace to calculate the score for the best page.';


$lang[action_plugin_webcomponent_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF]='<a href="'.PluginStatic::$URL_BASE.'/automatic/canonical">'.action_plugin_webcomponent_urlmanager::NAME.' - Automatic Canonical</a> - The number of last part of a Dokuwiki Id to create a <a href="'.PluginStatic::$URL_BASE.'/canonical">canonical</a> (0 to disable)';

$lang[UrlManagerBestEndPage::CONF_MINIMAL_SCORE_FOR_REDIRECT]='<a href="'.PluginStatic::$URL_BASE.'/best/end/page/name">'.action_plugin_webcomponent_urlmanager::NAME.' - Best End Page Name</a> - The number of last part of a Dokuwiki Id to perform a <a href="'.PluginStatic::$URL_BASE.'/id/redirect">ID redirect</a> (0 to disable)';


$lang[syntax_plugin_webcomponent_icon::CONF_ICONS_MEDIA_NAMESPACE]='<a href="'.PluginStatic::$URL_BASE.'/icon#configuration">UI Icon Component</a> - The media namespace where the downloaded icons will be search and saved';

?>
