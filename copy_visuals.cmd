@echo off

SET SCRIPT_PATH=%~dp0
SET WEB_COMPO_VISUAL_BACKUP=%SCRIPT_PATH%doc
echo .
echo Backup of the dokuwiki pages that serves as visual into the directory %WEB_COMPO_VISUAL_BACKUP%
echo .
echo .
SET DOKU_ROOT=%SCRIPT_PATH%..\..\..
SET DOKU_DATA=%DOKU_ROOT%\dataweb

SET WEB_COMPO_VISUAL_PAGES=%DOKU_DATA%\pages
SET WEB_COMPO_VISUAL_PAGES_DST=%WEB_COMPO_VISUAL_BACKUP%\pages

echo Copying the pages:
echo   * from %WEB_COMPO_VISUAL_PAGES%
echo   * to %WEB_COMPO_VISUAL_PAGES_DST%
echo .
call xcopy %WEB_COMPO_VISUAL_PAGES% %WEB_COMPO_VISUAL_PAGES_DST% /s /e /h /y
echo .

SET WEB_COMPO_VISUAL_MEDIAS=%DOKU_DATA%\media
SET WEB_COMPO_VISUAL_MEDIAS_DST=%WEB_COMPO_VISUAL_BACKUP%\media

echo Copying the images:
echo   * from %WEB_COMPO_VISUAL_MEDIAS%
echo   * to %WEB_COMPO_VISUAL_MEDIAS_DST%
echo .
call xcopy %WEB_COMPO_VISUAL_MEDIAS% %WEB_COMPO_VISUAL_MEDIAS_DST% /s /e /h /y
echo .
echo Done
