<?
$get = file_get_contents('file.json');

$decode = json_decode($get, true);

$totalplayers = 0;

unset($decode['servernum']);

$string = array();

$svnum = 0;

$highest['name'] = '';
$highest['score'] = 0;

foreach ( $decode as $i )
{
	$totalplayers += $i['players'];
	$svnum += 1;

	if ( $i['players'] > $highest['score'] )
	{
		$highest['ip'] = $i['ip'];
		$highest['name'] = trim(addslashes($i['hostname']));
	}

	if ( is_null($i['hostname']) OR strlen($i['hostname']) < 2 )
		$hostname = $i['ip'];
	else
		$hostname = trim(addslashes($i['hostname'])).' - '.$i['ip'];

	$string[] = '	{
		value: '.$i['players'].',
		color:"#'.$i['color'].'",
		label: "'.trim(addslashes($hostname)).'"
	}';
}
?>
<!doctype html>
<html>
<head>
	<title>SA-MP user chart</title>

	<link rel="stylesheet" href="css/fonts.css" type="text/css" />
	<link rel="stylesheet" href="css/style.css" type="text/css" />

	<script src="js/jquery.js" type="text/javascript"></script>
	<script src="js/chart.js" type="text/javascript"></script>

	<script src="js/script.js" type="text/javascript"></script>
</head>
<body>
	<div id="canvas-holder">
	<center>
		<h1>SA-MP Popularity Chart</h1>
		<a>Press F11 to go Fullscreen | Updated every 30 minutes</a><br>
		<canvas id="chart-area">
	</center>
	</div>
<script>
var pieData = [<?=implode(",", $string);?>];

window.onload = function(){
	var ctx = document.getElementById("chart-area").getContext("2d");
	window.myPie = new Chart(ctx).Pie(pieData);
};
</script>
</body>
</html>