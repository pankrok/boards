$(function() {
	
		$.ajax({
			url: path, 
			success: function(result){
				
				result = JSON.parse(result);	
				if(result.status != "Boards is updated")
				{
					$("#up-btn-span").fadeIn();
				}
				else
				{
					 $("#up-content").html(result.status);
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
				if(result.status == "Boards is updated" || result.status == "Update Error!")
				{
					if(result.message)
					{
						$("#message-content").html(result.message.data).addClass(result.message.type).fadeIn();
					}
					
					$("#update-bar").attr('aria-valuenow', '100').css('width', '100%');
					setTimeout(function () { 
						$('#updateModal').modal('hide');
					}, 500);
					setTimeout(function () { $("#update-bar").attr('aria-valuenow', '0').css('width', '0%');
					}, 1500);
				}
				else
				{
					setTimeout(function () {
						update();
					}, 5000);
				}
			}});
}