/**
 * Character selector object
 * (anonymous constructor function)
 */
il.assStackQuestionStepwise = new function () {

	/**
	 * Self reference for usage in event handlers
	 * @type object
	 * @private
	 */
	var self = this;


	/**
	 * Configuration
	 * Has to be provided as JSON when init() is called
	 * @type object
	 * @private
	 */
	var jsstep = {};

	/**
	 * Texts to be dynamically rendered
	 * @type object
	 * @private
	 */
	var texts = {
		page: ''
	};


	/**
	 * Initialize the selector
	 * called from ilTemplate::addOnLoadCode,
	 * @param object    start configuration as JSON
	 * @param object    texts to be dynamically rendered
	 */
	this.init_stepwise = function (a_config, a_texts) {
		jsstep = a_config;
		texts = a_texts;
		$('tr#xqcas_question_display button.btn-default').click(self.validate_stepwise);
	};


	/**
	 * Send the current panel state per ajax
	 */
	this.validate_stepwise = function (event) {
		var name = "";
		if (event.target.name === undefined) {
			name = event.target.getAttribute('name');
			if (name === null) {
				alert(5);
			}
		} else {
			name = event.target.name;
		}
		name = name.replace(/cmd\[xqcas_step_/, '', name);
		name = name.replace(/\]/, '', name);
		var i = name.indexOf('_');
		var question_id = name.substr(0, i);
		var prt_name = name.substr(i + 1);
		//var is_matrix = $('#xqcas_' + question_id + '_' + input_name + '_sub_0_0').html();

		if (typeof is_matrix === "string") {
			var rows = $('#xqcas_input_matrix_height_' + input_name).html();
			var columns = $('#xqcas_input_matrix_width_' + input_name).html();
			var user_response = 'matrix(';
			for (var r = 0; r < rows; r++) {
				user_response += '[';
				for (var c = 0; c < columns; c++) {
					var value = $('#xqcas_' + question_id + '_' + input_name + '_sub_' + r + '_' + c).val();
					if (value.length == 0) {
						user_response += '?';
					} else {
						user_response += value;
					}
					if (c < columns - 1) {
						user_response += ',';
					}
				}
				user_response += ']';
				if (r < rows - 1) {
					user_response += ',';
				}
			}
			user_response += ')';
			var input_value = user_response;
		} else {
			//var input_value = $('#xqcas_' + question_id + '_' + input_name).val();
		}

		//Get All input data
		var inputs, index, stack_inputs = [];

		inputs = document.getElementsByTagName('input');
		for (index = 0; index < inputs.length; ++index) {
			// deal with inputs[index] element.
			if (inputs[index].name.startsWith("xqcas_")) {
				//Input name
				var j = inputs[index].name.lastIndexOf('_') + 1;
				var input_name = inputs[index].name.substr(j);
				//Input value
				var input_value = inputs[index].value;
				//Add to array
				var entry = input_name + '_' + input_value;
				stack_inputs.push(entry);
			}
		}

		/**
		 * Hide current question feedback
		 */
		//$(".alert").hide();
		//$(".test_specific_feedback").hide();
		/*
		$(".ilAssQuestionRelatedNavigationContainer:first").nextUntil(".ilAssQuestionRelatedNavigationContainer").hide();*/

		$.get(jsstep.step_feedback_url, {
			'question_id': question_id,
			'inputs': stack_inputs,
			'prt_name': prt_name
		})
			.done(function (data) {
				$('#stepwise_feedback_xqcas_' + question_id + '_' + prt_name).html(data);
				MathJax.Hub.Queue(["Typeset", MathJax.Hub, 'stepwise_feedback_xqcas_' + question_id + '_' + prt_name]);
			});

		return false;
	}
};