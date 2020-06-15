$(function () {

	//TODO: implement search backend

	const controls = $( "#controls" );
	const date1 = controls.find( "#datepicker1" );
	const date2 = controls.find( "#datepicker2" );
	const version1 = controls.find( "#versions1" );
	const version2 = controls.find( "#versions2" );
	const show_last_days = controls.find( "#show_last_days" );
	const isa = controls.find( "#isa" );
	const submit = $( "#submit" );
	const tab = $('#tab');


	function open_url(parameters,new_tab=false){

		let date1_val = date1[0].valueAsNumber/1000;
		let date2_val = date2[0].valueAsNumber/1000;
		const show_last_days_val = show_last_days[0].val();
		const version1_val = version1.val();
		const version2_val = version2.val();
		const isa_val = isa.val();


		if(date1_val>date2_val)
			[date1_val,date2_val] = [date2_val,date1_val]

		else if(show_last_days_val!==0){

			date1_val = (new Date()).getDate()/1000;
			date2_val = date1_val - show_last_days_val*86400;

		}

		if(version1_val>version1_val)
			[version1_val,version2_val] = [version2_val,version1_val]


		const link = link+
			'?date_1='+date1_val+
			'&date_2='+date2_val+
			'&version_1='+version1_val+
			'&version_2='+version2_val+
			'&isa='+isa_val+
			'&'+parameters;

		if(new_tab)
			window.open(link,'_blank');
		else
			window.location.href = link;

	}


	submit.on( "click", open_url);


	tab.find('a').click(function(){

		const el = $(this);
		const track_id = el.attr('data-track_id');
		const track_target = el.attr('data-track_target');

		if(typeof track_target === 'undefined')
			open_url('collection='+track_id,true);
		else
			open_url('track_id='+track_id,true);


	});

} );