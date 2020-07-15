# Release

## Steps

  * Check a [detail page](http://localhost:81/_detail/strap/strap_heightfixedtopnavbar.png)
  * Change the date in the [info plugin file](../plugin.info.txt) and [info template file](../../../tpl/strap/template.info.txt)
  * Change the snapshot value to the date in the changes page
  * Commit and push
  * Upload to ComboStrap.com
```bash
cd combo
upssh
```
  * Check that [Travis](https://travis-ci.org/github/gerardnico/dokuwiki-plugin-webcomponent/branches) is green
  * Create the Zip
```bash
cd /opt/www/bytle/farmer.bytle.net/lib/plugins
mv /opt/www/bytle/farmer.bytle.net/combo.zip /opt/www/bytle/farmer.bytle.net/combo-date.zip
zip /opt/www/bytle/farmer.bytle.net/combo.zip -r ./combo
```
  * Reinstall at DataCadamia via the Plugin Manager
  * Check the error log
```bash
tail -f /var/log/php-fpm/www-error.log
```
  * Working ?
  * Release the date at DokuWiki

