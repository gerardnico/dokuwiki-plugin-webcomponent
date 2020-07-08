<?php
/**
 * Take the metadata description
 *
 *
 */




class action_plugin_combo_metadescription extends DokuWiki_Action_Plugin
{

    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'description_modification', array());
    }

    /**
     * Add a meta-data description
     */
    function description_modification(&$event, $param)
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


    }


}
