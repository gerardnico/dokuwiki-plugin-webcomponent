# Dev


## Hot Reload

To reload the page when a file has changed - [brow](./browser-sync-start.bat)

```bash
browser-sync-start.bat
```


## Release

  * Upload to ComboStrap.com
  * Check that [Travis](https://travis-ci.org/github/gerardnico/dokuwiki-plugin-webcomponent/branches) is green
  * Create the Zip
```bash
cd /opt/www/bytle/farmer.bytle.net/lib/plugins
mv /opt/www/bytle/farmer.bytle.net/combo.zip /opt/www/bytle/farmer.bytle.net/combo-date.zip
zip /opt/www/bytle/farmer.bytle.net/combo.zip -r ./combo
```
  * Reinstall at DataCadamia via the Plugin Manager
  * Working ?
  * Release the date at DokuWiki

