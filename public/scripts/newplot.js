
var editor = new Jodit('#newPlotEditor', {
    autofocus: true,
	height: "280",
	allowResizeX: false,
    allowResizeY: true
});

function postReply(){
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
		$.ajax({
		  type: "POST",
		  url: ajaxUrl,
		  data: { 
		  'topic': 	$('#newPlotTopic').val(),
		  'content': $('#newPlotEditor').val(),
		  'board_id': $('#BoardID').val(),
		  "csrf_name" : csrfName,
		  "csrf_value" : csrfValue
		  
		  },
		  dataType: 'json',
		  error: function(mydata){
			console.log(mydata);
		  },
		  success: function(mydata){					
			$('#csrf_name').val(mydata['csrf']['csrf_name']);
			$('#csrf_value').val(mydata['csrf']['csrf_value']);
			if(mydata.redirect)
				window.location.replace(mydata.redirect);
			if(mydata.warn)
				$('.card-header').after(mydata.warn);
				setTimeout(function(){ $('.alert').fadeOut(); }, 5000);
				setTimeout(function(){ $('.alert').remove(); }, 5600);
		  }
		});	
}


$( "#startnewplot" ).click(function() {
		postReply();
});


 $('#goback').click(function(){
        parent.history.back();
        return false;
  });
