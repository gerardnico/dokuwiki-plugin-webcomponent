<?php
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . 'admin.php');
require_once(DOKU_INC . 'inc/parser/xhtml.php');
require_once(__DIR__ . '/../class/PageRules.php');

/**
 * The admin pages
 * need to inherit from this class
 *
 */
class admin_plugin_webcomponent_pagerules extends DokuWiki_Admin_Plugin
{


    // Use to pass parameter between the handle and the html function to keep the form data
    var $redirectionSource = '';
    var $redirectionTarget = '';
    var $currentDate = '';
    // Deprecated
    private $redirectionType;
    // Deprecated
    var $isValidate = '';
    // Deprecated
    var $targetResourceType = 'Default';


    // Name of the variable in the HTML form
    const FORM_NAME_SOURCE_PAGE = 'SourcePage';
    const FORM_NAME_TARGET_PAGE = 'TargetPage';

    /**
     * @var array|string[]
     */
    private $infoPlugin;

    /**
     * @var PageRules|null
     */
    private $pageRules;


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
        return $this->getPluginName()." - ".$this->lang['PageRules'];
    }

    public function getMenuIcon()
    {
        return DOKU_PLUGIN . $this->getPluginName() . '/admin/'.$this->getPluginComponent().'.svg';
    }


    /**
     * handle user request
     */
    function handle()
    {

        // Handle here to not make
        if ($this->pageRules == null) {
            $sqlite = PluginStatic::getSqlite();
            if ($sqlite == null) {
                // A message should have already been send by the getSqlite function
                return;
            }
            $this->pageRules = new PageRules($sqlite);
        }

        if ($_POST['Add']) {

            $this->redirectionSource = $_POST[self::FORM_NAME_SOURCE_PAGE];
            $this->redirectionTarget = $_POST[self::FORM_NAME_TARGET_PAGE];

            if ($this->redirectionSource == $this->redirectionTarget) {
                msg($this->lang['SameSourceAndTargetAndPage'] . ': ' . $this->redirectionSource . '', -1);
                return;
            }


            // This a direct redirection
            // If the source page exist, do nothing
            if (page_exists($this->redirectionSource)) {

                $title = false;
                global $conf;
                if ($conf['useheading']) {
                    $title = p_get_first_heading($this->redirectionSource);
                }
                if (!$title) $title = $this->redirectionSource;
                msg($this->lang['SourcePageExist'] . ' : <a href="' . wl($this->redirectionSource) . '">' . hsc($title) . '</a>', -1);
                return;

            } else {

                // Is this a direct redirection to a valid target page
                if (!page_exists($this->redirectionTarget)) {

                    if (PluginStatic::isValidURL($this->redirectionTarget)) {

                        $this->targetResourceType = 'Url';

                    } else {

                        msg($this->lang['NotInternalOrUrlPage'] . ': ' . $this->redirectionTarget . '', -1);
                        return;

                    }

                } else {

                    $this->targetResourceType = 'Internal Page';

                }
                $this->pageRules->addRule($this->redirectionSource, $this->redirectionTarget);
                msg($this->lang['Saved'], 1);

            }


        }

        if ($_POST['Delete']) {

            $ruleId = $_POST['SourcePage'];
            $this->pageRules->deleteRule($ruleId);
            msg($this->lang['Deleted'], 1);

        }
        if ($_POST['Validate']) {
            $ruleId = $_POST['SourcePage'];
            $this->pageRules->validateRules($ruleId);
            msg($this->lang['Validated'], 1);
        }
    }

    /**
     * output appropriate html
     */
    function html()
    {

        global $conf;

        echo $this->locale_xhtml('intro');

        // Add a redirection
        ptln('<h2><a name="add_redirection" id="add_redirection">' . $this->lang['AddModifyRedirection'] . '</a></h2>');
        ptln('<div class="level2">');
        ptln('<form action="" method="post">');
        ptln('<table class="inline">');

        ptln('<thead>');
        ptln('		<tr><th>' . $this->lang['Field'] . '</th><th>' . $this->lang['Value'] . '</th> <th>' . $this->lang['Information'] . '</th></tr>');
        ptln('</thead>');

        ptln('<tbody>');
        ptln('		<tr><td><label for="add_sourcepage" >' . $this->lang['source_page'] . ': </label></td><td><input type="text" id="add_sourcepage" name="' . self::FORM_NAME_SOURCE_PAGE . '" value="' . $this->redirectionSource . '" class="edit" /></td><td>' . $this->lang['source_page_info'] . '</td></td></tr>');
        ptln('		<tr><td><label for="add_targetpage" >' . $this->lang['target_page'] . ': </label></td><td><input type="text" id="add_targetpage" name="' . self::FORM_NAME_TARGET_PAGE . '" value="' . $this->redirectionTarget . '" class="edit" /></td><td>' . $this->lang['target_page_info'] . '</td></tr>');
        ptln('		<tr>');
        ptln('			<td colspan="3">');
        ptln('				<input type="hidden" name="do"    value="admin" />');
        ptln('				<input type="hidden" name="page"  value="404manager" />');
        ptln('				<input type="submit" name="Add" class="button" value="' . $this->lang['btn_addmodify'] . '" />');
        ptln('			</td>');
        ptln('		</tr>');
        ptln('</tbody>');
        ptln('</table>');
        ptln('</form>');

        // Add the file add from the lang directory
        echo $this->locale_xhtml('add');
        ptln('</div>');


//      List of redirection
        ptln('<h2><a name="list_redirection" id="list_redirection">' . $this->lang['ListOfRedirection'] . '</a></h2>');
        ptln('<div class="level2">');

        ptln('<div class="table-responsive">');

        ptln('<table class="table table-hover">');
        ptln('	<thead>');
        ptln('		<tr>');
        ptln('			<th>&nbsp;</th>');
        ptln('			<th>' . $this->lang['SourcePage'] . '</th>');
        ptln('			<th>' . $this->lang['TargetPage'] . '</th>');
        ptln('			<th>' . $this->lang['CreationDate'] . '</th>');
        ptln('	    </tr>');
        ptln('	</thead>');

        ptln('	<tbody>');


        foreach ($this->getRedirections() as $key => $row) {

            if ($this->dataStoreType == self::DATA_STORE_TYPE_SQLITE) {
                $sourcePageId = $row['SOURCE'];
                $targetPageId = $row['TARGET'];
                $creationDate = $row['CREATION_TIMESTAMP'];
            } else {
                $sourcePageId = $key;
                $targetPageId = $row['TargetPage'];
                $creationDate = $row['CreationDate'];
            }
            $title = false;
            if ($conf['useheading']) {
                $title = p_get_first_heading($targetPageId);
            }
            if (!$title) $title = $targetPageId;


            ptln('	  <tr class="redirect_info">');
            ptln('		<td>');
            ptln('			<form action="" method="post">');
            ptln('				<input type="image" src="' . DOKU_BASE . 'lib/plugins/404manager/images/delete.jpg" name="Delete" title="Delete" alt="Delete" value="Submit" />');
            ptln('				<input type="hidden" name="Delete"  value="Yes" />');
            ptln('				<input type="hidden" name="SourcePage"  value="' . $sourcePageId . '" />');
            ptln('			</form>');

            ptln('		</td>');
            print('	<td>');
            tpl_link(wl($sourcePageId), $this->truncateString($sourcePageId, 30), 'title="' . $sourcePageId . '" class="wikilink2" rel="nofollow"');
            ptln('		</td>');
            print '		<td>';
            tpl_link(wl($targetPageId), $this->truncateString($targetPageId, 30), 'title="' . hsc($title) . ' (' . $targetPageId . ')"');
            ptln('		</td>');
            ptln('		<td>' . $creationDate . '</td>');
            ptln('    </tr>');
        }
        ptln('  </tbody>');
        ptln('</table>');
        ptln('</div>'); //End Table responsive
        ptln('</div>'); // End level 2


    }

    /**
     * Generate a text with a max length of $length
     * and add ... if above
     * @param $myString
     * @param $length
     * @return string
     */
    function truncateString($myString, $length)
    {
        if (strlen($myString) > $length) {
            $myString = substr($myString, 0, $length) . ' ...';
        }
        return $myString;
    }




}
