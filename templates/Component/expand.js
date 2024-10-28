$(".viewcontrol-expand").click(function() {
  let new_state = $(this).attr("data-state") === "collapsed";

  $(this).find("img").attr("src", `/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/tree_${new_state ? "exp" : "col"}.svg`);
  $(this).find("img").attr("alt", new_state ? "Expanded" : "Collaped");
  $(this).attr("data-state", new_state ? "expanded" : "collapsed");

  const $content = $(this).parent().parent().find(".panel-body");

  if (new_state) {
    $content.slideDown();
  } else {
    $content.slideUp();
  }
});

$(".viewcontrol-expand").each(function() {
  // Poner el boton como primer elemento del div
  $(this).parent().prepend($(this));

  $(this).parent().css("justify-content", "normal").css("align-items", "center");

  $(this).css("margin-right", "10px");

  if ($(this).attr("data-state") === "collapsed") {
    $(this).parent().parent().find(".panel-body").hide();
  }

  if ($(this).parent().find(".panel-viewcontrols").empty()) {
    $(this).parent().find(".panel-viewcontrols").remove();
  }
});