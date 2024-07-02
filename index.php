<?php
$pkg = $_GET["pkg"];
$mode = $_GET["mode"];
$debug = $_GET["debug"];

if($debug) {
	print("In debug mode!\n");
}
if(!$pkg) {
	finish(400,"Please supply a valid package");
}
$opts = [
        'http' => [
                'method' => 'GET',
                'header' => [
                        'User-Agent: PHP'
                ]
        ]
];

$context = stream_context_create($opts);

$gh = 'https://api.github.com/';
if($mode == "github") {
	$path = $gh . "repos/" . $pkg;
	if($debug) { print("fetching from " . $path); }
  $data = file_get_contents($path, false, $context);
	if($data === false ) {
		finish(500, 'The connection to "' . $path . '" failed');
	}
  finish(200, $data);
} else if ($mode == "githubsearch") {
	$path = $gh . 'search/issues?q=+type:pr+repo:'.urlencode($pkg).'+is:open';
	if($debug) { print("fetching from " . $path); }
	$data = file_get_contents($path, false, $context);
	if(!$data === 'Not Found') {
		finish(500, 'Problem "not found" fetching from ' . $path);
	}
  finish(200,$data);
} else {

$siteData = file_get_contents("https://www.npmjs.com/package/" . urlencode($pkg));
}

if($debug) {
	print('fetching from' .$gh . 'search/issues?q=+type:pr+repo:'.urlencode($pkg).'+is:open' . ' and ' .  "https://www.npmjs.com/package/" . urlencode($pkg));
	print($siteData . " and " . $data);
}

preg_match('@weekly-downloads">([^<]*)@', $siteData, $matches);
$result = array("weekly_downloads"=>$matches[1]);
preg_match('@daily-downloads">([^<]*)@', $siteData, $matches);
$result["daily_downloads"] = $matches[1];
preg_match('@monthly-downloads">([^<]*)@', $siteData, $matches);
$result["monthly_downloads"] = $matches[1];
$result["package"] = $pkg;

finish(200, json_encode($result));



function finish($responseCode, $msg) {
	header("Access-Control-Allow-Origin: *");
	header('Content-Type: application/json');
	http_response_code($responseCode);
	print $msg;
	exit;
}
