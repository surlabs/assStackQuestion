$(document).ready(function() {
    $('textarea').not('.yesRTEditor').addClass('noRTEditor');

    $(".tab-button").click(function () {
        $(this).parent().find(".tab-button.active").removeClass("active");
        $(this).parent().parent().find(".tab-panel.active").removeClass("active");

        $(this).addClass("active");

        const target = $(this).data("tab");

        $(this).parent().parent().find("[data-tab-panel='" + target + "']").addClass("active");
    });
});