{# findologicxmlexport/tpl/admin.tpl for findologicxmlexport plugin #}
{% set totalPages = var['amount'] %}
<link rel="stylesheet" type="text/css" href="{{var['stylesheetUrl']}}">
<div id="fl-plugin-findologicxmlexport">
    <h1 id="fl-headline">{{languageText['menu']}}</h1>
    <p>
        {{languageText['youCan']}}<a id="fl-exportlink" href="{{var['exportUrl']}}">{{languageText['callExport']}}</a>.
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
        <legend id="fl-legend">{{languageText['pagesWithoutTitle']}} ({{totalPages}})</legend>
        <div id="div-table" class="table">
            {% if totalPages > 0 %}
            <table id="table" class="table table-condensed">
                <tbody id="table-body">
                <tr id="header-table-row">
                    <th class="header-page-id">
                        {{languageText['namespace']}}
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
                {% for page in page..4 %} {# 5 pages are shown #}
                {% if page < 5 %}
                <tr>
                    <td class="page-id">
                        {# namespace #}
                        {{var[page]['id']}}
                    </td>
                    <td class="page-url">
                        {# url #}
                        <a target="_blank" href="{{var[page]['url']}}">{{var[page]['url']}}</a>
                    </td>
                    <td class="page-author">
                        {# lasteditby #}
                        {{var[page]['author']}}
                    </td>
                    <td class="page-last-edited">
                        {# lastedited #}
                        {{var[page]['lastEdit']}}
                    </td>
                    <td class="page-edit-link">
                        {# edit #}
                        <a target="_blank" class='page-edit-link-a' href="{{var[page]['url']}}&do=edit">
                            <div class='page-edit-link-div' style='background-image:url("{{var['editImageUrl']}}");'></div>
                        </a>
                    </td>
                </tr>
                {% elseif page == 4 %}
                <div id="fl-notify-morepages" class="notify">
                    {{languageText['thereAre']}} {{totalPages-5}} {{languageText['morePages']}}
                </div>
                {% endif %}
                {% endfor %}
                </tbody>
            </table>
            {% endif %}
        </div>
    </fieldset>
</div>