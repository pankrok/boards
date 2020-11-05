var editor = new Jodit('#quickReply', {
    autofocus: true,
	height: "280", 
	toolbarAdaptive: false,
	allowResizeX: false,
    allowResizeY: false
});

function postReply(){
	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
		$.ajax({
		  type: "POST",
		  url: urlForReply,
		  data: { 
		  'content': 	$('#quickReply').val(),
		  'plot_id': 	$('#plot').val(),
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
			
			$('#posts').append(mydata['response']);
			setTimeout(function(){ $('.card.mb-3').fadeIn('slow'); }, 500);
			
		  }
		});	
}

function likeit(id){

	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
	$.ajax({
		  type: "POST",
		  url: likePost,
		  data: { 
		  'post_id': 	id,
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
			$('#'+id).before(mydata['likeit']);
			setTimeout(function(){ $('#overlay').fadeIn('slow'); }, 200);
			setTimeout(function(){ $('#overlay').fadeOut('slow'); }, 2500);	
		  }
		});	
	
}

function raport(id){

	
	var csrfName = $('#csrf_name').val();
	var csrfValue = $('#csrf_value').val();
	
	$.ajax({
		  type: "POST",
		  url: " path_for('board.raport.post') ",
		  data: { 
		  'post_id': 	id,
		  'reason': 	$('#ReportReason option:selected"').val(),
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
			$('#'+id).before(mydata['report']);
			setTimeout(function(){ $('#overlay').fadeIn('slow'); }, 200);
			setTimeout(function(){ $('#overlay').fadeOut('slow'); }, 2500);	
		  }
		});	
	
}


$( "#quickReply-btn" ).click(function() {
		postReply();
});

$('#collapseTwo').on('show.bs.collapse', function () {
  $("html, body").animate({ scrollTop: $(document).height() }, 1200);
})

function scrollToAnchor(aid){
   if(aid){
	var aTag = $("#"+aid);
    $('html,body').animate({scrollTop: aTag.offset().top-80},'slow');
	$("#"+aid).animate({opacity: '0'}, 'slow').animate({opacity: '1'}, 'slow');
	}
}
var link = window.location+'';
var data = link.split("#");


$(function() {
   setTimeout(function(){scrollToAnchor(data[1])}, 500);
   
    $("a.likeit").click(function(event) {
        likeit(event.target.id);
    });  
	$("a.raport").click(function(event) {
		
		
		$('#RaportModal').modal('show');
    }); 
	$("a.post-edit").click(function(event) {
		var len = (event.target.id).length
		aedit.setEditorValue($('#content'+(event.target.id).substring(9,len)).html());
		$('#editPlotID').val(event.target.id);		
		$('#editPost').modal('show');
		
		$( "#post-edit-btn" ).click(function() {
			editPost(event.target.id, len);
		});	
		
    }); 
});