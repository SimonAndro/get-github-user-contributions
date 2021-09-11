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
    "ironmann250",
    "Franck225-coder",
    "coachsteveee",
    "turinaf",
    "HelloNush",
    "agnessgeorge",
    "Ozymandias",
    "Negus25",
    "MabiJ",
    "teshe1221",
    "anothermorena",
];
$gitHubUsers=[];

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
    $gitHubUser["totalContributions"] = array_sum(array_column($gitHubUser["contributions"],null));
    $gitHubUser["url"] = $url;
    $gitHubUsers[] = $gitHubUser;
}

    $total_contributions = array_column($gitHubUsers, 'totalContributions');
    array_multisort($total_contributions, SORT_DESC, $gitHubUsers);

/**
 * Makes parallel curl requests at once
 */
function multiple_threads_request($nodes)
{
    set_time_limit(0);

    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $url) {
        $curl_array[$i] = curl_init($url);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_array[$i], CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_array[$i], CURLOPT_FOLLOWLOCATION, true);
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
?>

                <?php
                    $position=0;
                    foreach ($gitHubUsers as $user):
                        $position++;
                ?>
                <li>
                 
                    <div class="container">
                    <a  target="_blank" style="text-decoration:none;" href="<?php echo $user["url"]  ?>" >
                        <div class="body-section bg-light mt-4">
                            <ul style="list-style: none; padding: 0">
                                <li class="p-2 card shadow-sm mb-2">
                                    <div class="title">
                                        <div class="row justify-content-center">
                                            <div class="">
                                                <div class="text-dark text-center">
                                                    <div class="m-auto" style="
                                height: 50px;
                                width: 50px;
                                border-radius: 50%;
                                border: 1px solid #ccc;
                                background-position: center;
                                background-size:cover;
                                background-image:url('<?php echo $user["avatar"]?>');
                            ">
                                                    </div>
                                                    <div><span class=""><?php echo $user["nickname"] ?></span></div>
                                                </div>
                                                <div style="position: absolute; top: 10px; right: 10px" class="mt-3 h3 float-right">
                                                    #
                                                    <?php echo $position ?>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <hr class="m-0" />
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex flex-row bg-light justify-content-between">
                                                    <div class="pl-2 py-2 text-dark">C</div>
                                                    <div class="pl-2 py-2 text-dark">S</div>
                                                    <div class="pl-2 py-2 text-dark">M</div>
                                                    <div class="pl-2 py-2 text-dark">T</div>
                                                    <div class="pl-2 py-2 text-dark">W</div>
                                                    <div class="pl-2 py-2 text-dark">T</div>
                                                    <div class="pl-2 py-2 text-dark">F</div>
                                                    <div class="pl-2 pr-2 py-2 text-dark">S</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="contents">
                                        <ul style="list-style: none; margin: 0; padding: 0">
                                            <li>
                                                <div class="row justify-content-center">
                                                    <div class="col-md-6">
                                                        <div class="d-flex flex-row bg-light justify-content-between">
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["totalContributions"] ?>
                                                            </div>
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["0"] ?>
                                                            </div>
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["1"] ?>
                                                            </div>
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["2"] ?>
                                                            </div>
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["3"] ?>
                                                            </div>
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["4"] ?>
                                                            </div>
                                                            <div class="pl-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["5"] ?>
                                                            </div>
                                                            <div class="pl-2 pr-2 py-2 text-dark">
                                                                <?php echo $user["contributions"]["6"] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        </a>
                    </div>
                </li>
                    <?php
                endforeach
                ?>

