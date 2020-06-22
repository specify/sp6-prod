$( function () {

	const controls = $( '#controls' );
	const date1 = controls.find( '#datepicker1' );
	const date2 = controls.find( '#datepicker2' );
	const show_last_days = controls.find( '#show_last_days' );
	const filter = $( '#filter' );
	const stats = $( '#stats' );
	const refresh_data_link = $( '#refresh_data_link' );

	let institutions_count = 0;
	let disciplines_count = 0;
	let collections_count = 0;
	let records_count = 0;


	//Redirect on date range change
	function redirect() {

		date1_val = date1[ 0 ].valueAsNumber / 1000;
		date2_val = date2[ 0 ].valueAsNumber / 1000;
		show_last_days_val = show_last_days.val();


		if ( isNaN( date1_val ) )
			date1_val = '';

		if ( isNaN( date2_val ) )
			date2_val = '';

		if ( date1_val > date2_val )
			[ date1_val, date2_val ] = [ date2_val, date1_val ];

		window.location.href = link + '?' + get_dates() + get_search();

	}

	date1.on( 'change', redirect );
	date2.on( 'change', redirect );
	show_last_days.on( 'change', redirect );


	//Hide elements and update stats on search
	function search() {

		const search_targets = $( 'ol:not(.breadcrumb) > li' );

		search_query = filter.val();

		search_targets.show();
		if ( search_query !== '' ){

			try {

				const regex = new RegExp( search_query, 'i' );

				$.each( search_targets, function ( key, el ) {

					el = $( el );

					const text = el.text();

					if ( text.match( regex ) === null )
						el.hide();

				} );

			} catch(e) {}

		}

		const institutions = $( 'ol:not(.breadcrumb) > li:not([style="display: none;"])' );
		institutions_count = institutions.length;

		const disciplines = institutions.find( '> ul > li' );
		disciplines_count = disciplines.length;

		const collections = disciplines.find( '> ul > li' );
		collections_count = collections.length;

		const records = collections.find( '.list_condensed li' );
		records_count = records.length;

		stats.html( institutions_count + ` institutions<br>` +
			disciplines_count + ` disciplines<br>` +
			collections_count + ` collections<br>` +
			records_count + ` records` );

	}
	filter.bind( 'input', search );
	if ( filter.val() !== '' )
		search();


	//Construct URLs
	function get_search() {
		return 'search=' + search_query + '&';
	}

	function get_dates() {
		if ( show_last_days_val !== '' )
			return 'show_last_days=' + show_last_days_val+'&';
		else if ( date1_val !== 0 && date2_val !== 0 )
			return 'date_1=' + date1_val + '&date_2=' + date2_val+'&';
		else
			return '';
	}

	refresh_data_link.click( function () {
		refresh_data_link.attr( 'href', link + '?' + get_dates() + get_search() + 'update_data=true' );
	} );


	//Un-hide timestamps
	$( '.opener' ).click( function () {

		const el = $( this );
		const list = el.parent().find( 'ul' );
		el.remove();
		list.removeClass( 'list_condensed' );

		return false;

	} );


	//Remove ?update_cache=true from the URL
	let url = window.location.href;
	window.history.pushState('', "Specify 7 Stats", link + '?' + get_dates() + get_search());

} );