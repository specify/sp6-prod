$(function(){

	const x_http = new XMLHttpRequest();
	x_http.onreadystatechange = function() {
		if (this.status !== 200)//request.readyState == 4
			return;

		if(this.responseText==='')
			return;

		if(this.responseText.indexOf('Error:')!==-1)
			return console.log(this.responseText);

		$( "#org" ).text( "Possible Institution: " + $( "<div>" ).html(this.responseText).text() );
	};
	x_http.open("GET", "../components/get_ip_info.php?ip=<?=$ip?>", true);
	x_http.send();

});