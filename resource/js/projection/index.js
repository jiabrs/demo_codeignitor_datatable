$(document).ready(function(){
	
	show_yr();
	
	calcTable();
	
	$('a.chg_yr').click(function(event) {
		event.preventDefault();
		show_yr(this.hash.slice(1));
		calcTable();
	});
	
	$('input.this').change(function() {		
		this.value = this.value.toString().numberFormat();
		calcRow($(this).closest('tr'), 'chg');
	});
	
	$('input.chg').change(function() {
		this.value = this.value.toString().numberFormat();
		calcRow($(this).closest('tr'), 'this');		
	});
	
	$('form#proj_update').submit(function() {
		
		$.ajax({
			url: this.action,
			data: $(this).serialize(),
			type: "POST",
			dataType: "html",
			beforeSend: function() {
				$('span#update_results').html('Updating Projections <span class="ico_working"></span>');
			},
			error: function() {
				$('span#update_results').html('Projection Update Failed');
			},
			success: function() {
				$('span#update_results').html('Projections Saved');
			}
		});
		
		return false;
	});
});

function calcRow(row, colCalc)
{
	var lastVal = parseFloat(row.find('td > input.last').val().toString().replace(',',''));
	var thisVal = parseFloat(row.find('td > input.this').val().toString().replace(',',''));	
	var chgVal = parseFloat(row.find('td > input.chg').val().toString().replace(',',''));
	
	if (colCalc == 'chg')
	{
		row.find('input.chg').val(calcChg(thisVal, lastVal).toString().numberFormat());
	}
	
	if (colCalc == 'this')
	{
		row.find('input.this').val(calcThis(chgVal, lastVal).toString().numberFormat());
	}
	
	calcTable();
}

function calcTable()
{
	var dt = new Date();
	var thisTtl = 0;
	var lastTtl = 0;	
	
	$('table#projections tr.proj_data').filter(":visible").each(function() {
		
		var thisVal = parseFloat($(this).find('td > input.this').val().toString().replace(',',''));
		var lastVal = parseFloat($(this).find('td > input.last').val().toString().replace(',',''));
		
		thisTtl = thisTtl + thisVal;
		lastTtl = lastTtl + lastVal;
		
		if (this.id == 'mth_' + dt.getMonth())
		{
			$('td#lastYTDTtl').html(lastTtl.toString().numberFormat());
			$('td#thisYTDTtl').html(thisTtl.toString().numberFormat());
			$('td#chgYTDTtl').html(calcChg(thisTtl, lastTtl).toString().numberFormat());
		}
	});
	
	$('td#lastTtl').html(lastTtl.toString().numberFormat());
	$('td#thisTtl').html(thisTtl.toString().numberFormat());
	$('td#chgTtl').html(calcChg(thisTtl, lastTtl).toString().numberFormat());
}

function calcChg(thisVal, lastVal)
{	
	if (lastVal == 0)
	{
		return 0;
	}
	else {
		return ((thisVal - lastVal) * 100 / lastVal).toFixed(2);
	}	
}

function calcThis(chgVal, lastVal)
{
	return ((chgVal*lastVal/100) + lastVal).toFixed(0);
}

function show_yr(yr)
{
	if (typeof yr == 'undefined')
	{
		var now = new Date();
		yr = now.getFullYear().toString();
	}
	
	$('table tbody tr').hide();
	$('table tbody tr.show_'+yr).show('highlight','slow');
}