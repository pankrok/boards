$(function() {
	
		$.ajax({
			url: path, 
			success: function(result){
				
				result = JSON.parse(result);	
				if(result.status == "boards is updated")
				{
					$("#up-content").html(result.status);
				}
				else if(result.status == "Update Error!")
				{
					$("#up-content").html(result.message.data).addClass(result.message.type).fadeIn();
				}
				else
				{
					$("#up-btn-span").fadeIn();					
					
				}
			}});
	
		$('#update-btn').click(function(){
			update();	
			$('#updateModal').modal('show');
		});
	
});


function update()
{
		$.ajax({
			url: path, 
			success: function(result){
				
				result = JSON.parse(result);
				if(result.status == "boards is updated" || result.status == "Update Error!")
				{
					if(result.message)
					{
						$("#message-content").html(result.message.data).addClass(result.message.type).fadeIn();
					}
					
					$("#update-bar").attr('aria-valuenow', '100').css('width', '100%');
					setTimeout(function () { 
						$('#updateModal').modal('hide');
					}, 500);
					setTimeout(function () { 
						location.reload();
					}, 1000);
				}
				else
				{
					if(result.status == "update start")
					{
						$("#current-update").html(result.status);
						$("#update-bar").attr('aria-valuenow', '10').css('width', '10%');
					}
					
					if(typeof(result.FileUpdate) !== 'undefined')
					{
						$("#current-update").html("files update");
						$("#update-bar").attr('aria-valuenow', '25').css('width', '25%');
					}
					
					if(result.status == "finish")
					{
						$("#current-update").html(result.status);
						$("#update-bar").attr('aria-valuenow', '99').css('width', '99%');
					}
					
					setTimeout(function () {
						update();
					}, 1000);
				}
			}});
}