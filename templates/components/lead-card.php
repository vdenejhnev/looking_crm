<link rel="stylesheet" href="/templates/css/lead-card.css" />

<article class="lead-card">
    <header>
        <h1>Лид <span class="id" data-field="id"></span></h1>
        <span class="status" data-field="status"></span>
        <div class="lead-header-info">
            <div class="lead-header-info-col">
                <span class="datetime" data-field="created_at"></span>
            </div>
            <div class="lead-header-info-col">
                <a class="dealer-filter-name" href="https://lonking-crm.ru/cp/dealers"><span class="dealer" data-field="dealer-name"></span></a>
                <a class="dealer-filter-company" href="https://lonking-crm.ru/cp/dealers"><span class="dealer-company" data-field="dealer-company"></span></a>
            </div>
        </div>
        <a href="#" class="delete" data-emit="delete">Удалить</a>
    </header>

    <hr />

    <table class="lead-info">
        <colgroup>
            <col width="119" />
            <col width="171" />
            <col />
        </colgroup>
        <tbody>
            <tr>
                <td>ИНН</td><td data-field="inn"></td>

                <td><a href="#" class="edit" data-emit="edit" data-lead="">Редактировать</a></td>
                <td><a href="#" class="done-lead" data-emit="done_lead">Завершить</a></td>
            </tr>
            <tr><td>ИНН добавлен</td><td data-field="inn_added_at"></td></tr>
            <tr><td>Организация</td><td colspan="2" data-field="company_name"></td></tr>
            <tr class="edit-field"><td>Город</td><td colspan="2" data-field="city"></td></tr>
            <tr class="edit-field"><td>Телефон</td><td colspan="2" data-field="phone"></td></tr>
            <tr class="edit-field"><td>Эл. почта</td><td colspan="2" data-field="email"></td></tr>
            <tr class="edit-field"><td>Имя</td><td data-field="name"></td></tr>
            <tr><td>Комментарий</td><td><textarea name="comment" data-field="comment"></textarea></td></tr>
        </tbody>
    </table>

    <hr />

    <table class="collisions">
        <colgroup>
            <col width="116" />
            <col width="89" />
            <col />
        </colgroup>
        <tbody></tbody>
    </table>

    <button class="save lead-card-save_btn" data-disabled="true">Сохранить</button>
</article>

<script src="/templates/script/lead-card.js"></script>
