<section id="chart-wrap">
    <div class="chart-background"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>var CHART_DATA = <?= json_encode($leads_stat) ?>;</script>
    <canvas id="chart" width="400px" height="200px"></canvas>

    <div id="leads-short-stat">
        <span class="total"><?= $leads_count ?></span>
        <span class="uniq-stat">
            <span class="uniq"><?= $leads_uniq ?></span>
            уникальных
        </span>
    </div>
</section>

<h1>Статистика</h1>

<? $years = \Model\Lead::getYearsList(); ?>
<select class="year-select" name="year_select">
    <? foreach ($years as $year): ?>
        <option value="<? print_r($year['year']); ?>" <? if (isset($year['curr_year']) && $year['curr_year'] == 'curr_year'): echo 'selected'; endif;?>><? print_r($year['year']); ?></option>
    <? endforeach; ?>
</select>
<!-- <span id="year-select">2022</span> -->
<a href="" data-href="/cp/xls/statistics" class="xls-link full-stat">Скачать</a>

<section id="panels">
    <div class="panel">
        <div class="title">Активные дилеры</div>
        <div class="leads-year-total total"><?= $dealers_count ?></div>
        <table>
            <tbody>
                <?php foreach ($dealers_stat as $dealer) : ?>                         
                    <? if ($dealer['leads_count'] > 0): ?>
                    <tr>
                        <td><?= $dealer['name'] ?></td>
                        <td><?= $dealer['leads_count'] ?></td>
                    </tr>
                    <? endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="/cp/xls/statistics?table=dealers_stat" class="xls-link">Скачать</a>
        <div class="show-panel-list" data-panel="0">&#709;</div>
    </div>
    <div class="panel">
        <div class="title">Активные компании</div>
        <table>
            <tbody>
                <?php foreach ($companies_stat as $company) : ?>
                    <? if ($company['leads_count'] > 0): ?>
                    <tr>
                        <td><?= $company['name'] ?></td>
                        <td><?= $company['leads_count'] ?></td>
                    </td>
                    <? endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="/cp/xls/statistics?table=companies_stat" class="xls-link">Скачать</a>
        <div class="show-panel-list" data-panel="1">&#709;</div>
    </div>
    <div class="panel">
        <div class="title">За месяц</div>
        <div class="month-stat-section">
            <div class="total"></div>
            <div class="month-select">
                <button class="month-select-btn month-prev"><img src="/img/btn-prev-triangle.png"></button>
                <span class="month"></span>
                <button class="month-select-btn month-next"><img src="/img/btn-next-triangle.png"></button>
            </div>
        </div>
        <table class="dealer-month-stat">
            <tbody></tbody>
        </table>
        <a href="" data-href="/cp/xls/statistics?table=months_dealers_stat" class="xls-link months-dealers-stat">Скачать</a>
        <div class="show-panel-list" data-panel="2">&#709;</div>
    </div>
    <div class="panel">
        <div class="title">Компании за месяц</div>
        <table class="company-month-stat">
            <tbody></tbody>
        </table>
        <a href="" data-href="/cp/xls/statistics?table=months_companies_stat" class="xls-link months-companies-stat">Скачать</a>
        <div class="show-panel-list" data-panel="3">&#709;</div>
    </div>
</section>
