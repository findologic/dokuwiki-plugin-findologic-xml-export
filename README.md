# DokuWiki Plugin: FINDOLOGIC XML Export

[![Travis](https://travis-ci.org/findologic/dokuwiki-plugin-findologic-xml-export.svg?branch=master)](https://travis-ci.org/TheKeymaster/phpdokuwiki2findologic)

This DokuWiki plugin creates an XML based on the DokuWiki metadata with the FINDOLOGIC XML scheme.  
It uses the library [findologic/libflexport](https://github.com/findologic/libflexport) to generate the XML.

For any other dependencies please refer to the [composer.json](https://raw.githubusercontent.com/findologic/dokuwiki-plugin-findologic-xml-export/master/composer.json) file.

### Install

 1. Download this repository
 2. Go into your DokuWiki folder and open the directory `/lib/plugins/`
 3. Create a folder in this directory named `findologicxmlexport` and copy the downloaded files in it
 
### Usage

 - Open your DokuWiki Admin page and there you will see the newly installed Plugin
 
 ![https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/raw/package_to_dokuwiki_plugin/examples/example_admin.png](https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/raw/package_to_dokuwiki_plugin/examples/example_admin.png)

 - When opening the Plugin there is a link where you can directly access the XML export data.
 - **It is recommend to also include a `.htaccess` file, so strangers that know this link cannot access your page data.**
 
### Important Notes
 
 - Tests are not yet complete. Use it on your own risk
 - Bugs? Please [submit an issue](https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new).