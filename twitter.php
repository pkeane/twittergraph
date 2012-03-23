<?php

$title = strip_tags($_GET['title']);
$q = $_GET['q'];
$labels = $_GET['labels'];
$terms = $_GET['terms'];


$base_url = "http://search.twitter.com/search.json";
$query = "?q=".urlencode($q);

$graph_data = array();
$elements = array();
$text_data = array();
$total_hits = 0;
$i = 0;
foreach ($labels as $label) {
	if (trim($label)) {
		$graph_data[$label] = 0;
		if ($terms[$i]) {
			$elements[$label] = $terms[$i];
			$text_data[$label] = array();
		}
	}
	$i++;
}


foreach (getResults($base_url,$query) as $text) {
	foreach($elements as $label => $terms) {
		foreach(explode(' ',$terms) as $term) {
			$pattern = '/'.trim($term).'/i';
			if (preg_match($pattern,$text)) {
				$graph_data[$label]++;
				$text_data[$label][] = $text;
				$total_hits++;
			}
		}
	}
}

function getResults($base_url,$query,$results=array()) {
	$json = file_get_contents($base_url.$query);
	$php_data = json_decode($json,true);
	$_results = array();
	foreach ($php_data['results'] as $res) {
		$_results[] = $res['text'];
	}
	$new_results = array_merge($results,$_results);
	if (isset($php_data['next_page']) && count($new_results) < 500 ) {
		return getResults($base_url,$php_data['next_page'],$new_results);
	} else {
		return $new_results;
	}
}

if ($total_hits) {
	$formatted_data_array = array();
	foreach ($graph_data as $label => $count) {
		$percentage = round(($count*100)/$total_hits,2);
		$formatted_data_array[] = "['".$label."',".$percentage."]"; 
	}

	$formatted_data = join(',',$formatted_data_array);

	$text_list = '<dl>';
	foreach ($text_data as $label => $text_array) {
		$text_list .= "<dt>".$label."</dt>";
		foreach ($text_array as $text) {
			$text_list .= "<dd>".$text."</dd>";
		}
	}
	$text_list .= "</dl>";
} else {
	$title = "No Matches";
}

?>


<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">

		<title>TwitterGraph</title>

		<link rel="stylesheet" href="css/base.css">
		<link rel="stylesheet" href="css/style.css">

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
		<script src="js/highcharts.js"></script>
		<script src="js/script.js"></script>
		<script src="js/modules/exporting.js"></script>
<script type="text/javascript">
var chart;
$(document).ready(function() {
	chart = new Highcharts.Chart({
		chart: {
			renderTo: 'main',
				margin: [50, 200, 60, 170]
		},
		title: {
			text: '<?php echo $title; ?>'
		},
		plotArea: {
			shadow: null,
				borderWidth: null,
				backgroundColor: null
		},
		tooltip: {
			formatter: function() {
				return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
			}
		},
			plotOptions: {
				pie: {
					allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: true,
								formatter: function() {
									if (this.y > 5) return this.point.name;
								},
									color: 'white',
										style: {
											font: '13px Trebuchet MS, Verdana, sans-serif'
										}
						}
				}
			},
				legend: {
					layout: 'vertical',
						style: {
							left: 'auto',
								bottom: 'auto',
								right: '50px',
								top: '100px'
						}
				},
					series: [{
						type: 'pie',
							name: '<?php echo $title; ?>',
							data: [
							<?php echo $formatted_data; ?>
/*
							['Firefox',   45.0],
							['IE',       26.8],
							['Chrome',   12.8],
							['Safari',    8.5],
							['Opera',     6.2],
							['Others',   0.7]
 */
							]
					}]
	});
});

</script>

	</head>
	<body>
		<div id="wordmark">
		</div>
		<div id="container">

			<div id="header">
				<h1>Twitter Graph</h1>	
			</div>

			<div id="main" style="width: 800px; height: 400px; margin: 0 auto"></div>

			<div id="list">
			<h2>Tweets</h2>
			<?php echo $text_list; ?>
			</div>

			<div class="clear"></div>

			<div id="footer">
			</div>

		</div>
	</body>
</html>
