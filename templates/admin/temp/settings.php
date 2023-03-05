<div class="panel">
	<h4>Данные администратора</h4>
	<form class="edit-admin-form" id="edit_admin" method="POST" action="/">
        <label>Эл. почта</label><br>
        <input type="text" name="email" value=""><br>
        <label>Новый пароль</label><br>
        <input type="password" name="new_pass" value=""><br>
        <label>Новый пароль еще раз</label><br>
        <input type="password" name="new_pass_repeat" value=""><br>
        <label>Текущий пароль</label><br>
        <input type="password" name="curr_pass" value=""><br>

        <input type="hidden" name="id" value="<?=$USER->id?>">
        <input type="hidden" name="action" value="edit-admin">
        <input type="hidden" name="_form" value="edit-admin">
        <button>Сохранить</button>
    </form>
</div>

<script>
	edit_admin.addEventListener('submit', function(e) {
		e.preventDefault();
		editAdmin(edit_admin);
	});
</script>