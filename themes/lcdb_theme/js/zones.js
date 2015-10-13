$(function() {
	var array = $('#calendar_admin').data('array'),
		year = $('#calendar_admin').find('table').data('year'),
		month = $('#calendar_admin').find('table').data('month');
		month = month > 9 ? "" + month: "0" + month;

	$('tr>td').click(function(event) {
		var $this=$(this);
		$this.parents('table').find('.desc').hide();
		$this.find('.desc').toggle();
	})
	/*
	$('td .check').click(function(event) {
		event.stopPropagation();
		var $this=$(this);
		$this.parents('td').toggleClass('open');
	})
	*/
	$('td .okk').click(function(event) {
		event.stopPropagation();
		var $this=$(this);
		var	day = $this.parents('td').data('day');
		//var	val = $this.siblings('.text').val();
        var val = $this.parents('td').find('label input:radio:checked').val();
        if (val == 0){
            $this.parents('td').removeClass('close');
            $this.parents('td').removeClass('open');
        }
        if (val == 1){
            $this.parents('td').removeClass('close');
            $this.parents('td').addClass('open');
        }
        if (val == 2){
            $this.parents('td').addClass('close');
            $this.parents('td').removeClass('open');
        }
		if ($this.parents('td').hasClass('open') ||
            $this.parents('td').hasClass('close')) {
			if ( typeof array != "object") {
				array = {};
			}
			if (!array[year]) {
				array[year] = {};
			};
			if ( typeof array[year][month] == "undefined") {
				array[year][month] = {};
			};
			array[year][month][day] = val;
		} else {
			array[year][month][day] = null;
		}

		$('#calendar').val(JSON.stringify(array))
		$this.parents('.desc').hide();
	})
})