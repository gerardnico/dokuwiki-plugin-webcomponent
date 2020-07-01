<?php

include_once(__DIR__ . "/PagesIndex.php");

/**
 * Class UrlManagerBestEndPage
 *
 * A class that implements the BestEndPage Algorithm for the {@link action_plugin_webcomponent_urlmanager urlManager}
 */
class UrlManagerBestEndPage
{


    const PAGE_ID_ATTRIBUTE = "bestPageId";
    const BEST_PAGE_SCORE = "bestPageScore";

    public static function getBestEndPageId($pageId)
    {
        $result = array();
        $pageName = noNS($pageId);

        $pagesWithSameName = PagesIndex::pagesWithSameName($pageName, $pageId);
        if (count($pagesWithSameName) > 0) {

            // Default value
            $bestScore = 1;
            $bestPage = $pagesWithSameName[0];

            // The name of the dokuwiki id
            $pageIdNames = explode(':', $pageId);

            // Loop
            foreach ($pagesWithSameName as $targetPageId => $pageTitle) {

                $targetPageIdNames = explode(':', $targetPageId);
                $targetPageIdScore = 0;
                for ($i = 1; $i <= sizeof($pageIdNames); $i++) {
                    $pageIdName = $pageIdNames[sizeof($pageIdNames) - $i];
                    $indexTargetPage = sizeof($targetPageIdNames) - $i;
                    if ($indexTargetPage < 0) {
                        break;
                    }
                    $targetPageIdName = $targetPageIdNames[$indexTargetPage];
                    if ($targetPageIdName == $pageIdName) {
                        $targetPageIdScore++;
                    }

                }
                if ($targetPageIdScore > $bestScore) {
                    $bestScore = $targetPageIdScore;
                    $bestPage = $targetPageId;
                }

            }

            $result = array(
                self::PAGE_ID_ATTRIBUTE =>$bestPage,
                self::BEST_PAGE_SCORE =>$bestScore
            );

        }
        return $result;

    }

    public static function process($pageId){
        list($bestPageId,$bestScore) =  self::getBestEndPageId($pageId);

    }
}
