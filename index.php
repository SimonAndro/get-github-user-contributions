<?php

require 'phpQuery/phpQuery.php'; //require php query

set_time_limit(0);

/**
 * get github user contributions, avatar and nickname
 */

// github user names
$userNames = [
    "simonandro",
    "admin368",
];

//create an array of github user home page urls
$url = "https://github.com/";
$urls = array();
foreach( $userNames as $u)
{
    array_push($urls,$url.$u);
}

$res = multiple_threads_request($urls); // fetch user github  users home pages

foreach ($urls as $url) {

    $markup = $res[$url];  // get user homepage markup

    $doc = phpQuery::newDocumentHTML($markup); // create php query document

    $avatar = pq(".avatar-user")->slice(-1)->attr("src"); // get github user avatar
    $nickname = trim(pq(".p-nickname")->html()); // get github username

    $contributions = pq(".js-calendar-graph-svg g g")->slice(-1)->find("rect"); //get contributions in current week
    $contributions_this_week = array("0" => 0, "1" => 0, "2" => 0, "3" => 0, "4" => 0, "5" => 0, "6" => 0); //["S","M","T","W","T","F","S"];
    $count = 0;

    foreach ($contributions as $c) {
        $contributions_this_week[$count++] = pq($c)->attr("data-count");
    }

    $gitHubUser["avatar"] = $avatar;
    $gitHubUser["nickname"] = $nickname;
    $gitHubUser["contributions"] = $contributions_this_week;

    print_r($gitHubUser); // github user data
}


/**
 * Makes parallel curl requests at once
 */
function multiple_threads_request($nodes)
{

    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $url) {
        $curl_array[$i] = curl_init($url);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_array[$i], CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_array[$i], CURLOPT_COOKIEFILE, "/tmp/cookie.txt");
        curl_setopt($curl_array[$i], CURLOPT_COOKIEJAR, "/tmp/cookie.txt");
        curl_multi_add_handle($mh, $curl_array[$i]);
    }
    $running = null;
    do {
        usleep(10000);
        curl_multi_exec($mh, $running);
    } while ($running > 0);

    $res = array();
    foreach ($nodes as $i => $url) {
        $res[$url] = curl_multi_getcontent($curl_array[$i]);
    }

    foreach ($nodes as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}
