function changeAddr( name, page ) {

	const date1 = $( "#datepicker1" ).val();
	const date2 = $( "#datepicker2" ).val();
	const version1 = document.getElementById( "versions1" ).selectedIndex;
	const version2 = document.getElementById( "versions2" ).selectedIndex;
	const isa = document.getElementById( "isa" ).selectedIndex;

	if ( page === "inst" ) {

		history.pushState( {
			state : "Colls",
			colls : name,
			d1 : date1,
			d2 : date2,
			v1 : version1,
			v2 : version2,
			i : isa,
		}, "", "?state=Colls" );
		query( "Institution_name=" + name );

	}
	else if ( page === "trackID" ) {

		history.pushState( {
			state : "Entry",
			tid : name,
			d1 : date1,
			d2 : date2,
			v1 : version1,
			v2 : version2,
			i : isa,
		}, "", "?state=Entry" );
		query( "trackID=" + name );

	}

}

function query( parameters = "" ) {

	const date1 = $( "#datepicker1" ).val();
	const date2 = $( "#datepicker2" ).val();
	const version1 = $( "#versions1" ).val();
	const version2 = $( "#versions2" ).val();
	const isa = $( "#isa" ).val();

	document.getElementById( "loadingImg" ).style.display = "block";
	document.getElementById( "Insts" ).innerHTML = "";

	let xml_http;
		if ( window.XMLHttpRequest )
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xml_http = new XMLHttpRequest();
	else
		// code for IE6, IE5
		xml_http = new ActiveXObject( "Microsoft.XMLHTTP" );

	xml_http.onreadystatechange = function () {

		if ( xml_http.readyState === 4 && xml_http.status === 200 ) {

			const response = xml_http.responseText;
			document.getElementById( "loadingImg" ).style.display = "none";
			document.getElementById( "Insts" ).innerHTML = response;
			console.log( document.getElementById( "query" ).value );

		}

		else if (xml_http.status === 500){
			document.getElementById( "loadingImg" ).style.display = "none";
			alert("Error occurred when fetching data!");
		}

	};

	xml_http.open( "GET", "../components/get_institution.php?" + parameters + "date1=" + date1 + "&date2=" + date2 + "&version1=" + version1 + "&version2=" + version2 + "&isa=" + isa, true );
	xml_http.overrideMimeType( "text/xml; charset=iso-8859-1" );
	xml_http.send();

}


$( function () {

	const box = $( "#box" );
	box.hide();

	$( "#submit" ).on( "click", function ( events ) {

		const date1 = $( "#datepicker1" ).val();
		const date2 = $( "#datepicker2" ).val();
		const version1 = document.getElementById( "versions1" ).selectedIndex;
		const version2 = document.getElementById( "versions2" ).selectedIndex;
		const isa = document.getElementById( "isa" ).selectedIndex;

		history.pushState( {
			state : "Insts",
			d1 : date1,
			d2 : date2,
			v1 : version1,
			v2 : version2,
			i : isa,
		}, "", "?state=Insts" );

		events.preventDefault();
		box.show();
		box.val( "" );
		query();

	} );

	document.onclick = function ( e ) {

		e = e || window.event;
		const element = e.target || e.srcElement;

		if ( element.tagName === "A" && element.id !== "ipaddress" ) {
			box.val( "" );
			box.hide();
			return false;
		}

	};

	if ( history.state ) {

		if ( history.state.state === "Insts" ) {
			box.show();
			query();
		}
		else if ( history.state.state === "Colls" )
			query( "Institution_name=" + history.state.colls );
		else if ( history.state.state === "Entry" )
			query( "trackID=" + history.state.state );

	}

	window.onpopstate = function ( event ) {

		const home = $( "#Insts" );
		const date1 = $( "#datepicker1" );
		const date2 = $( "#datepicker2" );
		const version1 = $( "#versions1" );
		const version2 = $( "#versions2" );
		const isa = $( "#isa" );
		const InstFilter = $( "#box" );

		if ( event.state == null ) {

			home.html( "" );
			date1.val( "" );
			date2.val( "" );
			date2.val( "" );
			version1.prop( "selectedIndex", 0 );
			version2.prop( "selectedIndex", 0 );
			isa.prop( "selectedIndex", 0 );
			InstFilter.hide();
			InstFilter.val( "" );

		} else if ( event.state.state === "Insts" ) {

			date1.val( event.state.d1 );
			date2.val( event.state.d2 );
			version1.prop( "selectedIndex", event.state.v1 );
			version2.prop( "selectedIndex", event.state.v2 );
			isa.prop( "selectedIndex", event.state.i );
			InstFilter.show();
			InstFilter.val( "" );
			query();

		} else if ( event.state.state === "Colls" || event.state.state === "Entry" ) {

			date1.val( event.state.d1 );
			date2.val( event.state.d2 );
			version1.prop( "selectedIndex", event.state.v1 );
			version2.prop( "selectedIndex", event.state.v2 );
			isa.prop( "selectedIndex", event.state.i );
			InstFilter.hide();
			InstFilter.val( "" );
			query( 'Institution_name='+event.state.colls );

		}

	};

	box.keyup( function () {

		const valThis = $( this ).val().toLowerCase();

		$( ".Inst>li" ).each( function () {

			if($( this ).text().toLowerCase().indexOf( valThis ) > -1)
				$( this ).show()
			else
				$( this ).hide();

		} );

	} );
} );