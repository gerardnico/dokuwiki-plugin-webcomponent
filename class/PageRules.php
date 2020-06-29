<?php


/**
 * The manager that handles the redirection metadata
 *
 */
require_once(__DIR__ . '/PluginStatic.php');

class PageRules
{

    // Name of the column
    // Used also in the HTML form as name
    public const ID_NAME = 'ID';
    public const PRIORITY_NAME = 'PRIORITY';
    public const MATCHER_NAME = 'MATCHER';
    public const TARGET_NAME = 'TARGET';
    public const TIMESTAMP_NAME = 'TIMESTAMP';


    /** @var helper_plugin_sqlite $sqlite */
    private $sqlite;

    /**
     * UrlRewrite constructor.
     * The sqlite path is dependent of the dokuwiki data
     * and for each new class, the dokuwiki helper just delete it
     * We need to pass it then
     * @param helper_plugin_sqlite $sqlite
     */
    public function __construct(helper_plugin_sqlite $sqlite)
    {
        $this->sqlite = $sqlite;
    }


    /**
     * Delete Redirection
     * @param string $ruleId
     */
    function deleteRule($ruleId)
    {

        $res = $this->sqlite->query('delete from PAGE_RULES where id = ?', $ruleId);
        if (!$res) {
            PluginStatic::msg("Something went wrong when deleting the redirections");
        }
        $this->sqlite->res_close($res);


    }


    /**
     * Is Redirection of a page Id Present
     * @param integer $id
     * @return boolean
     */
    function ruleExists($id)
    {
        $id = strtolower($id);


        $res = $this->sqlite->query("SELECT count(*) FROM PAGE_RULES where ID = ?", $id);
        $exists = null;
        if ($this->sqlite->res2single($res) == 1) {
            $exists = true;
        } else {
            $exists = false;
        }
        $this->sqlite->res_close($res);
        return $exists;


    }

    /**
     * Is Redirection of a page Id Present
     * @param integer $pattern
     * @return boolean
     */
    function patternExists($pattern)
    {


        $res = $this->sqlite->query("SELECT count(*) FROM PAGE_RULES where MATCHER = ?", $pattern);
        $exists = null;
        if ($this->sqlite->res2single($res) == 1) {
            $exists = true;
        } else {
            $exists = false;
        }
        $this->sqlite->res_close($res);
        return $exists;


    }


    /**
     * @param $sourcePageId
     * @param $targetPageId
     * @param $priority
     * @return int - the rule id
     */
    function addRule($sourcePageId, $targetPageId, $priority)
    {
        $currentDate = date("c");
        return $this->addRuleWithDate($sourcePageId, $targetPageId, $priority, $currentDate);
    }

    /**
     * Add Redirection
     * This function was needed to migrate the date of the file conf store
     * You would use normally the function addRedirection
     * @param string $matcher
     * @param string $target
     * @param $priority
     * @param $creationDate
     * @return int - the last id
     */
    function addRuleWithDate($matcher, $target, $priority, $creationDate)
    {

        $entry = array(
            'target' => $target,
            'timestamp' => $creationDate,
            'matcher' => $matcher,
            'priority' => $priority
        );

        $res = $this->sqlite->storeEntry('PAGE_RULES', $entry);
        if (!$res) {
            PluginStatic::msg("There was a problem during insertion");
        }
        $lastInsertId = $this->sqlite->getAdapter()->getDb()->lastInsertId();
        $this->sqlite->res_close($res);
        return $lastInsertId;

    }

    function updateRule($id, $matcher, $target, $priority)
    {
        $updateDate = date("c");

        $entry = array(
            'matcher' => $matcher,
            'target' => $target,
            'priority' => $priority,
            'timestamp' => $updateDate,
            'íd' => $id
        );

        $statement = 'update PAGE_RULES set matcher = ?, target = ?, priority = ?, timestamp = ? where id = ?';
        $res = $this->sqlite->query($statement, $entry);
        if (!$res) {
            PluginStatic::msg("There was a problem during the update");
        }
        $this->sqlite->res_close($res);

    }


    /**
     * Delete all rules
     * Use with caution
     */
    function deleteAll()
    {

        $res = $this->sqlite->query("delete from PAGE_RULES");
        if (!$res) {
            PluginStatic::msg('Errors during delete of all redirections');
        }
        $this->sqlite->res_close($res);

    }

    /**
     * Return the number of page rules
     * @return integer
     */
    function count()
    {

        $res = $this->sqlite->query("select count(1) from PAGE_RULES");
        if (!$res) {
            PluginStatic::msg('Errors during delete of all redirections');
        }
        $value = $this->sqlite->res2single($res);
        $this->sqlite->res_close($res);
        return $value;

    }


    /**
     * @return array
     */
    function getRules()
    {

        $res = $this->sqlite->query("select * from PAGE_RULES order by PRIORITY asc");
        if (!$res) {
            throw new RuntimeException('Errors during select of all redirections');
        }
        return $this->sqlite->res2arr($res);


    }

    public function getRule($id)
    {
        $res = $this->sqlite->query("SELECT * FROM PAGE_RULES where ID = ?", $id);

        $array = $this->sqlite->res2row($res);
        $this->sqlite->res_close($res);
        return $array;

    }




}