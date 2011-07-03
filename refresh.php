<?php

$key = 'your_klout_key'; // Get one here --> http://developer.klout.com/member/register

date_default_timezone_set(timezone_name_from_abbr('CET'));

include_once 'db.inc.php';

$query = mysql_query("SELECT twitter_screen_name FROM users ORDER BY RAND() LIMIT 0, 50"); // only 50 at a time

$users = array();
$results = array();

while ($user = mysql_fetch_assoc($query)) {
	$users[] = $user['twitter_screen_name'];
}

$users_chunked = array_chunk($users, 5);

foreach ($users_chunked as $chunk) {
	$result = json_decode(file_get_contents('http://api.klout.com/1/klout.json?key='.$key.'&users='.implode(',', $chunk)), true);
	foreach ($result['users'] as $item) {
		$results[] = $item;
	}	
	sleep(1); // avoid Klout API limitations
}

$now = date('Y-m-d H:i:s');

foreach ($results as $result) {
	mysql_query("UPDATE users SET kscore = '{$result['kscore']}', last_update = '$now' WHERE twitter_screen_name = '{$result['twitter_screen_name']}'");
}