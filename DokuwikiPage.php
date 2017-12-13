<?php
/**
 * This is the Dokuwiki export for FINDOLOGIC.
 *
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

class DokuwikiPage
{

    /**
     * @var string ID of the DokuWiki page.
     */
    public $id;

    /**
     * @var string URL of the DokuWiki page (absolute).
     */
    public $url;

    /**
     * @var string Author of the last change that was made to the DokuWiki page.
     */
    public $author;

    /**
     * @var DateTime DateTime object of last edited date.
     */
    public $lastEdit;

    /**
     * @var array Entire metadata of the page.
     */
    public $metadata;

    /**
     * Gets DokuWiki page data based on page ID.
     *
     * @param $page string page ID.
     */
    public function __construct($page)
    {
        $this->id = $page;
        $this->url = wl($page, '', true);
        $this->author = p_get_metadata($page)['last_change']['user'];
        $date = new DateTime();
        $this->lastEdit = $date->setTimestamp(p_get_metadata($page)['last_change']['date']);
        $this->metadata = p_get_metadata($page);
        // If no user was logged in, then no author is saved.
        // DokuWiki uses '(external edit)' as value, so we use it too.
        if (empty($this->author)) {
            $this->author = '(external edit)';
        }
    }
}