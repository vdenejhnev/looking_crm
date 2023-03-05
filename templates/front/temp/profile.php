<div class="tab-nav">
    <a class="tab-nav-btn curr-tab" href="#" data-tab="tab1">Профиль</a>
    <a class="tab-nav-btn" href="#" data-tab="tab2">Авторизация</a>
</div>

<div class="panel active-tab" id="tab1">
    <label>Компания</label>
    <h3><?=Model\Dealer::findOne(['user.id' => $USER->id])->company->name; ?></h3>
    <form class="edit-admin-form" id="edit_profile" method="POST" action="/">
        <label>Имя и фамилия</label><br>
        <input type="text" name="name" value=""><br>
        <label>Город</label><br>
        <input type="text" name="city" value=""><br>
        <label>Телефон</label><br>
        <input type="text" name="phone" placeholder="+7 (999) 999-99-99" class="short" value=""><br>

        <input type="hidden" name="id" value="<?=$USER->id?>">
        <input type="hidden" name="action" value="edit-profile">
        <input type="hidden" name="_form" value="edit-profile">
        <button>Сохранить</button>
    </form>
</div>

<div class="panel" id="tab2">
    <h4>Данные для авторизации</h4>
	<form class="edit-admin-form" id="edit_profile_auth" method="POST" action="/">
        <label>Эл. почта</label><br>
        <input type="text" name="email" value=""><br>
        <label>Новый пароль</label><br>
        <input type="password" name="new_pass" value=""><br>
        <label>Новый пароль еще раз</label><br>
        <input type="password" name="new_pass_repeat" value=""><br>
        <label>Текущий пароль</label><br>
        <input type="password" name="curr_pass" value=""><br>

        <input type="hidden" name="id" value="<?=$USER->id?>">
        <input type="hidden" name="action" value="edit-profile">
        <input type="hidden" name="_form" value="edit-profile">
        <button>Сохранить</button>
    </form>
</div>

<script>
    $('.tab-nav-btn').click(function() {
        $('.tab-nav-btn').removeClass('curr-tab');
        $(this).addClass('curr-tab');
        let id = $(this).data('tab');
        $('.panel').removeClass('active-tab');
        $('#' + id).addClass('active-tab');      
    });

	edit_profile.addEventListener('submit', function(e) {
		e.preventDefault();
		editProfile(edit_profile);
	});

    edit_profile_auth.addEventListener('submit', function(e) {
        e.preventDefault();
        editProfile(edit_profile_auth);
    });

    $('input[name="phone"]').mask(
        '+7 (999) 999-99-99',
        { autoclear: false }
    );

</script>