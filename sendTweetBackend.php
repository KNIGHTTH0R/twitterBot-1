<?php
require_once('.private-keys.php');
date_default_timezone_set('America/Denver');
require "twitteroauth/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {
    $connection = new TwitterOAuth('HnJghiNKs68sJhdnNVN9alvAz', 'd1iwpD0AMY6ER0goT2QFwVhkqFETN1bTzRMYhmJz9ZzRyJeUlC', $oauth_token, $oauth_token_secret);
    return $connection;
}

$connection = getConnectionWithAccessToken("781532307228286976-FVPTxlj7624yyCCZ9PNdiegYqFPCWFI", "ft39Bp63NqbeX49pmpfTCPCBpelBO3g2ysaiG1ncAs9Db");

$bookmarks_file = file_get_contents("/home/benfausch/webapps/twitterbot/chrome_bookmarks.json");
$bookmarks      = json_decode($bookmarks_file, true);
// print("<pre>" . print_r($json_a, 1) . "</pre>");

$my_file = '/home/benfausch/webapps/twitterbot/lasttweet.txt';
$handle  = fopen($my_file, 'r');
$key     = fread($handle, filesize($my_file));

error_log('last tweet ID is:'.$key);


foreach ($bookmarks as $bookmark => $v) {
    if (($bookmark >= $key) && ($v['parentId'] == 234)) {
        $bookmark = intval($bookmark) + 1;
        //error_log('bookmark: ' . $bookmark);
        //error_log(print_r($v, 1));

        if (!empty($v['title']) && !empty($v['url'])) {

            $short = shorten_link($v['url']);
            $tweet = $v['title'] . ' #webdev #php #js ' . $short;
            //error_log('tweet:' . $tweet);
            send_tweet($tweet, $connection);
            $my_file = 'lasttweet.txt';
            $handle  = fopen($my_file, 'w');
            $t       = fwrite($handle, $bookmark);
            break;
        }

    }
}

function send_tweet($tweet, $connection) {

    $content = $connection->post("statuses/update", ["status" => $tweet]);

    $errors = $content->errors;

    if (!empty($errors)) {
        $errormsg = $errors[0]->message;
        error_log('there was an error yo' . $errormsg);
    } else {
        $timestamp = $content->created_at;
        $tweet     = $content->text;

        error_log('yo dawg you tweeted at: ' . $timestamp . ' : ' . $tweet . '');
    }

}

function shorten_link($link) {
$api_key = Google_shorten::google_url;

    $cmd = "curl https://www.googleapis.com/urlshortener/v1/url?key=".$api_key." \
  -H 'Content-Type: application/json' \
  -d '{
  	\"longUrl\": \"" . $link . "\"
  }'";
    exec($cmd, $result);

    $result_json = '';
    foreach ($result as $r) {
        $result_json .= $r;
    }
    $result_json = json_decode($result_json, true);

    return $result_json['id'];

}
?>
