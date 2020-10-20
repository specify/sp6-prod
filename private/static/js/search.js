$( function () {

	const filter = $( '#filter' );
	const stats = $( '#stats' );
	const unfold_collections = $( '#unfold_collections' );
	const search_button = $( '#search_button' );
	const parent = $( 'ol:not(.breadcrumb)' );
	const nodes = parent.find( 'li' );
	const institutions = [];


	$.each( $( 'ol > li' ), function ( index, element ) {//institutions

		const institution = $( element );
		const institution_name = institution.find( '> span' ).text();
		const disciplines = [];

		$.each( institution.find( '> ul > li' ), function ( index, element ) {//disciplines

			const discipline = $( element );
			const discipline_name = discipline.find( '> span' ).text();
			const collections = [];

			$.each( discipline.find( '> ul > li' ), function ( index, element ) {//collections

				const collection = $( element );
				const collection_name = collection.find( '> a:not(.opener)' ).text();
				const reports_count = collection.find( '> ul > li' ).length;

				collections.push( [ collection, collection_name, reports_count ] );

			} );

			disciplines.push( [ discipline, discipline_name, collections ] );

		} );

		institutions.push( [ institution, institution_name, disciplines ] );

	} );


	function update_stats( defaults = [] ) {

		let institutions_count = 0;
		let disciplines_count = 0;
		let collections_count = 0;
		let reports_count = 0;

		if ( defaults.length === 4 )
			[ institutions_count, disciplines_count, collections_count, reports_count ] = defaults;
		else {

			$.each( institutions, function ( index, [ institution, institution_name, disciplines ] ) {

				$.each( disciplines, function ( index, [ discipline, discipline_name, collections ] ) {

					$.each( collections, function ( index, [ collection, collection_name, reports_count_local ] ) {

						if ( ! collection.hasClass( 'greyed_out' ) ) {
							collections_count++;
							reports_count += reports_count_local;
						}

					} );

					if ( ! discipline.hasClass( 'greyed_out' ) )
						disciplines_count++;

				} );

				if ( ! institution.hasClass( 'greyed_out' ) )
					institutions_count++;

			} );

		}

		stats.html( institutions_count + ` institutions<br>` +
			disciplines_count + ` disciplines<br>` +
			collections_count + ` collections<br>` +
			reports_count + ` reports` );

	}


	function search() {

		search_query = filter.val();
		nodes.removeClass( 'greyed_out soft_greyed_out' );

		if ( search_query === '' ) {

			unfold_collections.addClass( 'disabled' );
			update_stats( [ initial_institutions_count, initial_disciplines_count, initial_collections_count, initial_reports_count ] );

		} else {

			unfold_collections.removeClass( 'disabled' );

			try {

				const regex = new RegExp( search_query, 'i' );

				$.each( institutions, function ( index, [ institution, institution_name, disciplines ] ) {

					let institution_matches = institution_name.match( regex ) !== null;
					let disciplines_match = false;

					$.each( disciplines, function ( index, [ discipline, discipline_name, collections ] ) {

						let discipline_matches = discipline_name.match( regex ) !== null;
						let collections_match = false;

						if(discipline_matches)
							disciplines_match = true;

						$.each( collections, function ( index, [ collection, collection_name, reports_count_local ] ) {

							const collection_matches = collection_name.match( regex ) !== null;

							if ( collection_matches )
								collections_match = true;
							else
								collection.addClass( 'greyed_out' );

						} );

						if ( ! discipline_matches && ! collections_match )
							discipline.addClass( 'greyed_out' );
						else if ( ! discipline_matches && collections_match ) {
							disciplines_match = true;
							discipline.addClass( 'soft_greyed_out' );
						}

					} );

					if ( ! institution_matches && ! disciplines_match )
						institution.addClass( 'greyed_out' );
					else if ( ! institution_matches && disciplines_match )
						institution.addClass( 'soft_greyed_out' );

				} );

			} catch ( e ) {
			}

		}

		update_stats();

	}


	search_button.click( search );
	if ( filter.val() !== '' )
		search();


	nodes.click( function () {

		const el = $( this );

		if ( ! el.is( 'li' ) )
			return true;

		el.removeClass( 'greyed_out soft_greyed_out' );

		const children = el.find( 'li' );
		children.removeClass( 'greyed_out soft_greyed_out' );

		update_stats();

		return ! ( el.hasClass( 'greyed_out' ) || el.hasClass( 'soft_greyed_out' ) );

	} );


	unfold_collections.click( function () {

		$('ul li').removeClass( 'soft_greyed_out greyed_out' );
		unfold_collections.addClass( 'disabled' );

		update_stats();

	} );

} );
