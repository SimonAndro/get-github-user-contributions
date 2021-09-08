<?php

/**
 * get github user contributions, avatar and nickname
 */

 // github user names
$userNames = [
    "simonandro",
    "admin368",
  ];
  
  foreach ($userNames as $u) {
    $markup = file_get_contents("https://github.com/" . $u); // fetch user github page
    $doc = phpQuery::newDocumentHTML($markup);  // create php query document
  
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