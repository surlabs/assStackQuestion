$(document).ready(function() {
    $('textarea').not('.yesRTEditor').addClass('noRTEditor');

    $(".tab-button").click(function () {
        let $section_id = $(this).attr('data-section-id');
        $(this).parent().find(".tab-button.active[data-section-id='" + $section_id + "']").removeClass("active");
            $(this).parent().parent().find(".tab-panel.active[data-section-id='" + $section_id + "']").removeClass("active");

        $(this).addClass("active");

        const target = $(this).data("tab");

        $(this).parent().parent().find("[data-tab-panel='" + target + "']").addClass("active");
    });
});