<?php
/**
 * This is the Dokuwiki export made by Dominik Brader for FINDOLOGIC.
 *
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) {
    define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
}

class admin_plugin_findologicxmlexport extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 10000;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return true;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        echo (
            "<h1>FINDOLOGIC XML Export Plugin</h1><p>You can <a href='\". DOKU_URL . 'lib/plugins/findologicxmlexport'>call the FINDOLOGIC Export manually</a>.</p>");
    }
}