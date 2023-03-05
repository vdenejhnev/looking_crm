<form id="login-form" method="POST">
    <div>
        <fieldset>
            <legend>Личный кабинет</legend>
            <label>Эл. почта</label>
            <input type="text" name="email" value="<?= htmlspecialchars($FORM_DATA['email'] ?? '') ?>" placeholder="admin@example.com" />
            <label>Пароль</label>
            <input type="password" name="password" value="" placeholder="123456" />
        </fieldset>

        <input type="hidden" name="action" value="login" />
        <input type="hidden" name="_form" value="login" />
        <button>Вход</button>
        <a href="" class="forget-toggler">Забыл пароль</a>
    </div>
    <div style="display: none;">
        <legend>Забыл пароль</legend>
        <p>Обратитесь к администратору, он сбросит текущий пароль, на эл. почту будет отправлен новый.
        <button class="forget-toggler">Понятно</button>
    </div>
</form>
