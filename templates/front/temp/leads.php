<a href="/xls/leads" id="dealers-xls" class="xls-link-white">Скачать</a>
<button id="add-lead" class="data-table-add-button">+ новый лид</button>

<div id="leads-list-wrap" class="data-table-wrap">
    <div class="data-table-filter leads-range">
        <!-- <input type="text" name="leads_from" placeholder="__.__.____" class="date" /> -->
        <input type="date" name="leads_from" value="<?= \Model\Lead::getFirstYearDate(); ?>" max="<?= \Model\Lead::getTodayDate(); ?>">
        &mdash;
        <input type="date" name="leads_to" value="<?= \Model\Lead::getTodayDate(); ?>" max="<?= \Model\Lead::getTodayDate(); ?>">
        <!-- <input type="text" name="leads_to" placeholder="__.__.____" class="date" /> -->
    </div>
    <table id="leads-list" class="data-table">
        <thead class="data-table-filter">
            <th>
                <input type="text" name="dealer_id" value="<?=$USER->id; ?>" hidden />
                <span class="custom-select filter-select filter-status">
                    <span class="caption">Все статусы</span>
                    <ul class="list">
                        <?php foreach (\Model\Lead::STATUSES as $key => $status) : ?>
                            <? if($key != 'deleted'): ?>
                            <li data-id="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($status['name']) ?></li>
                            <? endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </th>
            <th><input type="text" name="id" placeholder="Номер" /></th>
            <th><input type="text" name="inn" placeholder="ИНН" /></th>
            <th><input type="text" name="company_name" placeholder="Организация" /></th>
            <th><input type="text" name="phone" placeholder="Телефон" /></th>
            <th><input type="text" name="email" placeholder="Эл. почта" /></th>
            <th><span class="filter-select filter-sort date-sort" data-sort="">Добавлен</span></th>
            <th><span class="filter-select filter-sort inn-sort" data-sort="">ИНН</span></th>
        </thead>
        <tbody>
            
        </tbody>
    </table>

    <div id="dealers-list-pager" class="pager clearfix">
        <span class="custom-select">
            <span class="caption">100</span>
            <ul class="list">
                <li data-id="20">20</li>
                <li data-id="50">50</li>
                <li data-id="100">100</li>
            </ul>
        </span>
        <ul class="pages"></ul>
        <div class="hint"></div>
    </div>

   <!-- <ul id="leads-list-pager" class="pager"></ul>-->
</div>

<div style="display: none;">
    <form class="add-lead-form">
        <table>
            <tbody>
                <tr>
                    <td><label>ИНН</label></td>
                    <td><input type="text" name="inn" value="" /><button class="search-company-btn"></button></td>
                </tr>
                <tr>
                    <td><label>Организация</label></td>
                    <td><input type="text" name="company_name" readonly/></td>
                </tr>
                <tr>
                    <td><label>Город</label></td>
                    <td><input type="text" name="city" value="" class="short" /></td>
                </tr>
                <tr>
                    <td><label>Телефон</label></td>
                    <td><input type="text" name="phone1" value="" placeholder="+7 (999) 999-99-99" class="short" /> <div class="open-field" data-type="open" data-field="phone2" data-open-field-item="phone1">+</div></td>
                </tr>
                <tr data-action-field="phone2" style="display: none;">
                    <td><label>Телефон 2</label></td>
                    <td><input type="text" name="phone2" value="" placeholder="+7 (999) 999-99-99" class="short" /> <div class="open-field" data-type="open" data-field="phone3" data-open-field-item="phone2">+</div></td>
                </tr>
                <tr data-action-field="phone3" style="display: none;">
                    <td><label>Телефон 3</label></td>
                    <td><input type="text" name="phone3" value="" placeholder="+7 (999) 999-99-99" class="short" /></td>
                </tr>
                <tr>
                    <td><label>Эл. почта</label></td>
                    <td><input type="text" name="email1" value="" class="short" /> <div class="open-field" data-type="open" data-field="email2" data-field-type="email" data-open-field-item="email1">+</div></td>
                </tr>
                <tr data-action-field="email2" style="display: none;">
                    <td><label>Эл. почта 2</label></td>
                    <td><input type="text" name="email2" value="" class="short" /> <div class="open-field" data-type="open" data-field="email3" data-field-type="email" data-open-field-item="email2">+</div></td>
                </tr>
                <tr data-action-field="email3" style="display: none;">
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
        <input type="hidden" name="user_id" value="<?= $USER->id; ?>" />
        <input type="hidden" name="action" value="add-lead" />
        <input type="hidden" name="_form" value="add-lead" />
        <button>Сохранить</button>
    </form>

    <!-- <div class="lead-card">
        <span class="lead-status"></span>
        <p class="lead-created-date"></p>
        <a href="#" class="delete">Удалить</a>
        <hr />
        <table>
            <tbody>
                <tr>
                    <td>ИНН</td><td class="lead-inn"></td>
                    <td rowspan="7">
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td>ИНН добавлен</td><td class="lead-inn-added-at"></td>
                </tr>
                <tr>
                    <td>Огранизация</td><td class="lead-company-name"></td>
                </tr>
                <tr>
                    <td>Город</td><td class="lead-city"></td>
                </tr>
                <tr>
                    <td>Телефон</td><td class="lead-phone"></td>
                </tr>
                <tr>
                    <td>Эл. почта</td><td class="lead-email"></td>
                </tr>
                <tr>
                    <td>Имя</td><td class="lead-name"></td>
                </tr>
                <tr>
                    <td>Комментарий</td><td colspan="2"><textarea class="lead-comment" readonly></textarea></td>
                </tr>
            </tbody>
        </table>
        <hr />
    </div> -->
    <?= $this->render('/components/lead-card') ?>
    <?= $this->render('/components/delete-lead-dealer') ?>
    <?= $this->render('/components/edit-lead') ?>
    <?= $this->render('/components/reference-card') ?>
    <?= $this->render('/components/coincidence-card') ?>
</div>
<script>
    $('.filter-sort').click(function() {
        if ($(this).data('sort') == 'ASC' || $(this).data('sort') == '') {
            $(this).addClass('filter-sort-rotate');
            $(this).data('sort', 'DESC');
        } else {
            $(this).removeClass('filter-sort-rotate');
            $(this).data('sort', 'ASC');
        }
    });
</script>