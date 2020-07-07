<?php
/**
 * The config manager is parsing this fucking file because they want
 * to be able to use 60*60*24 ???? :(
 *
 * See {@link \dokuwiki\plugin\config\core\ConfigParser::parse()}
 *
 * Which means that only value can be given as:
 *   * key
 *   * and value
 * The test test_plugin_default in plugin.test.php is checking that
 *
 * What fuck up is fucked up.
 *
 * The solution:
 *   * The literal value is copied
 *   * A link to the constant is placed before
 */




$conf['UnitShortCutKey'] = 'u';

/**
 * Related UI components
 */
$conf['maxLinks'] = 10;
$conf['extra_pattern'] = '{{backlinks>.}}';

/**
 * Disqus
 */
$conf['forumShortName'] = '';

/**
 * ie {@link action_plugin_combo_urlmanager::GO_TO_BEST_END_PAGE_NAME}
 */
$conf['ActionReaderFirst']  = 'GoToBestEndPageName';

/**
 * ie {@link action_plugin_combo_urlmanager::GO_TO_BEST_PAGE_NAME}
 */
$conf['ActionReaderSecond'] = 'GoToBestPageName';
/**
 * ie {@link action_plugin_combo_urlmanager::GO_TO_SEARCH_ENGINE}
 */
$conf['ActionReaderThird']  = 'GoToSearchEngine';
$conf['GoToEditMode'] = 1;
$conf['ShowPageNameIsNotUnique'] = 1;
$conf['ShowMessageClassic'] = 1;
$conf['WeightFactorForSamePageName'] = 4;
$conf['WeightFactorForStartPage'] = 3;
$conf['WeightFactorForSameNamespace'] = 5;

/**
 * See {@link UrlManagerBestEndPage::CONF_MINIMAL_SCORE_FOR_REDIRECT_DEFAULT}
 */
$conf['BestEndPageMinimalScoreForIdRedirect'] = 0;

/**
 * Does automatic canonical processing is on
 */
$conf['MinimalNamesCountForAutomaticCanonical'] = 0;

/**
 * Icon Namespace
 * See {@link syntax_plugin_combo_icon::CONF_ICONS_MEDIA_NAMESPACE}
 * See {@link syntax_plugin_combo_icon::CONF_ICONS_MEDIA_NAMESPACE_DEFAULT}
 */
$conf['icons_namespace']=":combostrap:icons";

/**
 * Css Optimization
 * See {@link action_plugin_combo_css::CONF_ENABLE_MINIMAL_FRONTEND_STYLESHEET}
 * See {@link action_plugin_combo_css::CONF_DISABLE_DOKUWIKI_STYLESHEET}
 */
$conf['enableMinimalFrontEndStylesheet'] = 0;
$conf['disableDokuwikiStylesheet'] = 0;
