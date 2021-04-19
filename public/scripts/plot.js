if ($("#quickReply-btn").length) {
    var editor = new Jodit("#quickReply", { autofocus: !0, height: "280", toolbarAdaptive: 1, allowResizeX: !1, allowResizeY: !1 });
}

if ($("#editPostJodit-btn").length) {
    var aedit = new Jodit('#editPostJodit', { autofocus: !0, height: "280", width: "100%", toolbarAdaptive: 1, allowResizeX: !1, allowResizeY: !1 });
}
function postReply() {
    var o = $("#csrf_name").val(),
        t = $("#csrf_value").val();
    $.ajax({
        type: "POST",
        url: urlForReply,
        data: { content: $("#quickReply").val(), plot_id: $("#plot").val(), csrf_name: o, csrf_value: t },
        dataType: "json",
        error: function (o) {
            console.log(o);
        },
        success: function (o) {
            
                $('#collapseTwo').collapse('hide');
                editor.value = '';
                $("#csrf_name").val(o.csrf.csrf_name),
                $("#csrf_value").val(o.csrf.csrf_value),
                $("#posts").append(o.response),
                setTimeout(function () {
                    $(".card.mb-3").fadeIn("slow");
                }, 500);
        },
    });
}
function likeit(o) {
    var t = $("#csrf_name").val(),
        a = $("#csrf_value").val();
    $.ajax({
        type: "POST",
        url: likePost,
        data: { post_id: o, csrf_name: t, csrf_value: a , url: window.location.pathname},
        dataType: "json",
        error: function (o) {
            console.log(o);
        },
        success: function (t) {
            $("#csrf_name").val(t.csrf.csrf_name),
                $("#csrf_value").val(t.csrf.csrf_value),
                $("#" + o).before(t.likeit),
                setTimeout(function () {
                    $("#overlay").fadeIn("slow");
                }, 200),
                setTimeout(function () {
                    $("#overlay").fadeOut("slow");
                }, 2500);
        },
    });
}
function raport(o) {
    var t = $("#csrf_name").val(),
        a = $("#csrf_value").val();
    $.ajax({
        type: "POST",
        url: " path_for('board.raport.post') ",
        data: { post_id: o, reason: $('#ReportReason option:selected"').val(), csrf_name: t, csrf_value: a },
        dataType: "json",
        error: function (o) {
            console.log(o);
        },
        success: function (t) {
            $("#csrf_name").val(t.csrf.csrf_name),
                $("#csrf_value").val(t.csrf.csrf_value),
                $("#" + o).before(t.report),
                setTimeout(function () {
                    $("#overlay").fadeIn("slow");
                }, 200),
                setTimeout(function () {
                    $("#overlay").fadeOut("slow");
                }, 2500);
        },
    });
}
function scrollToAnchor(o) {
    if (o) {
        var t = $("#" + o);
        $("html,body").animate({ scrollTop: t.offset().top - 80 }, "slow"),
            $("#" + o)
                .animate({ opacity: "0" }, "slow")
                .animate({ opacity: "1" }, "slow");
    }
}

function editPost(id, len){
		
		var csrfName = $('#csrf_name').val();
		var csrfValue = $('#csrf_value').val();
			
			$.ajax({

				  type: "POST",
				  url: urlEditPost,
				  data: { 
				  'content': 	$('#editPostJodit').val(),
				  'hide': 	$('#hidePost').val(),
				  'post_id': 	(id).substring(10,len),
				  "csrf_name" : csrfName,
				  "csrf_value" : csrfValue
				  },
				  dataType: 'json',
				  error: function(query){
					console.log(query);
				  },
				  success: function(mydata){					
					
					$('#csrf_name').val(mydata['token']['csrf_name']);
					$('#csrf_value').val(mydata['token']['csrf_value']);
					$('#content'+(id).substring(9,len)).html($('#editPostJodit').val());
					$('#editPost').modal('hide');
				  }
				});	
			
		}
if ($("#quickReply-btn").length) {
    $("#quickReply-btn").click(function () {
        postReply();
    });
}
    $("#collapseTwo").on("show.bs.collapse", function () {
        $("html, body").animate({ scrollTop: $(document).height() }, 1200);
    });
var link = window.location + "",
    data = link.split("#");
$(function () {
    
    $( ".rate" ).mouseover(function(e) {
        var rate = ($(e.target).data("rate"));
        $( ".rate" ).each(function( index ) {
            if (index >= (rate)) {
                $( this ).removeClass('fas');
                $( this ).addClass('far');
            } else {
                $( this ).removeClass('far');
                $( this ).addClass('fas');
            }
        });
    });
    $("span#star-rating").mouseout(function() {
        
        $( ".rate" ).each(function( index ) {
            if (index >= stars) {
                $( this ).removeClass('fas');
                $( this ).addClass('far');
            } else {
                $( this ).removeClass('far');
                $( this ).addClass('fas');
            }
        });
    });
    
    $( ".rate" ).click(function(e) {      
        $.ajax({
            
          type: "POST",
          url: ratePlot,
          data: { 
                  "csrf_name" : $('#csrf_name').val(),
				  "csrf_value" : $('#csrf_value').val(),
                  "plot_id": $("#plot-id").data('plot_id'), 
                  "rate": $(e.target).data('rate') 
                },
          dataType: "json",
          success: function(mydata){					
					
					$('#csrf_name').val(mydata['csrf']['csrf_name']);
					$('#csrf_value').val(mydata['csrf']['csrf_value']);
                    $('#js-modal-title').html('Ocena tematu');
                    $('#js-modal-body').html(mydata['message']);
                    $('#js-modal').modal('show');
				  }
        })
        
    });
    
	$('#hidePost').val(0)
    setTimeout(function () {
        scrollToAnchor(data[1]);
    }, 500),
        $("a.likeit").click(function (o) {
            likeit(o.target.id);
        }),
        $("a.raport").click(function (o) {
            $("#RaportModal").modal("show");
        }),
        $("a.post-edit").click(function (o) {
            var t = o.target.id.length;
            aedit.setEditorValue($("#content" + o.target.id.substring(9, t)).html()),
                $("#editPlotID").val(o.target.id),
                $("#editPost").modal("show"),
                $("#post-edit-btn").click(function () {
                    editPost(o.target.id, t);
                });
        });
		
        $('#hidePost').click(function(){
            if($(this).prop("checked") == true){
                $('#hidePost').val(1)
            }
            else if($(this).prop("checked") == false){
                $('#hidePost').val(0)
            }
        });
});
