{# findologicxmlexport/tpl/admin.tpl for findologicxmlexport plugin #}

<div class="plugin-findologicxmlexport">
    <h1>{{menu}}</h1>
    <p>
        {{youCan}}<a href="lib/plugins/findologicxmlexport">{{callExport}}</a>.
    </p>
    <fieldset style="margin-top: 5em;">
        {% if pagesWithoutTitle %}
            <div class="notify">
                {{noTitleWarning}} <br /> {{noTitleWarningMore}}
            </div>
        {% else %}
            <div class="success">
                {{allPagesHaveATitle}}
            </div>
        {% endif %}
        <legend>{{pagesWithoutTitleText}} ({{pagesWithoutTitle|length}})</legend>
        <div class="table">
            {% if pagesWithoutTitle %}
            <table class="table table-condensed">
                <tbody>
                <tr>
                    <th>
                        {{namespace}}
                    </th>
                    <th>
                        {{url}}
                    </th>
                    <th>
                        {{lasteditby}}
                    </th>
                    <th>
                        {{lastedited}}
                    </th>
                    <th>
                        {{edit}}
                    </th>
                </tr>
                {% set amount = 0 %}
                {% set morePages = 0 %}
                {% for pageWithoutTitle in pagesWithoutTitle %}
                    {% if amount < 5 %}
                    <tr>
                        <td>
                            {{pageWithoutTitle}}
                        </td>
                        <td>
                            <a target="_blank" href="{{urls[amount]}}">{{urls[amount]}}</a>
                        </td>
                        <td>
                            {{metadata[amount]['last_change']['user']}}
                        </td>
                        <td>
                            {{timestamp[amount]}}
                        </td>
                        <td>
                            <a target="_blank" class='editpage' style="color: transparent;" href="{{urls[amount]}}&do=edit">
                                <div class='editpage' style='background-image:url("{{imageUrl}}");background-repeat: no-repeat;background-size:25px;width: 25px;height: 25px;color: transparent; font-size: 20px;'>
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
                    {{thereAre}} {{morePages}} {{morePagesText}}
                </div>
            {% endif %}
        </div>
    </fieldset>
</div>