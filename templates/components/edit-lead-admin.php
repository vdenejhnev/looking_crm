<link rel="stylesheet" href="/templates/css/edit-lead-admin.css" />

    <form class="edit-lead-form">
       

        <article class="edit-lead-card">
            <header>
                <h1>Лид <span class="id" data-field="id"></span></h1>
                <input type="text" name="lead_id" hidden>
                <input type="text" name="action" value="edit-lead" hidden>
                <span class="status" data-field="status"></span>
                <div class="lead-header-info">
                    <div class="lead-header-info-col">
                        <!-- <span class="datetime" data-field="created_at"></span> -->
                        <input type="date" data-field="lead-date" name="lead_date">
                        <div class="lead-time-section">
                            <input type="time" data-field="lead-time" name="lead_time">
                            <span class="timezone-name">мск</span>
                        </div>
                    </div>
                    <div class="lead-header-info-col">
                        <span class="dealer" data-field="dealer-name"></span>
                        <span class="dealer-company" data-field="dealer-company"></span>
                    </div>
                </div>
            </header>

            <hr />

            <table class="lead-info">
                <colgroup>
                    <col width="119" />
                    <col width="171" />
                    <col />
                </colgroup>
                <tbody>
                    <tr><td>ИНН</td><td><input class="short-input" data-field="inn" name="inn"></td></tr>
                    <tr><td>ИНН добавлен</td><td><input type="date" data-field="inn-date" name="inn_date"><input type="time" data-field="inn-time" name="inn_time"><span class="timezone-name">мск</span></td></tr>
                    <tr><td>Организация</td><td><input colspan="2" data-field="company_name" name="company_name"></td></tr>
                    <tr><td>Город</td><td><input colspan="2" data-field="city" name="city"></td></tr>
                    <tr><td>Телефон</td><td><input class="short-input" colspan="2" data-field="phone1" placeholder="+7 (999) 999-99-99" name="phone"></td></tr>
                    <!-- <tr><td>Телефон 2</td><td><input class="short-input" colspan="2" data-field="phone2" placeholder="+7 (999) 999-99-99" name="phone2"></td></tr>
                    <tr><td>Телефон 3</td><td><input class="short-input" colspan="2" data-field="phone3" placeholder="+7 (999) 999-99-99" name="phone3"></td></tr> -->
                    <tr><td>Эл. почта</td><td><input colspan="2" data-field="email" name="email"></td></tr>
                    <!-- <tr><td>Эл. почта 2</td><td><input colspan="2" data-field="email2" name="email2"></td></tr>
                    <tr><td>Эл. почта 3</td><td><input colspan="2" data-field="email3" name="email3"></td></tr> -->
                    <tr><td>Имя</td><td><input data-field="name" name="name"></td></tr>
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
                <tbody>
                    <tr><td>Пересечения</td><td>#000384</td><td>ИНН</td></tr>
                    <tr><td>&nbsp;</td><td>#000267</td><td>Телефон, эл. почта</td></tr>
                    <tr><td>&nbsp;</td><td>#000267</td><td>ИНН, эл. почта</td></tr>
                </tbody>
            </table>

            <input type="hidden" name="action" value="save-dealer" />
            <input type="hidden" name="_form" value="save-dealer" />

            <button class="save save-edit-lead">Сохранить</button>
        </article>
    </form>