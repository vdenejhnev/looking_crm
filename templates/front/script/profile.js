function editProfile(form) {
	$.post(
		'/ajax/front/profile.php', 
		$(form).serialize(), 
		function(response) {
			response = JSON.parse(response);
			//console.log(typeof response)
			if ((typeof response == 'object') && 'errors' in response && response.errors.length) {
          		for (let item = 0; item < response.errors.length; item++) {
          			$('input[name=' + response.errors[item].field + ']', form).addClass('error');
          			alert(response.errors[item].error);
          		}
        	} else {
        		document.location.reload();
        	}      	
        }
	);
}

