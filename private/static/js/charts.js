let defined_colors_count = chart_background_colors.length;

const default_options = {
	responsive: true,
	scales : {
		yAxes : [ {
			ticks : {
				beginAtZero : true,
			},
		} ],
	},
};

function extend_colors(target){

	let i=defined_colors_count;
	for(; i<target; i++){

		chart_background_colors.push(chart_background_colors[i%defined_colors_count]);
		chart_border_colors.push(chart_border_colors[i%defined_colors_count]);

	}

	defined_colors_count = i;

}

function create_chart(chart,label='',labels=[],data=[],options=[]){

	extend_colors(data.length);

	return new Chart( chart, {
		type : "bar",
		data : {
			labels : labels,
			datasets : [ {
				label : label,
				data : data,
				backgroundColor : chart_background_colors,
				borderColor : chart_border_colors,
				borderWidth : 1,
			} ],
		},
		options : Object.assign(default_options,options),
	} );
}