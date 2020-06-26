$( function () {

	const controls = $( '#controls' );
	const date1 = controls.find( '#datepicker1' );
	const date2 = controls.find( '#datepicker2' );
	const show_last_days = controls.find( '#show_last_days' );
	const refresh_data_link = $( '#refresh_data_link' );


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

		window.location.href = link + 'stats/?' + get_dates() + get_search();

	}

	date1.on( 'change', redirect );
	date2.on( 'change', redirect );
	show_last_days.on( 'change', redirect );


	//Construct URLs
	function get_search() {
		if ( search_query !== '' )
			return 'search_query=' + search_query + '&';
		return '';
	}

	function get_dates() {
		if ( show_last_days_val !== '' )
			return 'show_last_days=' + show_last_days_val + '&';
		else if ( date1_val !== 0 && date2_val !== 0 )
			return 'date_1=' + date1_val + '&date_2=' + date2_val + '&';
		return '';
	}

	refresh_data_link.click( function () {
		refresh_data_link.attr( 'href', link + 'stats/?' + get_dates() + get_search() + 'update_cache=true' );
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
	if ( url.indexOf( 'update_cache=true' ) !== -1 )
		window.history.pushState( '', 'Specify 7 Stats', link + 'stats/?' + get_dates() + get_search() );

} );