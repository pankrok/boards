document.addEventListener("DOMContentLoaded", function(event) { 
  
   $("#migration-start").click(function () {
        migration();
        $("#migration-start").hide();
        $("#migration-status-button").show();
    });
  
});

function migration()
{
    $.ajax({
        type: "POST",
        url: ajax_migration_url,
        data: { migrate: 1},
        dataType: "json",
        success: function (t) {
            console.log(t);
            if(t != 'done'){
                $('#migration-progress-bar').attr('aria-valuenow', t).css('width', t+'%'); 
                migration();
            } else {
                $('#migrationModal').modal('hide');
                $("#migration-status-button").hide();
            }
        },
    }); 
}