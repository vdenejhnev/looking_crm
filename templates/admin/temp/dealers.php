<a href="/cp/xls/dealers" id="dealers-xls" class="xls-link-white">Скачать</a>

<button id="add-dealer" class="data-table-add-button">+ новый дилер</button>

<div id="dealers-list-wrap" class="data-table-wrap">
    <div class="data-table-filter leads-range">
        <!-- <input type="text" name="leads_from" placeholder="__.__.____" class="date" /> -->
        <input type="date" name="leads_from" value="<?= \Model\Lead::getFirstYearDate(); ?>" max="<?= \Model\Lead::getTodayDate(); ?>">
        &mdash;
        <input type="date" name="leads_to" value="<?= \Model\Lead::getTodayDate(); ?>" max="<?= \Model\Lead::getTodayDate(); ?>">
        <!-- <input type="text" name="leads_to" placeholder="__.__.____" class="date" /> -->
    </div>
    <input type="text" class="dealers-count" value="<?=Model\Dealer::get_count(); ?>" hidden/>
    <table id="dealers-list" class="data-table">
        <thead class="data-table-filter">
            <th><input type="text" name="name" placeholder="Имя и фамилия" value="<? if(isset($_GET['filter_name'])){ echo $_GET['filter_name']; }?>" /></th>
            <th>
                <span class="custom-select filter-select filter-company" data-val="<? if(isset($_GET['filter_company'])){ echo $_GET['filter_company']; }?>">
                    <span class="caption">Организация</span>
                    <ul class="list">
                        <li>&lt;Все&gt;</li>
                        <?php foreach ($companies as $company) : ?>
                            <li data-id="<?= htmlspecialchars($company->id) ?>"><?= htmlspecialchars($company->name) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </th>
            <th><input type="text" name="city" placeholder="Город" /></th>
            <th><input type="text" id="filter_phone" name="phone" placeholder="Телефон" /></th>
            <th><input type="text" name="email" placeholder="Эл. почта" /></th>
            <th><span class="filter-select filter-sort">Лидов</span></th>
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

<? $companies = Model\Company::find(); ?>
<? $cities = Model\City::get_all(); ?>

<div style="display: none;">
    <form class="add-dealer-form">
        <label>Организация</label><br />
        <select class="company-select" name="company_name">
            <option></option>
            <? foreach ($companies as $company): ?>
            <option value="<?= $company->name; ?>"><?= $company->name; ?></option>
            <? endforeach; ?>              
        </select>
        <br />
        <label>Имя и фамилия</label><br />
        <input type="text" name="name" value="" /><br />
        <label>Город</label><br />
        <select name="city" class="city-select short">
            <option></option>
            <? foreach ($cities as $city): ?>
            <option value="<?= $city->name; ?>"><?= $city->name; ?></option>
            <? endforeach; ?>         
        </select>
        <label>Телефон</label><br />
        <input type="text" name="phone" value="" placeholder="+7 (999) 999-99-99" class="short" /><br />
        <label>Эл. почта</label><br />
        <input type="text" name="email" value="" class="short" /><br />

        <input type="hidden" name="id" value="" />
        <input type="hidden" name="action" value="save-dealer" />
        <input type="hidden" name="_form" value="save-dealer" />
        <button>Добавить</button>
    </form>
    <?= $this->render('/components/dealer-card') ?>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    
  /*  $(document).ready(function() {
        $('.company_select').select2({
            tags: true
        });
    });*/
    $('.company-select').click(function() {
        $(this).select2({
            tags: true
        });
    });

    $('.city-select').click(function() {
        $(this).select2({
            tags: true
        });
    });
    
</script>