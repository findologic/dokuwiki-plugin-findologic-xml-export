<?php
/**
 * DokuWiki Plugin phpdokuwiki2findologic (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Dominik Brader <d.brader@findologic.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_phpdokuwiki2findologic extends DokuWiki_Admin_Plugin {

    var $output = 'noMessage';


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
        if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do
        $conf['url'] = 'docs.findologic.com';

        $this->output = 'invalid';
        if (!checkSecurityToken()) return;
        if (!is_array($_REQUEST['cmd'])) return;

        // verify valid values
        switch (key($_REQUEST['cmd'])) {
            case 'save' :
                //var_dump($_POST['person']);
                var_dump ($_POST['cmd']);
                die;
                $this->output = 'success';
                $meta['url'] = $_REQUEST['cmd'];
            break;
        }
        if (filter_var(key($_REQUEST['cmd']), FILTER_VALIDATE_URL) === FALSE) {
            die('Not a valid URL');
        }


    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $conf;
        $url = $conf['url'];



        ptln('<h1>'. $this->getLang('menu') .'</h1>');
        ptln('<div class="alert alert-warning">'. $this->getLang('warning') . '</div>');
        ptln('<div class="alert alert-success">'.htmlspecialchars($this->getLang($this->output)).'</div>');

        ptln('<div class="level1"><p>'. $this->getLang('information') .'</p></div>');

        ptln('<form action="'.wl($ID).'" method="post">');

        // Can be ignored. Information for DokuWiki to return to this page after saving.
        ptln('<input type="hidden" name="do"   value="admin" />');
        ptln('<input type="hidden" name="page" value="'.$this->getPluginName().'" />');
        formSecurityToken();

        ptln('<div id="config__manager">');
        ptln('<fieldset id="_basic">');
        ptln('<legend>'. $this->getLang('text_basic') .'</legend>');
        ptln('<div class="table-responsive">');
        ptln('<table class="inline table table-striped table-condensed">');
        ptln('<tbody>');
        ptln('<tr>');
        ptln('<td class="label">');
        ptln('<span class="outkey"><a href="#">'. $this->getLang('outkey_url') .'</a></span><label for="config___title" class="control-label">'. $this->getLang('desc_url') .'</label>');
        ptln('</td>');

        ptln('<td class="value">');
        ptln('<input class="edit form-control" type="text" autocomplete="off" name="text[url]" value="'. $url .'"  placeholder="'.$this->getLang('textbox_url').'" />');
        ptln('</td>');

        ptln('</tr>');
        ptln('</tbody>');
        ptln('</table>');
        ptln('</div>');
        ptln('</fieldset>');
        ptln('<input type="submit" name="cmd[save]" class="btn-success" value="'.$this->getLang('btn_save').'" />');
        ptln('</div>');
        ptln('</form>');


    }
}

// vim:ts=4:sw=4:et: