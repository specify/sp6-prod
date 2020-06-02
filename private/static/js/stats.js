date1_val = '';
date2_val = '';
version1_val = '';
version2_val = '';
isa_val = '';
hide_invalid_val = false;


$( function () {

	const tabs = 3;

	const date1 = $( "#datepicker1" );
	const date2 = $( "#datepicker2" );
	const version1 = $( "#versions1" );
	const version2 = $( "#versions2" );
	const isa = $( "#isa" );
	const hide_invalid = $( "#hide_invalid" );
	const filter = $( "#box" );
	const loading_img = $('#loading_mg');
	const submit = $( "#submit" );
	const controls = $( "#controls" );

	let tab = [];
	let breadcrumb = [];
	for(let i=1; i<=tabs; i++) {

		tab[i] = $( "#results_"+i );
		breadcrumb[i] = $( "#breadcrumb_"+i );

		if(i !== 1){
			tab[i].hide();
			breadcrumb[i].hide();
		}

	}


	function changeAddr( name, page ) {

		get_data();

		if ( page === "inst" ) {

			history.pushState( {
				state : "Colls",
				colls : name,
				d1 : date1_val,
				d2 : date2_val,
				v1 : version1_val,
				v2 : version2_val,
				i : isa_val,
			}, "", "?state=Colls" );

			query( "Institution_name=" + name, 2 );

		}
		else if ( page === "trackID" ) {

			history.pushState( {
				state : "Entry",
				tid : name,
				d1 : date1_val,
				d2 : date2_val,
				v1 : version1_val,
				v2 : version2_val,
				i : isa_val,
			}, "", "?state=Entry" );
			query( "trackID=" + name, 3 );

		}

	}

	function query( parameters = "",target_id = 1 ) {

		get_data();
		loading_img.show();

		let target;
		if(target_id === 2)
			target = tab[2][0];
		else
			target = tab[1][0];
		console.log(target);
		target.innerHTML = '';

		let xml_http;
		if ( window.XMLHttpRequest )
			xml_http = new XMLHttpRequest(); // code for IE7+, Firefox, Chrome, Opera, Safari
		else
			xml_http = new ActiveXObject( "Microsoft.XMLHTTP" ); // code for IE6, IE5

		xml_http.onreadystatechange = function () {

			if ( xml_http.readyState === 4 && xml_http.status === 200 ) {

				loading_img.hide();
				target.innerHTML = xml_http.responseText;
				const query_box = $('#query');
				console.log( query_box[0].value );

			}

			else if (xml_http.status === 500){

				loading_img.hide();
				alert("Error occurred when fetching data!");

			}

		};

		if(parameters !== '')
			parameters = parameters + '&';

		xml_http.open( "GET", "../components/get_institution.php?" + parameters +
			"date1=" + date1_val +
			"&date2=" + date2_val +
			"&version1=" + version1_val +
			"&version2=" + version2_val +
			"&isa=" + isa_val +
			"&no_head=true" +
			"&hide_invalid="+hide_invalid_val, true );
		xml_http.overrideMimeType( "text/xml; charset=iso-8859-1" );
		xml_http.send();

		if(target_id===2){
			breadcrumb[2].addClass('active').html('<a href="#">'+name+'</a>');
			breadcrumb[3].hide();
		}
		else if(target_id===3)
			breadcrumb[3].addClass('active').html('<a href="#">'+name+'</a>');

	}

	function get_data(){

		date1_val = date1.val();
		date2_val = date2.val();
		version1_val = version1.val();
		version2_val = version2.val();
		isa_val = isa.val();
		hide_invalid_val = hide_invalid.is(":checked") ? "true" : "false";

	}


	filter.hide();
	tab[2].hide();

	submit.on( "click", function ( events ) {

		get_data();

		history.pushState( {
			state : "Insts",
			d1 : date1_val,
			d2 : date2_val,
			v1 : version1_val,
			v2 : version2_val,
			i : isa_val,
		}, "", "?state=Insts" );

		events.preventDefault();
		filter.show();
		filter.val( "" );
		query();

	} );

	document.onclick = function ( e ) { //TODO: investigate this

		e = e || window.event;
		const element = e.target || e.srcElement;

		if ( element.tagName === "A" && element.id !== "ipaddress" ) {
			filter.val( "" );
			filter.hide();
			return false;
		}

	};

	if ( history.state ) {

		if ( history.state.state === "Insts" ) {
			filter.show();
			query();
		}
		else if ( history.state.state === "Colls" )
			query( "Institution_name=" + history.state.colls );
		else if ( history.state.state === "Entry" )
			query( "trackID=" + history.state.state );

	}

	window.onpopstate = function ( event ) {

		if ( event.state == null ) {

			tab[1].html( "" );
			date1.val( "" );
			date2.val( "" );
			date2.val( "" );
			version1.prop( "selectedIndex", 0 );
			version2.prop( "selectedIndex", 0 );
			isa.prop( "selectedIndex", 0 );
			filter.hide();
			filter.val( "" );

		} else if ( event.state.state === "Insts" ) {

			date1.val( event.state.d1 );
			date2.val( event.state.d2 );
			version1.prop( "selectedIndex", event.state.v1 );
			version2.prop( "selectedIndex", event.state.v2 );
			isa.prop( "selectedIndex", event.state.i );
			filter.show();
			filter.val( "" );
			query('',2);

		} else if ( event.state.state === "Colls" || event.state.state === "Entry" ) {

			date1.val( event.state.d1 );
			date2.val( event.state.d2 );
			version1.prop( "selectedIndex", event.state.v1 );
			version2.prop( "selectedIndex", event.state.v2 );
			isa.prop( "selectedIndex", event.state.i );
			filter.hide();
			filter.val( "" );
			query( 'Institution_name='+event.state.colls,3 );

		}

	};

	filter.keyup( function () {

		const el = $(this);
		const valThis = el.val().toLowerCase();

		tab[1].find( "li" ).each( function () {

			if(el.text().toLowerCase().indexOf( valThis ) > -1)
				el.show()
			else
				el.hide();

		} );

	} );

	tab[1].on('click', '[data-track_id]', function() {

		const el = $(this);
		const track_id = el.attr('data-track_id');
		let track_target = el.attr('data-track_target');

		if(typeof track_target === 'undefined')
			track_target = 'trackID';

		tab[1].hide();
		tab[2].show();

		changeAddr(track_id,track_target);

	});

	function show_tab(tab_number){

		if(breadcrumb[tab_number].hasClass('active'))
			return false;

		for(let i = 1; i <= tabs; i++){

			if(tab_number===i){

				tab[i].show();
				breadcrumb[i].find('a').attr('href','#');

			}
			else {

				tab[i].hide();
				breadcrumb[i].find('a').removeAttr('href');

			}

		}

		if(tab_number===1)
			controls.show();
		else if(tab_number>2)
			controls.hide();

	}

	for(let i = 1; i <= tabs; i++)
		breadcrumb[i].click(function() {
			show_tab(i);
		});

} );