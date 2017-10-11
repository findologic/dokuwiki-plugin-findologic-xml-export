<?php
/**
 * This is the Dokuwiki export made by Dominik Brader for FINDOLOGIC.
 *
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

if (!defined('DOKU_INC')) {
    die('Must be run within Dokuwiki!');
}

require_once (__DIR__ . '/vendor/autoload.php');

class admin_plugin_findologicxmlexport extends DokuWiki_Admin_Plugin
{

    /**
     * Sort plugin in the DokuWiki admin interface.
     * The lower this value is, the higher it is sorted.
     */
    const MENU_SORT = 1;

    /**
     * This is the time format as it gets outputted to the template.
     */
    const TIME_FORMAT = 'd.F Y (H:i)';

    /**
     * This string gets assigned after the DateTime object.
     */
    const TIMEZONE = 'CEST';

    /**
     * Base64 image URL for the edit button.
     */
    const EDIT_IMAGE_URL = 'data:image/png;base64,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';

    /**
     * Template folder directory.
     */
    const TEMPLATE_DIR = __DIR__ . '/tpl';

    public function getMenuSort()
    {
        return self::MENU_SORT;
    }

    /**
     * Admins or higher can access this plugin.
     * It only has page data that is accessible for admins.
     * You do not need to be a superuser to access this plugin.
     */
    public function forAdminOnly()
    {
        return true;
    }

    /**
     * HTML output (gets generated by twig).
     */
    public function html()
    {
        $pagesWithoutTitle = $this->getPagesWithoutTitle();
        $vars = $this->getVariablesForTemplate($pagesWithoutTitle);
        $translations = $this->getTranslations();

        $variables = array_merge($vars, $translations);

        $loader = new Twig_Loader_Filesystem(self::TEMPLATE_DIR);
        $twig = new Twig_Environment($loader);

        echo $twig->render('admin.tpl', $variables);
    }

    /**
     * @return array all pages that do not have a title set.
     */
    private function getPagesWithoutTitle() {
        $indexer = new Doku_Indexer();
        $allPages = $indexer->getPages();

        foreach ($allPages as $page) {
            if (p_get_metadata($page)['description']) { // Only get pages with content
                if (!p_get_metadata($page)['title']) { // Check for pages without a title
                    // Set variables that are used for the template
                    $pagesWithoutTitle[] = $page;
                }
            }
        }

        return $pagesWithoutTitle;
    }

    /**
     * @param $pagesWithoutTitle array of pages without title.
     * @return array variables for twig template.
     */
    private function getVariablesForTemplate($pagesWithoutTitle) {

        // Generate variables based on page data
        foreach ($pagesWithoutTitle as $key => $page) {
            $metadata[] = p_get_metadata($page);
            $url[] = wl($page, '', true);

            $timeModified = new DateTime('@' . $metadata[$key]['last_change']['date']);
            $timeModified->add(new DateInterval('PT2H'));
            $modifiedTimeStamp[] = $timeModified->format(self::TIME_FORMAT) . ' ' . self::TIMEZONE; // Last edit
        }

        // Put variables to array
        $variables = array(
            'pagesWithoutTitle' => $pagesWithoutTitle,
            'metadata' => $metadata,
            'urls' => $url,
            'timestamp' => $modifiedTimeStamp,
            'imageUrl' => self::EDIT_IMAGE_URL,
            );

        return $variables;
    }

    private function getTranslations() {

        // Get translations from DokuWiki
        $translations = array(
            'menu' => $this->getLang('menu'),
            'youCan' => $this->getLang('youCan'),
            'callExport' => $this->getLang('callExport'),
            'noTitleWarning' => $this->getLang("noTitleWarning"),
            'noTitleWarningMore' => $this->getLang("noTitleWarningMoreInformation"),
            'pagesWithoutTitleText' => $this->getLang("pagesWithoutTitle"),
            'namespace' => $this->getLang("namespace"),
            'url' => $this->getLang("url"),
            'lasteditby' => $this->getLang("lasteditby"),
            'lastedited' => $this->getLang("lastedited"),
            'edit' => $this->getLang("edit"),
            'thereAre' => $this->getLang("thereare"),
            'morePagesText' => $this->getLang("morePages"),
            'allPagesHaveATitle' => $this->getLang("allPagesHaveATitle")
        );

        return $translations;
    }

    /**
     * Ignore;
     * This method is not used, but required, or a warning will get thrown.
     */
    public function handle()
    {
    }
}