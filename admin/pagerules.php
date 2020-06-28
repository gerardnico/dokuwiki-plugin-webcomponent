<?php
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . 'admin.php');
require_once(DOKU_INC . 'inc/parser/xhtml.php');
require_once(__DIR__ . '/../class/PageRules.php');
require_once(__DIR__ . '/../class/PluginStatic.php');

/**
 * The admin pages
 * need to inherit from this class
 *
 *
 * ! important !
 * The suffix of the class name should:
 *   * be equal to the name of the file
 *   * and have only letters
 */
class admin_plugin_webcomponent_pagerules extends DokuWiki_Admin_Plugin
{


    // Name of the column and of the variable in the HTML form
    const ID_NAME = 'ID';
    const MATCHER_NAME = 'MATCHER';
    const TARGET_NAME = 'TARGET';
    const PRIORITY_NAME = 'PRIORITY';
    const TIMESTAMP_NAME = 'TIMESTAMP';

    /**
     * @var array|string[]
     */
    private $infoPlugin;

    /**
     * @var PageRules
     */
    private $pageRuleManager;


    /**
     * admin_plugin_404manager constructor.
     *
     * Use the get function instead
     */
    public function __construct()
    {

        // enable direct access to language strings
        // of use of $this->getLang
        $this->setupLocale();
        $this->currentDate = date("c");
        $this->infoPlugin = $this->getInfo();


    }


