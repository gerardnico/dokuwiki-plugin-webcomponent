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

## Private / Public Repo

```bash
git config --global core.excludesfile fullPathGitIgnore
```
https://docs.travis-ci.com/user/installing-dependencies/#installing-projects-from-source

Strategy:
  * use [private submodule](https://www.appveyor.com/docs/how-to/private-git-sub-modules/) such as adding
`/.gitmodules` to `.gitignore` and share it amongst developers via a back-channel.
  * 2 different repositories where the [relationship is build using symlinks](https://stackoverflow.com/questions/2195826/proper-git-workflow-for-combined-os-and-private-code)

```batch
mklink /D "D:\dokuwiki\lib\plugins\combo\_test"  "D:\dokuwiki\lib\plugins\combo_test\_test"
```

The link stay static in order to help IDEA and to allows good refactoring

```php
require_once (__DIR__ . '/../../combo/class/'.'class.php');
```

