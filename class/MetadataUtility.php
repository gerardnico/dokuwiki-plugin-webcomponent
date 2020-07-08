<?php

namespace ComboStrap;

/**
 * Class MetadataUtility
 * @package ComboStrap
 * A class with Dokuwiki Metadata utility
 */
class MetadataUtility
{

    /**
     * The HTML id of the box (for testing purpose)
     */
    const META_MESSAGE_BOX_ID = "metadata-viewer";

    /**
     *
     * @param $lang - the lang array of the plugin (generally $this->lang)
     * @return string - an HTML box of the array
     */
    public static function getHtmlMetadataBox($lang)
    {
        $content = '<div id="' . self::META_MESSAGE_BOX_ID . '" class="alert alert-success " role="note">';

        global $ID;
        $metadata = p_read_metadata($ID);
        $metas = $metadata['persistent'];


        if (!array_key_exists("canonical", $metas)) {
            $metas["canonical"] = PluginUtility::createUrl("canonical", "No Canonical");
        }

        $content .= ArrayUtility::formatAsHtmlList($metas);


        $referenceStyle = array(
            "font-size" => "95%",
            "clear" => "both",
            "bottom" => "5px",
            "right" => "15px",
            "position" => "absolute",
            "font-style" => "italic"
        );

        $content .= '<div style="' . PluginUtility::array2InlineStyle($referenceStyle) . '">' . $lang['message_come_from'] . ' <a href="' . PluginUtility::$URL_BASE . '/metadata/viewer" class="urlextern" title="ComboStrap Metdata Viewer" >ComboStrap Metdata Viewer</a>.</div>';
        $content .= '</div>';
        return $content;
    }
}
