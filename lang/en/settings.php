<?php

use ComboStrap\MetadataUtility;
use ComboStrap\PluginUtility;
use ComboStrap\UrlManagerBestEndPage;

require_once(__DIR__ . '/../../class/PluginUtility.php');
require_once(__DIR__ . '/../../class/UrlManagerBestEndPage.php');
require_once(__DIR__ . '/../../class/MetadataUtility.php');

/**
 * @var array
 */
$lang[syntax_plugin_combo_related::MAX_LINKS_CONF] = PluginUtility::getUrl("related", "Related Component").' - The maximum of related links shown';
$lang[syntax_plugin_combo_related::EXTRA_PATTERN_CONF] = PluginUtility::getUrl("related", "Related Component").' - Another pattern';

/**
 * Disqus
 */
$lang[syntax_plugin_combo_disqus::CONF_DEFAULT_ATTRIBUTES] = PluginUtility::getUrl("disqus", "Disqus").' - The disqus forum short name (ie the disqus website identifier)';


/**
 * Url Manager
 */
$lang['ActionReaderFirst'] = action_plugin_combo_urlmanager::NAME.' - First redirection action for a reader';
$lang['ActionReaderSecond'] = action_plugin_combo_urlmanager::NAME.' - Second redirection action for a reader if the first action don\'t success.';
$lang['ActionReaderThird'] = action_plugin_combo_urlmanager::NAME.' - Third redirection action for a reader if the second action don\'t success.';
$lang['GoToEditMode'] = action_plugin_combo_urlmanager::NAME.' - Switch directly in the edit mode for a writer ?';

$lang['ShowPageNameIsNotUnique'] = action_plugin_combo_urlmanager::NAME.' - When redirected to the edit mode, show a message when the page name is not unique';
$lang['ShowMessageClassic'] = action_plugin_combo_urlmanager::NAME.' - Show classic message when a action is performed ?';
$lang['WeightFactorForSamePageName'] = action_plugin_combo_urlmanager::NAME.' - Weight factor for same page name to calculate the score for the best page.';
$lang['WeightFactorForStartPage'] = action_plugin_combo_urlmanager::NAME.' - Weight factor for same start page to calculate the score for the best page.';
$lang['WeightFactorForSameNamespace'] = action_plugin_combo_urlmanager::NAME.' - Weight factor for same namespace to calculate the score for the best page.';


$lang[action_plugin_combo_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF]= PluginUtility::getUrl("automatic:canonical",action_plugin_combo_urlmanager::NAME.' - Automatic Canonical').' - The number of last part of a DokuWiki Id to create a '. PluginUtility::getUrl("canonical", "canonical").' (0 to disable)';

$lang[UrlManagerBestEndPage::CONF_MINIMAL_SCORE_FOR_REDIRECT]=PluginUtility::getUrl("best:end:page:name", action_plugin_combo_urlmanager::NAME.' - Best End Page Name').' - The number of last part of a DokuWiki Id to perform a '. PluginUtility::getUrl("id:redirect", "ID redirect").' (0 to disable)';


$lang[syntax_plugin_combo_icon::CONF_ICONS_MEDIA_NAMESPACE]=PluginUtility::getUrl("icon#configuration", "UI Icon Component").' - The media namespace where the downloaded icons will be search and saved';

/**
 * Css Optimization
 */
$lang[action_plugin_combo_css::CONF_ENABLE_MINIMAL_FRONTEND_STYLESHEET]= PluginUtility::getUrl("css:optimization", "Css Optimization").' - If enabled, the DokuWiki Stylesheet for a public user will be minimized';
$lang[action_plugin_combo_css::CONF_DISABLE_DOKUWIKI_STYLESHEET]= PluginUtility::getUrl("css:optimization", "Css Optimization").' - If disabled, the DokuWiki Stylesheet will not be loaded for a public user';

/**
 * Metdataviewer
 */
$lang[MetadataUtility::CONF_METADATA_DEFAULT_ATTRIBUTES]= PluginUtility::getUrl("metadata:viewer","Metadata Viewer").' - The default attributes of the metadata component';
$lang[MetadataUtility::CONF_ENABLE_WHEN_EDITING]= PluginUtility::getUrl("metadata:viewer","Metadata Viewer").' - Shows the metadata box when editing a page';

?>
