function scrollDown()
{
	setTimeout(function () {
		var height = 0;
		$('div li').each(function(i, value){
			height += parseInt($(this).height());
		});

		height += '';

		$('div.chatbox').animate({scrollTop: height});
	}, 500);
}


function chatboxPrinter(item) {
  $('#shouts').prepend(item);
  $('.list-group-item').fadeIn("slow");
} 

function chatboxNewsPrinter(item) {
  $('#shouts').append(item);
  $('.list-group-item').fadeIn("slow");
} 

function loadMoreShouts(offset){
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
		$.ajax({
		  type: "POST",
		  url: urlForLoadMessages,
		  data: { 
		  'offset': offset,
		  "csrf_name" : csrfName,
		  "csrf_value" : csrfValue
		  
		  },
		  dataType: 'json',
		  error: function(){
			console.log(query);
		  },
		  success: function(mydata){					
			
			$('#csrf_name').val(mydata['csrf']['csrf_name']);
			$('#csrf_value').val(mydata['csrf']['csrf_value']);
			if(mydata['chatbox'] != 'no more shouts'){
				mydata['chatbox'].reverse();
				mydata['chatbox'].forEach( chatboxPrinter ) ;
			}
		  }
		});	
	
}

function postShout(){
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
		$.ajax({
		  type: "POST",
		  url: urlForChatbox,
		  data: { 
		  'shout': 	$('#shout-content').val(),
		  "csrf_name" : csrfName,
		  "csrf_value" : csrfValue
		  
		  },
		  dataType: 'json',
		  error: function(){
			console.log(query);
		  },
		  success: function(mydata){					
			
			$('#csrf_name').val(mydata['csrf']['csrf_name']);
			$('#csrf_value').val(mydata['csrf']['csrf_value']);
			$('#shouts').append(mydata['shout']);
			$('#shout-content').attr('value', '');  $('#shout-content').val(''); 
			scrollDown();
		  }
		});	
}

function getNewMessages(){
	
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
		$.ajax({
		  type: "POST",
		  url: urlForChatboxNew,
		  data: { 
		  'lastShout': $('.chatbox-li').last().val(),
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
			if(mydata['chatbox'][0] != 'no new shouts'){
				mydata['chatbox'].forEach( chatboxNewsPrinter ) ;
				scrollDown();
			}
			setTimeout(function (){getNewMessages();}, 5000);
		  }
		});	
	
}

$(document).ready(function(){ 

	$( "#button-chatbox" ).click(function() {
		postShout();
	});
	
	$('#shout-content').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)  // the enter key code
		  {
			$('#button-chatbox').click();
			return false;  
		  }
		});  
	
	scrollDown()
	
	var offset = 0;
	
	$(".chatbox.card-body").scroll(function() {
		var $this = $(this);
		
			if ($this.scrollTop() == 0) {
				offset++;
				loadMoreShouts(offset);
				if($('#scrollHere').length){
				setTimeout(function () {
					$('div.chatbox').animate({
						scrollTop: $("#scrollHere").offset().top},				
						"fast");
					}, 250);
					$('#scrollHere').remove();
				}
			}
	});
	getNewMessages();
});


    