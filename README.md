# Dokuwiki Web Component Plugin 

[![Build Status](https://travis-ci.org/gerardnico/dokuwiki-plugin-webcomponent.svg?branch=master)](https://travis-ci.org/gerardnico/dokuwiki-plugin-webcomponent)


## About

This plugin implements several Web UI Component.


## List

The list of component can be found [here](https://gerardnico.com/dokuwiki/webcomponent/)


## Dev 

Javascript: 

  * To install the javascript dependency:

```bash
yarn 
# or
yarn install
```
  
  * To build the javascript library:

```bash
npm build
``` 

  * To develop the javascript library:

```bash
npm start
```

Php:

  * To reload the page when a file has changed - [brow](browser-sync-start.bat)
  
```bash
browser-sync-start.bat
```


  
## Release

### 2019-06-14

  * Implementation of the [Markdown Atx Heading](https://spec.commonmark.org/0.29/#atx-heading)
  * First version

## TODO

  * Suppress in teaser - the card body if there is only a image
  * Rename teaser-columns with card columns
  * Implement: image bottom for card


