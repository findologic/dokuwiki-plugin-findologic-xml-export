{# findologicxmlexport/tpl/admin.tpl for findologicxmlexport plugin #}
<!-- findologicxmlexport plugin START -->
<link rel="stylesheet" type="text/css" href="{{stylesheetUrl}}">
<div id="fl-plugin-findologicxmlexport">
    <h1 id="fl-headline">{{languageText['menu']}}</h1>
    <p>
        {{languageText['youCan']}}<a id="fl-exportlink" target="_blank" href="{{exportUrl}}">{{languageText['callExport']}}</a>.
    </p>
    <fieldset>
        {% if totalPages > 0 %}
        <div id="fl-notify-warning" class="notify">
            {{languageText['noTitleWarning']}} <br/> {{languageText['noTitleWarningMoreInformation']}}
        </div>
        {% else %}
        <div id="fl-success" class="success">
            {{languageText['allPagesHaveATitle']}}
        </div>
        {% endif %}
        <legend id="fl-legend-all">
            <span id="fl-legend-all">{{languageText['pagesWithoutTitle']}} ({{totalPages}}) </span>
            <img src="{{informationImageUrl}}" title="{{languageText['noTitleTooltip']}}" class="info_hover">
        </legend>
        <div id="div-table" class="table">
            {% if totalPages > 0 %}
            <table id="table" class="table table-condensed">
                <tbody id="table-body">
                <tr id="header-table-row">
                    <th class="header-page-id">
                        {{languageText['wikipage']}}
                    </th>
                    <th class="header-page-url">
                        {{languageText['url']}}
                    </th>
                    <th class="header-page-author">
                        {{languageText['lasteditby']}}
                    </th>
                    <th class="header-page-last-edited">
                        {{languageText['lastedited']}}
                    </th>
                    <th class="header-page-edit-link">
                        {{languageText['edit']}}
                    </th>
                </tr>
                {% for key, page in page..maxPages-1 %}
                {% if key < totalPages %}
                <tr>
                    <td class="page-id">
                        {# wikipage #}
                        {{pages[page].id}}
                    </td>
                    <td class="page-url">
                        {# url #}
                        <a target="_blank" href="{{pages[page].url}}">{{pages[page].url}}</a>
                    </td>
                    <td class="page-author">
                        {# lasteditby #}
                        {{pages[page].author}}
                    </td>
                    <td class="page-last-edited">
                        {# lastedited #}
                        {{pages[page].lastEdit|localizeddate('long', 'medium', locale)}}
                    </td>
                    <td class="page-edit-link">
                        {# edit #}
                        <a target="_blank" class='page-edit-link-a' href="{{pages[page].url}}&do=edit">
                            <div class='page-edit-link-div' style='background-image:url("{{editImageUrl}}");'></div>
                        </a>
                    </td>
                </tr>
                {% endif %}
                {% endfor %}
                {% if pagesSkipped > 0 %}
                <div id="fl-notify-morepages" class="fl-notify notify">
                    {{languageText['thereAre']}}{{pagesSkipped}}{{languageText['morePages']}}
                </div>
                {% endif %}
                </tbody>
            </table>
            {% endif %}
        </div>
    </fieldset>
</div>
<script src="{{scriptUrl}}"></script>
<!-- findologicxmlexport plugin END -->