    /**
     * Access for managers allowed
     */
    function forAdminOnly()
    {
        return false;
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort()
    {
        return 140;
    }

    /**
     * return prompt for admin menu
     * @param string $language
     * @return string
     */
    function getMenuText($language)
    {
        return $this->getPluginName() . " - " . $this->lang['PageRules'];
    }

    public function getMenuIcon()
    {
        return DOKU_PLUGIN . $this->getPluginName() . '/admin/' . $this->getPluginComponent() . '.svg';
    }


    /**
     * handle user request
     */
    function handle()
    {

        /**
         * Handle Sqlite instantiation  here and not in the constructore
         * to not make sqlite mandatory everywhere
         */
        if ($this->pageRuleManager == null) {
            $sqlite = PluginStatic::getSqlite();
            if ($sqlite == null) {
                // A message should have already been send by the getSqlite function
                return;
            }
            $this->pageRuleManager = new PageRules($sqlite);

        }

        /**
         * If one of the form submit has the add key
         */
        if ($_POST['save']) {

            $id = $_POST[self::ID_NAME];
            $matcher = $_POST[self::MATCHER_NAME];
            $target = $_POST[self::TARGET_NAME];
            $priority = $_POST[self::PRIORITY_NAME];

            if ($matcher == null) {
                msg('Matcher can not be null', PluginStatic::LVL_MSG_ERROR);
                return;
            }
            if ($target == null) {
                msg('Target can not be null', PluginStatic::LVL_MSG_ERROR);
                return;
            }

            if ($matcher == $target) {
                msg($this->lang['SameSourceAndTargetAndPage'] . ': ' . $matcher . '', PluginStatic::LVL_MSG_ERROR);
                return;
            }

            if ($id == null) {
                $this->pageRuleManager->addRule($matcher, $target, $priority);
            } else {
                $this->pageRuleManager->updateRule($id, $matcher, $target, $priority);
            }
            msg($this->lang['Saved'], PluginStatic::LVL_MSG_INFO);


        }

        if ($_POST['Delete']) {

            $ruleId = $_POST[self::ID_NAME];
            $this->pageRuleManager->deleteRule($ruleId);
            msg($this->lang['Deleted'], PluginStatic::LVL_MSG_INFO);

        }

    }

    /**
     * output appropriate html
     * TODO: Add variable parsing where the key is the key of the lang object ??
     */
    function html()
    {

        global $conf;

        ptln('<h1>' . ucfirst($this->getPluginName()) . ' - ' . ucfirst($this->getPluginComponent()) . '</a></h1>');
        $relativePath = 'admin/' . $this->getPluginComponent() . '_intro';
        echo $this->locale_xhtml($relativePath);

        // Forms
        if ($_POST['upsert']) {

            $id = $_POST[self::ID_NAME];
            $matcher = $_POST[self::MATCHER_NAME];
            $target = $_POST[self::TARGET_NAME];
            $priority = $_POST[self::PRIORITY_NAME];
            if ($priority == null) {
                $priority = 1;
            }

            // Add a redirection
            // ptln('<h2><a name="add_redirection" id="add_redirection">' . $this->lang['AddModifyRedirection'] . '</a></h2>');
            ptln('<div class="level2">');
            ptln('<form action="" method="post">');
            ptln('<table class="m-3">');

            ptln('<thead>');
            ptln('		<tr><th class="p-2">' . $this->lang['Field'] . '</th><th class="p-2">' . $this->lang['Value'] . '</th> <th class="p-2">' . $this->lang['Information'] . '</th></tr>');
            ptln('</thead>');

            ptln('<tbody>');
            ptln('		<tr><td class="p-2"><label for="add_sourcepage" >' . 'Matcher' . ': </label></td><td class="p-2"><input type="text" id="add_sourcepage" name="' . self::MATCHER_NAME . '" value="' . $matcher . '" class="edit" /></td><td class="p-2">' . '' . '</td></td></tr>');
            ptln('		<tr><td class="p-2"><label for="add_targetpage" >' . $this->lang['target_page'] . ': </label></td><td class="p-2"><input type="text" id="add_targetpage" name="' . self::TARGET_NAME . '" value="' . $target . '" class="edit" /></td><td class="p-2">' . $this->lang['target_page_info'] . '</td></tr>');
            ptln('		<tr><td class="p-2"><label for="priority" >' . 'priority' . ': </label></td><td class="p-2"><input type="id" id="priority" name="' . self::PRIORITY_NAME . '" value="' . $priority . '" class="edit" /></td><td class="p-2">' . 'The priority in which the rules are applied' . '</td></tr>');
            ptln('</tbody>');
            ptln('</table>');
            ptln('<input type="hidden" name="do"    value="admin" />');
            if ($id != null) {
                ptln('<input type="hidden" name="' . self::ID_NAME . '" value="' . $id . '" />');
            }
            ptln('<input type="hidden" name="page"  value="' . $this->getPluginName() . '_' . $this->getPluginComponent() . '" />');
            ptln('<a class="btn btn-light" href="?do=admin&page=webcomponent_pagerules" > ' . 'Cancel' . ' <a/>');
            ptln('<input class="btn btn-primary" type="submit" name="save" class="button" value="' . 'Save' . '" />');
            ptln('</form>');

            // Add the file add from the lang directory
            echo $this->locale_xhtml('admin/' . $this->getPluginComponent() . '_add');
            ptln('</div>');

        } else {

            ptln('<form action="" method="post">');
            ptln('    <input type="hidden" name="do"    value="admin" />');
            ptln('	<input type="hidden" name="page"  value="' . $this->getPluginName() . '_' . $this->getPluginComponent() . '" />');
            ptln('	<input type="submit" name="upsert" name="Create a page rule" class="button" value="' . 'Create a rule' . '" />');
            ptln('</form>');

            //      List of redirection
            ptln('<h2><a name="list_redirection" id="list_redirection">' . $this->lang['ListOfRedirection'] . '</a></h2>');
            ptln('<div class="level2">');

            ptln('<div class="table-responsive">');

            ptln('<table class="table table-hover">');
            ptln('	<thead>');
            ptln('		<tr>');
            ptln('			<th>&nbsp;</th>');
            ptln('			<th>' . 'Priority' . '</th>');
            ptln('			<th>' . $this->lang['SourcePage'] . '</th>');
            ptln('			<th>' . $this->lang['TargetPage'] . '</th>');
            ptln('			<th>' . $this->lang['CreationDate'] . '</th>');
            ptln('	    </tr>');
            ptln('	</thead>');
            ptln('	<tbody>');


            foreach ($this->pageRuleManager->getRules() as $key => $row) {

                $id = $row[self::ID_NAME];
                $matcher = $row[self::MATCHER_NAME];
                $target = $row[self::TARGET_NAME];
                $timestamp = $row[self::TIMESTAMP_NAME];
                $priority = $row[self::PRIORITY_NAME];


                ptln('	  <tr class="redirect_info">');
                ptln('		<td>');
                ptln('			<form action="" method="post">');
                ptln('				<input type="image" src="' . DOKU_BASE . 'lib/plugins/' . $this->getPluginName() . '/images/delete.jpg" name="Delete" title="Delete" alt="Delete" value="Submit" />');
                ptln('				<input type="hidden" name="Delete"  value="Yes" />');
                ptln('				<input type="hidden" name="' . self::ID_NAME . '"  value="' . $id . '" />');
                ptln('			</form>');

                ptln('		</td>');
                ptln('		<td>' . $priority . '</td>');
                ptln('	    <td>' . $matcher . '</td>');
                ptln('		<td>' . $target . '</td>');
                ptln('		<td>' . $timestamp . '</td>');
                ptln('    </tr>');
            }
            ptln('  </tbody>');
            ptln('</table>');
            ptln('</div>'); //End Table responsive
            ptln('</div>'); // End level 2


        }


    }


}
