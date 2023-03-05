<a href="/cp/xls/leads" id="dealers-xls" class="xls-link-white">Скачать</a>

<div class="data-table-wrap">
    <div class="data-table-filter leads-range">
        <!-- <input type="text" name="leads_from" placeholder="__.__.____" class="date" /> -->
        <input type="date" name="leads_from" value="<?= \Model\Lead::getFirstYearDate(); ?>" max="<?= \Model\Lead::getTodayDate(); ?>">
        &mdash;
        <input type="date" name="leads_to" value="<?= \Model\Lead::getTodayDate(); ?>" max="<?= \Model\Lead::getTodayDate(); ?>">
        <!-- <input type="text" name="leads_to" placeholder="__.__.____" class="date" /> -->
    </div>
    <table class="data-table">
        <thead class="data-table-filter">
            <th>
                <span class="custom-select filter-select filter-status">
                    <span class="caption">Все статусы</span>
                    <ul class="list">
                        <?php foreach (\Model\Lead::STATUSES as $key => $status) : ?>
                            <li data-id="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($status['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </th>
            <th><input type="text" name="id" placeholder="Номер" /></th>
            <th><input type="text" name="inn" placeholder="ИНН" /></th>
            <th><input type="text" name="company_name" placeholder="Организация" /></th>
            <th><input type="text" name="phone" placeholder="Телефон" /></th>
            <th><input type="text" name="email" placeholder="Эл. почта" /></th>
            <th><span class="filter-select filter-sort date-sort" data-sort="" data-emit="date-sort">Добавлен</span></th>
            <th><span class="filter-select filter-sort inn-sort" data-sort="">ИНН</span></th>
            <th><input type="text" name="dealer" placeholder="Дилер" /></th>
            <th>
                <span class="custom-select filter-select filter-dealer-company">
                    <span class="caption">Орг-ция дилера</span>
                    <ul class="list">
                        <?php foreach (\Model\Company::find(null, ['order' => 'name']) as $company) : ?>
                            <li data-id="<?= htmlspecialchars($company->id) ?>"><?= htmlspecialchars($company->name) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </th>
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
</div>

<div style="display: none;">
    <?= $this->render('/components/edit-lead-admin'); ?>
    <?= $this->render('/components/lead-card'); ?>
    <?= $this->render('/components/delete-lead'); ?>
    <?= $this->render('/components/done-lead'); ?>
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

    /*$('input[type=date]').on('change', function(){
        console.log($(this).val());
    });*/
</script>