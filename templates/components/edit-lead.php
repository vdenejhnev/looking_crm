<link rel="stylesheet" href="/templates/css/edit-lead.css" />

<form class="edit-lead-form">
    <article class="edit-lead">
        <header>
            <h1>Лид <span class="id" data-field="id"></span></h1>
            <span class="status" data-field="status"></span>
            <span class="datetime" data-field="created_at"></span>
            <span class="dealer" data-field="dealer-name"></span>
            <span class="dealer-company" data-field="dealer-company"></span>
        </header>
        <hr />
        <table>
            <tbody>
                <tr>
                    <td><label>ИНН</label></td>
                    <td class="inn-field"><input type="text" name="inn" value="" /><button class="search-company-btn"></button></td>
                </tr>
                <tr>
                    <td><label>Организация</label></td>
                    <td class="company-name-field"><input type="text" name="company_name" value="" readonly/></td>
                </tr>
                <tr>
                    <td><label>Город</label></td>
                    <td><input type="text" name="city" value="" class="short" /></td>
                </tr>
                <tr>
                    <td><label>Телефон</label></td>
                    <td><input type="text" name="phone1" value="" placeholder="+7 (999) 999-99-99" class="short" /> <div class="open-field-text" data-type="open" data-form-field="phone2" data-open-field-item="phone1">+</div></td>
                </tr>
                <tr data-action-form-field="phone2" style="display: none;">
                    <td><label>Телефон 2</label></td>
                    <td><input type="text" name="phone2" value="" placeholder="+7 (999) 999-99-99" class="short" /> <div class="open-field-text" data-type="open" data-form-field="phone3" data-open-field-item="phone2">+</div></td>
                </tr>
                <tr data-action-form-field="phone3" style="display: none;">
                    <td><label>Телефон 3</label></td>
                    <td><input type="text" name="phone3" value="" placeholder="+7 (999) 999-99-99" class="short" /></td>
                </tr>
                <tr>
                    <td><label>Эл. почта</label></td>
                    <td><input type="text" name="email1" value="" class="short" /> <div class="open-field-text" data-type="open" data-form-field="email2" data-field-type="email" data-open-field-item="email1">+</div></td>
                </tr>
                <tr data-action-form-field="email2" style="display: none;">
                    <td><label>Эл. почта 2</label></td>
                    <td><input type="text" name="email2" value="" class="short" /> <div class="open-field-text" data-type="open" data-form-field="email3" data-field-type="email" data-open-field-item="email2">+</div></td>
                </tr>
                <tr data-action-form-field="email3" style="display: none;">
                    <td><label>Эл. почта 3 </label></td>
                    <td><input type="text" name="email3" value="" class="short" /></td>
                </tr>
                <tr>
                    <td><label>Имя</label></td>
                    <td><input type="text" name="name" value="" /></td>
                </tr>
                <tr>
                    <td><label>Комментарий</label></td>
                    <td><textarea name="comment"></textarea></td>
                </tr>
            </tbody>
        </table>

        <hr />

        <table class="collisions">
            <colgroup>
                <col width="116" />
                <col width="89" />
                <col />
            </colgroup>
            <tbody>
                <tr><td>Пересечения</td><td>#000384</td><td>ИНН</td></tr>
                <tr><td>&nbsp;</td><td>#000267</td><td>Телефон, эл. почта</td></tr>
                <tr><td>&nbsp;</td><td>#000267</td><td>ИНН, эл. почта</td></tr>
            </tbody>
        </table>

        <input type="hidden" name="action" value="edit-lead" />
        <input type="hidden" name="_form" value="edit-lead" />

        <button class="save save-edit-lead">Сохранить</button>
    </article>
</form>