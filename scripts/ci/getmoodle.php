<?php

if (empty($argv) || count($argv) < 3) {
    die ('You must specify BB_USER and BB_APPPWD');
}
$bbuser = $argv[1];
$bbpwd = $argv[2];
$contents = file_get_contents(getcwd().'/package.json');
// Ensure the package.json file can be decoded.
$obj = json_decode($contents);
if (empty($obj)) {
    $this->trace("Failed to decode $file. Cannot download moodle.");
    die;
}

$flavour = $obj->moodle->flavour ?? 'moodle';
$flavour = strtolower($flavour);
$branch = $obj->moodle->branch;
$tag = $obj->moodle->tag;

function curl_file($url, $filepath) {
    $ch = curl_init();
    $fp = fopen($filepath, "w");
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_exec ($ch);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($httpcode == 404) {
        echo ('404 ERROR!');
        exit(1);
    }
    curl_close ($ch);
}

$moodlefile = getcwd()."/moodle.zip";
if ($flavour === 'mwp') {
    // Only master is supported for now with MWP.
    curl_file("https://$bbuser:$bbpwd@bitbucket.org/titus-learning/moodle_workplace/get/master.zip", $moodlefile);
} else {
    curl_file("https://download.moodle.org/download.php/direct/stable$branch/moodle-$tag.zip", $moodlefile);
}

exec("unzip -qq moodle.zip");

if ($flavour === 'mwp') {
    exec("rsync -a titus-learning-moodle_workplace-*/* moodle");
}
