# DokuWiki Plugin: FINDOLOGIC XML Export

[![Travis](https://travis-ci.org/findologic/dokuwiki-plugin-findologic-xml-export.svg?branch=master)](https://travis-ci.org/findologic/dokuwiki-plugin-findologic-xml-export/)
[![code climate](https://camo.githubusercontent.com/c4ca5e03a9cfcdcef3c074ceb2c0f3555725128d/68747470733a2f2f6170692e636f6465636c696d6174652e636f6d2f76312f6261646765732f33363239383432376434323736646231386661362f6d61696e7461696e6162696c697479)](https://codeclimate.com/github/findologic/dokuwiki-plugin-findologic-xml-export)
[![code cov](https://codecov.io/gh/findologic/dokuwiki-plugin-findologic-xml-export/branch/master/graph/badge.svg)](https://codecov.io/gh/findologic/dokuwiki-plugin-findologic-xml-export)

This DokuWiki plugin creates an XML based on the DokuWiki metadata with the FINDOLOGIC XML scheme.  
It uses the library [findologic/libflexport](https://github.com/findologic/libflexport) to generate the XML.

For any other dependencies please refer to the [composer.json](https://raw.githubusercontent.com/findologic/dokuwiki-plugin-findologic-xml-export/master/composer.json) file.

### Install

 - Open your extension manager and install the Plugin via "Search and Install"

### Manual installation

 1. Download this repository
 2. Go into your DokuWiki folder and open the directory `/lib/plugins/`
 3. Create a folder in this directory named `findologicxmlexport` and copy the downloaded files in it
 
### Usage

 - Open your DokuWiki Admin page and there you will see the newly installed Plugin
 
 ![https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/raw/master/examples/example_admin.png](https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/raw/master/examples/example_admin.png)

 - When opening the Plugin there is a link where you can directly access the XML export data.
 - **It is recommend to also include a `.htaccess` file, so strangers that know this link cannot access your page data.**
 
### Important Notes
 
 - Tests are not yet complete. Use it on your own risk!
 - Bugs? Please [submit an issue](https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new).