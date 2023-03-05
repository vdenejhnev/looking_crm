<link rel="stylesheet" type="text/css" href="/templates/css/lead-card.css">
<div class="first-pannel">
	<h5>Дублирование оповещений</h5>
 	<label><input type="checkbox" class="checkbox-notification" value="email_notification" data-id="<?= $USER->id; ?>">Эл. почта</label>
 	<label><input type="checkbox" class="checkbox-notification" value="telegram_notification" data-id="<?= $USER->id; ?>">Телеграм</label>
 	<hr>
</div>
<div class="main-panel">
	<? 
		$notifications = Model\Notification::get($USER->id);

		if($notifications != ''):
			foreach ($notifications as $notification):
	?>
	<div class="notification" data-id="<?=$notification['id']?>">

		<? if($notification['type'] == 'reference'): ?>
			<img src="/img/notification1.svg">
		<? else: ?>
			<img src="/img/notification2.svg">
		<? endif; ?>
		<span class="close-cross" data-id="<?=$notification['id']?>">✕</span>
		<? if($notification['type'] == 'reference'): ?>
			<p class="notification-title" >Ваш лид <a href='#' class="lead-button" data-id="<?=$notification['lead']?>">#<?printf("%06s\n", $notification['lead']);?></a> упоминается в другом лиде</p>
		<? else: ?>
			<p class="notification-title" >Добавленный лид <a href='#' class="lead-button" data-id="<?=$notification['lead']?>">#<?printf("%06s\n", $notification['lead']);?></a> совпадает с другим</p>
		<? endif; ?>
		<? $lead = Model\Lead::findOne(['id' => $notification['recurring_lead']]); ?>
		<div class="notification-descr">
			#<?printf("%06s\n", $notification['recurring_lead']);?> от <?=$lead->created_at ?> <?=$lead->name ?> <?=$lead->phone ?>
		</div>
		<div class="notification-intersection">
			<span class="intersection-text">Пересечение</span>
			<span class="intersection-field"><?=$notification['fields']?></span>
		</div>
	</div>
		<? endforeach; ?>
	<? endif; ?>
</div>

<div style="display: none;">
	<?= $this->render('/components/lead-card') ?>
	<?= $this->render('/components/delete-lead-dealer') ?>
</div>


<script src="/templates/front/script/notification.js"></script>