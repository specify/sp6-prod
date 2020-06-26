<script>

	let chart_background_colors = JSON.parse('<?=json_encode(CHART_BACKGROUND_COLORS)?>');
	let chart_border_colors = JSON.parse('<?=json_encode(CHART_BORDER_COLORS)?>');

</script>
<script src="<?=LINK?>static/js/charts<?=JS_EXTENSION?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script>