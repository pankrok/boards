$(function () {
	$('#next').show();	
    if (
        ($(".modal").fadeIn(),
        $(".modal").click(function (t) {
            ("modal" != $(t.target).attr("class") && "modal-close" != $(t.target).attr("class")) || $(".modal").fadeOut();
        }),
        $(".message").click(function (t) {
            "message-close" == $(t.target).attr("class") && $(t.target).parent().hide("slow");
        }),
        $(window).width() > 600)
    ) {
        $(window).scroll(function (t) {
            $(this).scrollTop() > 0 &&
                ($("#nav").css("position", "relative"),
                $("#nav")
                    .stop()
                    .animate(
                        { top: $(window).scrollTop() + "px", marginLeft: $(window).scrollLeft() + "px" },
                        {
                            duration: "slow",
                            done: function () {
                                $("#nav").css("position", "relative"), $("#nav").css("left", "0px");
                            },
                        }
                    ));
        });
    }
    setTimeout(function () {
        $(".autoHide").hide("slow");
    }, 5e3),
        $("ul.tabs li").click(function () {
            var t = $(this).attr("data-tab");
            $("ul.tabs li").removeClass("current"), $(".tab-content").removeClass("current"), $(this).addClass("current"), $("#" + t).addClass("current");
        });
    var t = $(window).width();
    $(window).resize(function () {
        (t = $(window).width()) < 600 ? ($(".nav").fadeOut("fast"), $(".menu-burger").html("|||")) : $(".nav").fadeIn("fast");
    }),
        t < 600
            ? ($(".nav").fadeOut("fast"),
              $(".menu-burger").click(function () {
                  $(".nav").toggle("slow"),
                      "|||" == $(".menu-burger").html()
                          ? ($(".menu-burger").fadeOut("fast"),
                            setTimeout(function () {
                                $(".menu-burger").html("X"), $(".menu-burger").fadeIn("fast");
                            }, 500))
                          : ($(".menu-burger").fadeOut("fast"),
                            setTimeout(function () {
                                $(".menu-burger").html("|||"), $(".menu-burger").fadeIn("fast");
                            }, 500));
              }))
            : ($(".nav").fadeIn("fast"), $(".menu-burger").html("|||")),
        $(".onoffswitch").on("change", function () {
            $($(this).attr("data-collapse")).toggle("slow");
        });
});
