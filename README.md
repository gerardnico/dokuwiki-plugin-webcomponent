# Dokuwiki Combostrap Plugin

[![Build Status](https://travis-ci.org/gerardnico/dokuwiki-plugin-webcomponent.svg?branch=master)](https://travis-ci.org/gerardnico/dokuwiki-plugin-webcomponent)


## About

This plugin adds several graphic component which are based on Bootstrap (Version 4.3.1)


## List

The list of component can be found [here](https://gerardnico.com/dokuwiki/webcomponent/)

## Release

### Current

  * [Markdown Header](https://spec.commonmark.org/0.29/#atx-heading) has been deleted because the status `$this->status['section']` of the handler is now private

### 2019-06-14

  * Implementation of the [Markdown Atx Heading](https://spec.commonmark.org/0.29/#atx-heading)
  * First version

## TODO

  * Suppress in teaser - the card body if there is only a image
  * Rename teaser-columns with card columns
  * Implement: image bottom for card


## Dev

Php:

  * To reload the page when a file has changed - [brow](./browser-sync-start.bat)

```bash
browser-sync-start.bat
```
