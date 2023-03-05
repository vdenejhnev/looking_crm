<link rel="stylesheet" href="/templates/css/dealer-card.css" />

<article class="dealer-card">
    <h1 data-field="name">Андрей Волокитов</h1>

    <dl>
        <dt>Организация</dt>
        <dd data-field="company.name">Строй Индастри</dd>
        <dt>Город</dt>
        <dd data-field="city.name">Москва</dd>
        <dt>Телефон</dt>
        <dd data-field="phone">+7 (999) 999-99-99</dd>
        <dt>Эл. почта</dt>
        <dd data-field="email">volokitov.and@mail.ru</dd>
    </dl>

    <a href="#" class="edit" data-emit="edit">Редактировать</a><br />
    <a href="#" data-emit="change-password">Напомнить пароль</a><br />
    <a href="#" class="disable-button" data-emit="disable">Отключить от системы</a><a class="enable-button" data-emit="disable">Подключить</a><br />
    <a href="#" class="delete" data-emit="delete">Удалить из системы</a>
</article>

<script src="/templates/script/dealer-card.js"></script>
