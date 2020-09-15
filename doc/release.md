# Release



## Steps

Steps to be taken to do a release (functional or snapshot)

  * Check a [detail page](http://localhost:81/_detail/strap/strap_heightfixedtopnavbar.png)
  * Release Type:
    * Functional Release: Change the date in the [info plugin file](../plugin.info.txt) and [info template file](../../../tpl/strap/template.info.txt)
    * Snapshot Release: Do nothing
  * Release Type:
    * Functional Release: Change the snapshot value to the date in the [changes page](http://localhost:81/changes)
    * Snapshot Release: Do nothing
  * Commit and push
  * Upload to ComboStrap.com the website and combo
```bash
cd combo
upssh
cd dataweb
upssh
```
  * Rebuild the [index](https://combostrap.com/ui/tabs?do=admin&page=searchindex)
  * Check Travis:
    * [Combo](https://travis-ci.org/github/gerardnico/dokuwiki-plugin-webcomponent/branches)
    * [Strap](https://travis-ci.org/github/ComboStrap/dokuwiki-template-strap)


  * Snapshot Release: Create the Zip

```bash
cd /opt/www/bytle/farmer.bytle.net/lib/plugins
rm /opt/www/bytle/farmer.bytle.net/combo-snapshot.zip
zip /opt/www/bytle/farmer.bytle.net/combo-snapshot.zip -r ./combo
```

  * Functional Release: Create the Zip

```bash
cd /opt/www/bytle/farmer.bytle.net/lib/plugins
mv /opt/www/bytle/farmer.bytle.net/combo.zip /opt/www/bytle/farmer.bytle.net/combo-date-release-before.zip
zip /opt/www/bytle/farmer.bytle.net/combo.zip -r ./combo
```

  * Function Release:
    * Reinstall at DataCadamia via the Plugin Manager
    * Check the error log
```bash
tail -f /var/log/php-fpm/www-error.log
```
    * Working ?
    * Release the date at DokuWiki
      * [Combo](https://www.dokuwiki.org/plugin:combo)
    * [Strap](https://www.dokuwiki.org/template:strap)
    * Tweet about it

