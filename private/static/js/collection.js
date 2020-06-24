$(function(){

	const select_fields = $('select');
	const result = $('#result');

	select_fields.on('change',function(){

		const select_field = $(this);
		const category_name = select_field.attr('name');
		const selected_field = select_field.find('option:selected').val();

		if(selected_field==='')
			return true;

		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState === 4 && this.status === 200)
				result.html(xhttp.responseText);
		};
		xhttp.open("GET", link+'chart/?collection_number='+collection_number+
			'&category_name='+category_name+
			'&selected_field='+selected_field, true);
		xhttp.send();

		select_fields.each(function(){

			const el = $(this);

			if(!el.is(select_field))
				el.val('');

		});

	});

});