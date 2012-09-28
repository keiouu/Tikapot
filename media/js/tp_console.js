$(function() {
    $(".debug_panel .debug_tab").hide();
    
    $(".debug_panel .toolbar .debug_pill").click(function() {
        if ($(this).hasClass("active")) {
            $(".debug_panel .toolbar .debug_pill").removeClass("active");
            $(".debug_panel .debug_tab").hide();
            return;
        }
        
        $(".debug_panel .toolbar .debug_pill").removeClass("active");
        $(this).addClass("active");
        $(".debug_panel .debug_tab").hide();
        $("#" + $(this).attr("data-tab") + "-tab").show();
    });
});