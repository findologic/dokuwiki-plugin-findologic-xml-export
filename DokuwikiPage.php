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
     * This is the time format as it gets outputted to the template.
     * DAY.MONTH YEAR (HOUR:MINUTE)
     */
    const TIME_FORMAT = '%d.%B %Y (%H:%M)';

    public $id;
    public $url;
    public $author;
    public $lastEdit;
    public $metadata;

    /**
     * Gets DokuWiki page data.
     *
     * * **id** - ID.
     * * **url** - URL of the page (absolute).
     * * **author** - The author of the last change that was made (not the creator of the page!).
     * * **lastEdit** - Formatted timestamp.
     * * **metadata** - Entire metadata.
     *
     * @param $page string page ID.
     */
    public function __construct($page)
    {
        $this->id = $page;
        $this->url = wl($page, '', true);
        $this->author = p_get_metadata($page)['last_change']['user'];
        $this->lastEdit = $this->formatTime(p_get_metadata($page)['last_change']['date']);
        $this->metadata = p_get_metadata($page);
        // If no user was logged in, then no author is saved.
        // DokuWiki uses '(external edit)' as value, so we use it too.
        if (empty($this->author)) {
            $this->author = '(external edit)';
        }
    }

    /**
     * Returns Localized and proper formatted string.
     *
     * @param $unixTimestamp int Unix timestamp.
     * @return string Time as formatted string.
     */
    private function formatTime($unixTimestamp)
    {
        $timeFormatted = strftime(self::TIME_FORMAT, $unixTimestamp);
        return $timeFormatted;
    }
}