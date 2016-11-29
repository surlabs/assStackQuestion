/*
 * Use the full page for show the PRT authoring interface
 */
$(document).ready(function () {
	/*Change width of PRT	part to use the full page space*/
	$('#il_prop_cont_question_prts label')
		.removeClass("col-sm-3 control-label")
		.addClass("col-sm-0 control-label");
	$('#il_prop_cont_question_prts label').next()
		.removeClass("col-sm-9")
		.addClass("col-sm-12");
	/*Change title to be like the question header*/
	var text = $("label[for='question_prts']").html();
	$("label[for='question_prts']").html("<h3 class='ilHeader'>" + text + "</h3>")

});

