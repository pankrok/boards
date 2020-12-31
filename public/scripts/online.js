$(function () {
   checkOnlineFunc();
});


function checkOnlineFunc()
{
		var csrfName = $('#csrf_name').val();
		var csrfValue = $('#csrf_value').val();
			
			$.ajax({

				  type: "POST",
				  url: checkOnline,
				  data: { 
				  "csrf_name" : csrfName,
				  "csrf_value" : csrfValue
				  },
				  dataType: 'json',
				  error: function(query){
					console.log(query);
				  },
				  success: function(mydata){					
			
					  setTimeout(function () {
							checkOnlineFunc();
						}, 300000);
				  }
		});		
}