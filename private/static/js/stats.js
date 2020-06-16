$(function () {

	const controls = $( "#controls" );
	const date1 = controls.find( "#datepicker1" );
	const date2 = controls.find( "#datepicker2" );
	const version1 = controls.find( "#versions1" );
	const version2 = controls.find( "#versions2" );
	const show_last_days = controls.find( "#show_last_days" );
	const isa = controls.find( "#isa" );
	const submit = $( "#submit" );
	const tab = $('#tab');
	const filter_label = $('#filter');
	const filter = filter_label.find('input');
	const loading = $('#loading');
	let search_targets = null;


	submit.on( "click", function(){

		let date1_val = date1[0].valueAsNumber/1000;
		let date2_val = date2[0].valueAsNumber/1000;
		const show_last_days_val = show_last_days.val();
		const version1_val = version1.val();
		const version2_val = version2.val();
		const isa_val = isa.prop('checked')===true?'true':'false';


		if(isNaN(date1_val))
			date1_val = '';

		if(isNaN(date2_val))
			date2_val = '';

		if(date1_val>date2_val)
			[date1_val,date2_val] = [date2_val,date1_val]

		if(version1_val>version1_val)
			[version1_val,version2_val] = [version2_val,version1_val]


		const link = target_link+
				'?date_1='+date1_val+
				'&date_2='+date2_val+
				'&version_1='+version1_val+
				'&version_2='+version2_val+
				'&show_last_days='+show_last_days_val+
				'&isa='+isa_val;

		submit.attr('href',link);

		loading.show();
		filter_label.hide();
		tab.hide();

	});


	filter.bind('input',function(){

		if(search_targets==null)
			search_targets = $('ol > li');

		const search_query = filter.val();

		if(search_query==='')
			search_targets.show();
		else {

			const regex = new RegExp(search_query,"i");

			$.each(search_targets,function(key,el){

				el = $(el);

				const text = el.text();

				if(text.match(regex)!==null)
					el.show();
				else
					el.hide();

			});
		}

	});

} );