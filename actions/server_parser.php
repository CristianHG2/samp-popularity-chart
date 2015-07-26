<?

file_put_contents('main.log', 'BEGUN NEW LOG AT '.time().PHP_EOL.'================================================='.PHP_EOL, FILE_APPEND);

echo PHP_EOL."========== MASTER SERVER BASED ANALYTICS COLLECTOR 1.0 ========".PHP_EOL;

echo "SETTING TIME LIMIT TO ZERO".PHP_EOL;

set_time_limit(0);

echo "INCLUDING REQUIRED FILES".PHP_EOL;

require '../inc/SampQuery.class.php';

echo "DECLARING REQUIRED FUNCTIONS".PHP_EOL;

function cleanString($string)
{
	return preg_replace("/[^A-Za-z 1-9 \[\]!@#$%^&*\(\)-_=+\{\}\\|;':\"\/,.]/", '', $string);
}

function cacheServers()
{
	echo "RETRIEVING CACHED IP ADDRESSES".PHP_EOL;

	$ips_f = file_get_contents("IP_CACHE");

	echo "CREATING ARRAY".PHP_EOL;

	$ips = explode("\n", $ips_f);

	echo "SETTING UP VARIABLES".PHP_EOL;

	$totalplayers = 0;
	
	$data = array();

	$index = 0;

	echo "LET'S BEGIN!".PHP_EOL;
	echo "STARTING LOOP...".PHP_EOL;

	foreach ( $ips as $ip_f )
	{
		echo "SERVING SERVER ".$ip_f.PHP_EOL;
		$buffer = explode(":", $ip_f);

		$current_sv = new SampQuery($buffer[0], $buffer[1]);

		if ( !$current_sv->connect() )
		{
			echo "SERVER OFFLINE. SKIPPING ".$ip_f." AND LOGGING".PHP_EOL;
			file_put_contents('main.log', "Skipped server with address ".$ip_f."\n", FILE_APPEND);

			continue;
		}
		else
		{
			echo "SERVER HAS BEEN REACHED. RETRIEVING INFORMATION...".PHP_EOL;
			$pdata = $current_sv->getInfo();

			if ( $pdata == -1 )
			{
				echo "COULD NOT RETRIEVE INFORMATION. SKIPPING ".$ip_f." AND LOGGING".PHP_EOL;
				file_put_contents('main.log', "Could not retrieve server with address ".$ip_f."\n", FILE_APPEND);
			
				continue;
			}
			else
			{
				echo "STORING INFORMATION IN ARRAY".PHP_EOL;

				$data[$index]['players'] = $pdata['players'];

				if ( is_null($pdata['hostname']) )
					$hostname = $ip_f;
				else
					$hostname = cleanString($pdata['hostname']);

				$data[$index]['hostname'] = $hostname;
				$data[$index]['color'] = random_color();
				$data[$index]['ip'] = $ip_f;

				echo "WE SERVED SERVER ".cleanString($pdata['hostname'])." WITH ".$pdata['players']." PLAYERS".PHP_EOL;

				echo "ADDING PLAYERNUM TO PLAYER LIST".PHP_EOL;

				$totalplayers += $pdata['players'];

				echo "NEW PLAYERNUM IS ".$totalplayers.PHP_EOL;

				echo "RESETTING VARIABLES".PHP_EOL;

				unset($current_sv);
				unset($buffer);

				echo "SUMMING TO INDEX".PHP_EOL.PHP_EOL;

				$index++;
			}
		}
	}

	var_dump($data);
	unlink('../file.json');
	file_put_contents('../file.json', json_encode($data));
}

function isCLI()
{
    return (php_sapi_name() === 'cli');
}

function random_color_part() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

function random_color() 
{
    return random_color_part() . random_color_part() . random_color_part();
}

echo "ARE WE RUNNING CLI? ";

if ( !isCLI() )
{
	echo "NO".PHP_EOL;
	echo "ABORTING, PLEASE RUN FROM CLI...".PHP_EOL;
	exit();
}

echo "YES".PHP_EOL;

if ( file_exists('IP_CACHE') )
{
	echo "FOUND PREVIOUS IP CACHE. ARCHIVING.".PHP_EOL;
	$archive = copy('IP_CACHE', 'ip_archive/IP_CACHE_ARCHIVE_'.time());

	if ( $archive )
		echo "ARCHIVED".PHP_EOL.PHP_EOL;
	else
		echo "COULD NOT ARCHIVE".PHP_EOL.PHP_EOL;
}

if ( file_exists('../file.json') )
{
	echo "FOUND PREVIOUS SERVER CACHE. ARCHIVING.".PHP_EOL;
	$archive = copy('../file.json', 'sv_archive/SERVER_CACHE_ARCHIVE_'.time());

	//ddd

	if ( $archive )
		echo "ARCHIVED".PHP_EOL.PHP_EOL;
	else
		echo "COULD NOT ARCHIVE".PHP_EOL.PHP_EOL;
}

if ( !file_exists('LAST_TIME') )
{
	echo "FIRST TIME RUNNING! CREATING CACHE FILES.".PHP_EOL;
	echo "DOWNLOADING HOSTED SERVER LIST".PHP_EOL;

	$source = "http://server.sa-mp.com/0.3.7/hosted";
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $source);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSLVERSION,3);

	$data = curl_exec($ch);
	$error = curl_error($ch);

	curl_close($ch);

	echo "SETTING UP CACHE".PHP_EOL;

	file_put_contents("LAST_TIME", time());

	cacheServers();
}
else
{
	echo "RETRIEVING LAST RUNNING TIME".PHP_EOL;

	$time = file_get_contents("LAST_TIME");

	if ( $time + 1600 < time() )
	{
		echo "IT'S BEEN AN HOUR SINCE WE CACHED! RECACHING...".PHP_EOL;

		if ( $time + 86400 < time() )
		{
			echo "DOWNLOADING HOSTED SERVER LIST".PHP_EOL;

			$source = "http://server.sa-mp.com/0.3.7/hosted";
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $source);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSLVERSION,3);

			$data = curl_exec($ch);
			$error = curl_error($ch);

			curl_close($ch);

			file_put_contents("IP_CACHE", trim($data));
		}

		echo "SETTING UP CACHE".PHP_EOL;

		file_put_contents("LAST_TIME", time());

		cacheServers();
	}
	else
	{
		echo "YOU RAN THE SCRIPT A WHILE AGO. TRY AGAIN LATER".PHP_EOL;
	}
}
