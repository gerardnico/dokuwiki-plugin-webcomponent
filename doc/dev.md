# Dev

## About

dev configuration.



## Start

  * Follows the [devel doc](https://datacadamia.com/dokuwiki/devel)
  * Start the container

```
docker start doku
```

  * Install the plugin via Git

```
cd lib\plugins
git clone https://github.com/gerardnico/dokuwiki-plugin-webcomponent.git combo
git branch devel
```

## Hot Reload

To reload the page when a file has changed - [brow](./browser-sync-start.bat)

```bash
browser-sync-start.bat
```


