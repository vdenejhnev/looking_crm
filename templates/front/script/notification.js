/*const LEAD_STATUSES = {
    new: {
        "name": "Переговоры",
        "color": "#FFD954"
    },
    single: {
        "name": "Единственный",
        "color": "#24FF00"
    },
    first: {
        "name": "Первый",
        "color": "#24FF00"
    },
    second: {
        "name": "Второй",
        "color": "#FF9254"
    },
    third: {
        "name": "3-й и более",
        "color": "#FF5C00"
    },
    trash: {
        "name": "В топке",
        "color": "#CDCDCD"
    },
    deleted: {
        "name": "Удалён",
        "color": "#000000"
    },
    done: {
        "name": "Завершено",
        "color": "#EB00FF"
    }
}*/

$.post(
	'/ajax/front/notification.php', 
	{
		action: 'get_status_channel',
		dealer_id : $('.checkbox-notification[value=email_notification]').data('id'),
		channel: 'email_notification'
	}, 
	function(response) {
		if (response == 1) {
			$('.checkbox-notification[value=email_notification]').prop('checked', 'true');
		}
   }
);

$.post(
	'/ajax/front/notification.php', 
	{
		action: 'get_status_channel',
		dealer_id : $('.checkbox-notification[value=telegram_notification]').data('id'),
		channel: 'telegram_notification'
	}, 
	function(response) {
		if (response == 1) {
			$('.checkbox-notification[value=telegram_notification]').prop('checked', 'true');
		}
   }
);

$('.close-cross').click(function() {
	var notification_id = $(this).data('id');
	$.post(
		'/ajax/front/notification.php', 
		{
			action: 'close_notification',
			id: notification_id
		}, 
		function(response) {
			if (response != '') {
				$('.notification[data-id=' + notification_id + ']').remove();
			}
			//document.location.reload();
     }
	);
});

$('.checkbox-notification').click(function() {
	$.post(
		'/ajax/front/notification.php', 
		{
			action: 'change_channel',
			dealer_id: $(this).data('id'),
			channel: $(this).val()
		}, 
		function(response) {}
	);
});

function editLead(Lead) {
	$('.edit').remove();
	$('td[data-field=city]').remove();
	$('td[data-field=phone]').remove();
	$('td[data-field=email]').remove();
	$('td[data-field=name]').remove();
	$('textarea[name=comment]').removeAttr('disabled');

	$('.edit-field').append('<td><input class="edit-input" name="" value=""></td>');

	$('.close-cross').click(function() {
		$('.edit-input').remove();
		$('textarea[name=comment]').attr('disabled');
	});
}

function deleteLead (lead) {
  const popupDeleteLead = new Popup({
    content: $('.delete-lead-open')
 	});

  $('[data-field="id"]').text(formatLeadId(lead.id));
  popupDeleteLead.show();

  $('.close-cross').on('click', function() {
    $('.popup-root').remove();
  });

  $('.button-success').on('click', function() {
    $('.popup-root').remove();
  });

  $('.button-danger').on('click', function() {
    $.post(
      '/ajax/front/leads.php',
      {
        action: 'delete-lead',
        id: lead.id
      },
      function() {
      	$.post(
      		'/ajax/front/notification.php',
      		{
      		  action: 'delete_notification',
      		  options: {
      		  	0: ['lead', lead.id]
      		  }
      		},
      		function(response) {
      			document.location.reload();
      		},
      		'json'
      	);
      },
      'json'
    );
    $('.popup-root').remove();
  });
};
/*
function deleteLead(lead) {
	const popup = new Popup({
		content: '<div class="delete-lead-block"><h1>Удаляем лид #' +  lead.id + '</h1><p>Подтвердите удаление</p><button class="cancel-button">Оставить</button><button class="delete-button">Удалить</button></div>'
	});
	popup.show();

	$('.cancel-button').click(function() {
		popup.close();
	});

	$('.delete-button').click(function() {
		$.post('/ajax/front/leads.php', {action: 'delete-lead', id: lead.id}, function(response) {
			document.location.reload();
		}, 'json');
	});
}*/

function formatLeadId (id) {
  return '#' + ('0'.repeat(6 - ('' + id).length)) + id;
};

$('.lead-button').click(function() {
	let lead = $(this).data('id');

	$.post('/ajax/front/leads.php', {action: 'get-lead', id: lead}, response => {	

      const popup = new Popup({
        beforeAppend: (root, content) => {
          $(content).leadCard().leadCard('fill', response.lead);
          content.on('edit.dealer', event => {
            editLead(response.lead);
          });
          content.on('delete.dealer', event => {
            popup.close();
            deleteLead(response.lead);
          });
        },
        content: $('.lead-card')
      });

      popup.show();

      $('.done-lead').css('display', 'none');
      $('.edit').css('display', 'none');
      $('.lead-card-save_btn').css('background', '#CBCBCB');
      $('.status', $('.lead-card')).html('<div class="status-color" style="background-color:' + LEAD_STATUSES[response.lead.status]?.color + ';"></div>' + LEAD_STATUSES[response.lead.status]?.name);
           
      if (response.lead.phones.length != 0) {
        $('td[data-field="phone"]', $('.lead-card')).text(response.lead.phones[0].value);
      } else {
        $('td[data-field="phone"]', $('.lead-card')).text('');
      }
           
      if (response.lead.emails.length != 0) {
        $('td[data-field="email"]', $('.lead-card')).text(response.lead.emails[0].value);
      } else {
        $('td[data-field="email"]', $('.lead-card')).text('');
      }

      $('[data-field="comment"]').on('input', function() {
        lead_comment = $(this).val();
        $('.lead-card-save_btn').css('background', '#E86C42');
      });

      $('.lead-card-save_btn').on('click', function() {
        $.post(
          '/ajax/front/leads.php',
          {
            action: 'update-lead-comment',
            id: lead,
            comment: lead_comment,
          },

          function(response) {},
          'json'
        );

        $('.lead-card-save_btn').css('background', '#CBCBCB');
      });
    }, 'json');
});

