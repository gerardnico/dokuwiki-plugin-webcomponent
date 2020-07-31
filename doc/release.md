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
  * Rebuild the [index](https://combostrap.com/ui/tabs?do=admin&page=searchindex)
  * Check Travis:
    * [Combo](https://travis-ci.org/github/gerardnico/dokuwiki-plugin-webcomponent/branches)
    * [Strap](https://travis-ci.org/github/ComboStrap/dokuwiki-template-strap)
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
    * https://www.dokuwiki.org/plugin:combo
    * https://www.dokuwiki.org/template:strap

