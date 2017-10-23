{# findologicxmlexport/tpl/admin.tpl for findologicxmlexport plugin #}

<div class="plugin-findologicxmlexport">
    <h1>{{languageText['menu']}}</h1>
    <p>
        {{languageText['youCan']}}<a href="lib/plugins/findologicxmlexport">{{languageText['callExport']}}</a>.
    </p>
    <fieldset style="margin-top: 5em;">
        {% if pagesWithoutTitle %}
        <div class="notify">
            {{languageText['noTitleWarning']}} <br/> {{languageText['noTitleWarningMoreInformation']}}
        </div>
        {% else %}
        <div class="success">
            {{languageText['allPagesHaveATitle']}}
        </div>
        {% endif %}
        <legend>{{languageText['pagesWithoutTitle']}} ({{pagesWithoutTitle|length}})</legend>
        <div class="table">
            {% if pagesWithoutTitle %}
            <table class="table table-condensed">
                <tbody>
                <tr>
                    <th>
                        {{languageText['namespace']}}
                    </th>
                    <th>
                        {{languageText['url']}}
                    </th>
                    <th>
                        {{languageText['lasteditby']}}
                    </th>
                    <th>
                        {{languageText['lastedited']}}
                    </th>
                    <th>
                        {{languageText['edit']}}
                    </th>
                </tr>
                {% set amount = 0 %}
                {% set morePages = 0 %}
                {% for pageWithoutTitle in pagesWithoutTitle %}
                {% if amount < 5 %}
                <tr>
                    <td>
                        {# namespace #}
                        {{pageWithoutTitle}}
                    </td>
                    <td>
                        {# url #}
                        <a target="_blank" href="{{urls[amount]}}">{{urls[amount]}}</a>
                    </td>
                    <td>
                        {# lasteditby #}
                        {{metadata[amount]['last_change']['user']}}
                    </td>
                    <td>
                        {# lastedited #}
                        {{timestamp[amount]}}
                    </td>
                    <td>
                        {# edit #}
                        <a target="_blank" class='editpage' style="color: transparent;" href="{{urls[amount]}}&do=edit">
                            <div class='editpage'
                                 style='background-image:url("{{imageUrl}}");background-repeat: no-repeat;background-size:25px;width: 25px;height: 25px;color: transparent; font-size: 20px;'>
                            </div>
                        </a>
                    </td>
                    {% set amount = amount + 1 %}
                    {% else %}
                    {% set morePages = morePages + 1 %}
                    {% endif %}
                    {% endfor %}
                </tr>
                </tbody>
            </table>
            {% endif %}
            {% if morePages > 0 %}
            <div class="notify">
                {{languageText['thereAre']}} {{morePages}} {{languageText['morePages']}}
            </div>
            {% endif %}
        </div>
    </fieldset>
</div>