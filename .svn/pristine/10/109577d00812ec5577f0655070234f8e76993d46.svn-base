/*
 * UI Datepicker for Jeditable (Depandence: ui.datepicker.js)
 * jHtmlArea for Jeditable (Depandence: jquery.jeditable.js)
 *
 * 	
 *
 */
 
$.editable.addInputType('jHtmlArea', {
    /* create textarea element */
	element : function(settings, original) {
		settings.onblur = 'ignore';
		
		var cols =50;
		if (settings.jHtmlArea_cols)
			cols =settings.jHtmlArea_cols;
			
		var rows = 5;
		if (settings.jHtmlArea_rows)
			rows =settings.jHtmlArea_rows;			
		
		
        var textarea = $('<textarea id="idjHtmlArea" cols='+cols+' rows='+rows+'></textarea>');
        $(this).append(textarea);
        return(textarea);
    },
    /* attach jHtmlArea plugin to input element */
    plugin : function(settings, original) {
        var form = this;
		$("textarea", this).htmlarea();
    }
});

$.editable.addInputType('valueslider', {
    /* create textarea element */
	element : function(settings, original) {
		settings.onblur = 'ignore';
        var input = $('<div id="idvalueslider" class="mb_slider"></div><input type=hidden name="idvalueslider_val" id="idvalueslider_val" value="0">');
        $(this).append(input);
        return(input);
    },
    /* attach jHtmlArea plugin to input element */
    plugin : function(settings, original) {
        var form = this;
		var grid =5;
		if (settings.valueslider_grid)
			grid =settings.valueslider_grid;
			
		var maxVal = 100;
		if (settings.valueslider_maxVal)
			maxVal =settings.valueslider_maxVal;			
			
		 $("#idvalueslider", this).mbSlider({
			grid:grid, 
			maxVal:maxVal,
			onSlide:function(o){$("#idvalueslider_val").val($(o).mbgetVal());},
			onSlideLoad:function(o){
			  $(o).mbsetVal(original.revert);
			  $("#idvalueslider_val").val($(o).mbgetVal());
			}
		  });  
    },
    submit  : function(settings, original) {
		$("#idvalueslider", this).val($("#idvalueslider_val").val());
    }
});



$.editable.addInputType('uidatepicker', {
    /* create input element */
	element : function(settings, original) {
		settings.onblur = 'ignore';
        var input = $('<input class="text" readonly="true">');
        $(this).append(input);
        return(input);
    },
    /* attach ui.datepicker plugin to input element */
    plugin : function(settings, original) {
        var form = this;
        $("input", this)
            .datepicker({
                yearRange: '-30:+2',
                dateFormat: 'dd-mm-yy',
                onClose: function(){
                    if ($.isFunction($.editable.types[settings.type].reset)) {
                        var reset = $.editable.types[settings.type].reset;
                    }
                    else {
                        var reset = $.editable.types['defaults'].reset;
                    }
                    reset.apply(form, [settings, original]);
                }
            })
            .bind('change', function() {
                $(form).submit();
            })
    },
    submit  : function(settings, original) {
        if(original.revert == $("input", this).val()) {
            exit();
         }
    }
});


$.editable.addInputType('uidobpicker', {
    /* create input element */
	element : function(settings, original) {
		settings.onblur = 'ignore';
        var input = $('<input class="text" readonly="true">');
        $(this).append(input);
        return(input);
    },
    /* attach ui.datepicker plugin to input element */
    plugin : function(settings, original) {
        var form = this;
        $("input", this)
            .datepicker({
                yearRange: '-90:0',
				maxDate: new Date(),
                dateFormat: 'dd-mm-yy',
                onClose: function(){
                    if ($.isFunction($.editable.types[settings.type].reset)) {
                        var reset = $.editable.types[settings.type].reset;
                    }
                    else {
                        var reset = $.editable.types['defaults'].reset;
                    }
                    reset.apply(form, [settings, original]);
                }
            })
            .bind('change', function() {
                $(form).submit();
            })
    },
    submit  : function(settings, original) {
        if(original.revert == $("input", this).val()) {
            exit();
         }
    }
});

