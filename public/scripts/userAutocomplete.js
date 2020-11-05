function hintUser(){
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	if($('#autocomplete-dynamic-users').val()){
		$.ajax({
		  type: "POST",
		  url: urlFor,
		  data: { 
		  recommended: $('#autocomplete-dynamic-users').val(),
						  "csrf_name" : csrfName,
						  "csrf_value" : csrfValue
		  
		  },
		  dataType: 'json',
		  error: function(query){
			console.log(query);
		  },
		  success: function(mydata){					
			
			$('#csrf_name').val(mydata['csrf']['csrf_name']);
			$('#csrf_value').val(mydata['csrf']['csrf_value']);
			var ret = mydata['username']
			
			 var search = $.map(ret, function (user) { return { value: user, data: { category: 'Users' }}; });
			
			    $('#autocomplete-dynamic-users').autocomplete({
					lookup: search
				});

		  }
		});
	}
}

function captcha(){
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();

		$.ajax({
		  type: "POST",
		  url: captchaRef,
		  data: { 
			"csrf_name" : csrfName,
			"csrf_value" : csrfValue
		  },
		  dataType: 'json',
		  error: function(query){
			console.log(query);
		  },
		  success: function(mydata){					
			
			$('#csrf_name').val(mydata['csrf']['csrf_name']);
			$('#csrf_value').val(mydata['csrf']['csrf_value']);
			$('#captchaImg').fadeOut().remove();
			$('#captcha').append(mydata['captcha']);
			$('#captchaImg').fadeIn();
		  }
		});

}		
$(document).ready(function(){
	
	$( "#captcha-ref-btn" ).click(function() {
		captcha();
	});
	
	
	$('#autocomplete-dynamic-users').on('input', function(){ 
		
		if($('#autocomplete-dynamic-users').val().length == 2){
			hintUser();
		}	
	}); 


}); 

