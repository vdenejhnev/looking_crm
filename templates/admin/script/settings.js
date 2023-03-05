function editAdmin(form) {
	$.post(
		'/ajax/admin/settings.php', 
		$(form).serialize(), 
		function(response) {
			response = JSON.parse(response);
			//console.log(response);
			if ('errors' in response && response.errors.length) {
          		for (let item = 0; item < response.errors.length; item++) {
          			$('input[name=' + response.errors[item].field + ']', form).addClass('error');
          			alert(response.errors[item].error);
          		}
        	} else {
        		console.log($(form).serializeArray()[0]['value']);

        		if ($(form).serializeArray()[0]['value'] != '') {
        			alert('Эл. почта изменена');
        		}
        		//document.location.reload();
        	}      	
        }
	);
}
