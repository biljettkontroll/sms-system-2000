<?php
class Helper{

    public static function dbgOut($str) {
        if ($GLOBALS['DEBUG_OUTPUT']) {
            echo $str . " \n";
        } 
    } 

	static function twitterSetStatus($user,$pwd,$status) {
		require_once 'twitteroauth/twitteroauth/twitteroauth.php';
	 
		define("CONSUMER_KEY", "ETqlpTTbgA0BAERCgv409w");
		define("CONSUMER_SECRET", "sVVEmfowTeXXggqWrLQfhP0EsUlI98TZudooNzjtl8");
		define("OAUTH_TOKEN", "190276305-BL0H003GAlMtXZmQfIH17UssF82nVghwifgZWr0d");
		define("OAUTH_SECRET", "ZiWM8ZotQAJkpAnZORFgZLdJYN2cYTuFIAeFgfr4");
		 
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
		$content = $connection->get('account/verify_credentials');
		 
		$connection->post('statuses/update', array('status' => $status));
	}
	
	

 	// static function twitterSetStatus($user,$pwd,$status) {
	// if (!function_exists("curl_init")) die("twitterSetStatus needs CURL module, please install CURL on your php.");
	// $ch = curl_init();

	// // -------------------------------------------------------
	// // get login form and parse it
	// curl_setopt($ch, CURLOPT_URL, "https://mobile.twitter.com/session/new");
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	// curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	// curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
	// curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
	// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3 ");
	// $page = curl_exec($ch);
	// $page = stristr($page, "<div class='signup-body'>");
	// preg_match("/form action=\"(.*?)\"/", $page, $action);
	// preg_match("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $page, $authenticity_token);

	// // -------------------------------------------------------
	// // make login and get home page
	// $strpost = "authenticity_token=".urlencode($authenticity_token[1])."&username=".urlencode($user)."&password=".urlencode($pwd);
	// curl_setopt($ch, CURLOPT_URL, $action[1]);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $strpost);
	// $page = curl_exec($ch);
	// // check if login was ok
	// preg_match("/\<div class=\"warning\"\>(.*?)\<\/div\>/", $page, $warning);
	// if (isset($warning[1])) return $warning[1];
	// $page = stristr($page,"<div class='tweetbox'>");
	// preg_match("/form action=\"(.*?)\"/", $page, $action);
	// preg_match("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $page, $authenticity_token);

	// // -------------------------------------------------------
	// // send status update
	// $strpost = "authenticity_token=".urlencode($authenticity_token[1]);
	// $tweet['display_coordinates']='';
	// $tweet['in_reply_to_status_id']='';
	// $tweet['lat']='';
	// $tweet['long']='';
	// $tweet['place_id']='';
	// $tweet['text']=$status;
	// $ar = array("authenticity_token" => $authenticity_token[1], "tweet"=>$tweet);
	// $data = http_build_query($ar);
	// curl_setopt($ch, CURLOPT_URL, $action[1]);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	// $page = curl_exec($ch);

	// return true;
// }




    /**
     * Sends message to twitter using curl.
     * @param <type> $message
     */
    public static function sendTwitter($message){
	Helper::twitterSetStatus('kontrollantkoll','p¤r¤gRAF32',$message);
	
/*
		if(false){
		
            $today = date("l F j, Y");

            $url = 'http://twitter.com/statuses/update.xml';

            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, "$url");
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_POST, 1);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$message");
            curl_setopt($curl_handle, CURLOPT_USERPWD, "{$GLOBALS['TWITTER_USERNAME']}:{$GLOBALS['TWITTER_PASSWORD']}");
            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);
		}else{
			 $twitterPhone = '+46737494222';
			 
		
		}
*/






    }

}

?>
