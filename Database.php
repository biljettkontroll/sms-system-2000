<?php

include_once 'Helper.php';
include_once 'Mobile.php';
include_once 'constants.php';
include_once 'Sms.php';
/**
 * Denna klass hanterar kommunikation med databasen och twitter.
 *
 *
 *
 *
 */

class Database {

    private static $connection = null;
    private static $mobiles = array();
    private static $receiverPrefix;
    private static $secretSmsString = ""; //"*hn#";
    private static $alternatingIterator = 0;

    public static function alternatingPrefix() {
        if (Database::$alternatingIterator == count(Database::$mobiles)) {
            Database::$alternatingIterator = 0;
        }
        return Database::$mobiles[Database::$alternatingIterator++];
    }
 
    /*
      function __construct() {
      echo "Creating database object\n";
      Database::connect();
      Database::getMobiles();
      }

      function __destruct() {
      Database::disconnect();
      }
     */

    public static function connect() {
        Helper::dbgOut("Connecting");

        Database::$connection = pg_connect("host={$GLOBALS['DB_HOST']} port=5432 dbname={$GLOBALS['DB_NAME']} user={$GLOBALS['DB_USER']} password={$GLOBALS['DB_PASS']}");

        if (!Database::$connection) {
            die('Could not connect to database.');
        }
		Database::getMobiles();
    }

    public static function disconnect() {
        pg_close(Database::$connection);
    }

    /**
     * Hämtar alla mobilerna och lagrar info om dem.
     *
     */
    public static function getMobiles() {
        Database::$mobiles = array();
        Database::$receiverPrefix = "";
        $result = Database::query("select mobiles.id, name,  smsleft, isreceiver, tablename from mobiles where mobiles.active = 1");
        while ($row = pg_fetch_array($result)) {

            $mobile = new Mobile();
            $mobile->id = $row['id'];
            $mobile->tablePrefix = $row['tablename'];
            $mobile->smsLeft = $row['smsleft'];
            $mobile->isReceiver = $row['isreceiver'];
            $mobile->mobileName = $row['name'];

            Database::$mobiles[] = $mobile;

            if ($row['isreceiver']) {
                Database::$receiverPrefix = $mobile->tablePrefix;
            }
        }
        if (Database::$receiverPrefix == "") {
            Helper::dbgOut("FATAL ERROR: no mobile is selected as receiver.");
            exit(-1);
        }
    }

    /**
     * Kör en query. För att korta ner.
     * @param <type> $string
     * @return <type>
     */
    public static function query($string) {
        Helper::dbgOut("Running query: " . $string);
        $result = pg_query(Database::$connection, $string) or die("Query failed");
        return $result;
    }

    public static function commandFailed($message, $description ="") {
        //$Database::createCase("FAIL", $message->getText(), $message->getId());
        //$case = pg_fetch_array($Database::getCaseByFirstMessageId($message->getId()));
        // $Database::associateCaseAndInbox($case->getId(), $message->getId());
        Database::sendSingleMessage($message->getFromNumber(), $GLOBALS['INCORRECT_SYNTAX'] . " " . $description);
    }
 
	
	public static function getMessageById($id){
		
		$receiverPrefix = Database::$receiverPrefix;
		
		//Max one row in return
        $id = pg_escape_string($caseId);
        if (!is_numeric($id)) {
            throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
        }
        $result = Database::query(
          "SELECT inbox.id as id, inbox.number, inbox.smsdate, inbox.insertdate, inbox.text,
			inbox.phone, inbox.processed
			FROM {$receiverPrefix}_inbox as inbox	
			where id = $id"			
			);
			
		while ($line = pg_fetch_array($result)) {
	//    $text = preg_replace('/[^\w\d_ -]/si', '', $line['text']);
			
        if ($line = pg_fetch_array($result)) {
			return new Sms($line['id'], $line['text'], $line['number'], false);	
        } else {
            return null;
        }	
	}
	
    /**
     * Hämtar alla nya obehandlade meddelanden från databasen.
     * @return <Message[]>
     */
    public static function getNewMessages() {
        //All messages in inbox which do not have a connections to any
        // case.

		$receiverPrefix = Database::$receiverPrefix;

		
        $result = Database::query(
                        "SELECT inbox.id as id, inbox.number, inbox.smsdate, inbox.insertdate, inbox.text,
		 inbox.phone, inbox.processed, admins.id as adminid, admins.name
			FROM {$receiverPrefix}_inbox as inbox			
			LEFT JOIN admins ON ( inbox.number = admins.number )
			LEFT JOIN members ON ( inbox.number = members.number )
			where completelyprocessed is null and (banned is null or banned = 0)");

        $messages = array();
        while ($line = pg_fetch_array($result)) {
	//    $text = preg_replace('/[^\w\d_ -]/si', '', $line['text']);
//Bad fucking idea
	    $text = $line['text'];
            $message = new Sms($line['id'], $text, $line['number'], $line['name'] != null);
			
			$messages[] = $message;
			
	   // if($GLOBALS['USE_MESSAGE_CONFIRMATION']){
	//	    Database::sendSingleMessage($line['number'], $GLOBALS['MESSAGE_CONFIRMATION']);
	 //   }
		}

	//Send confirmation
	

        return $messages;
    }


	public static function canSendDefaultMethod($caseId){
	    try{
		$caseId = pg_escape_string($caseId);
                if(!is_numeric($caseId)){
                        throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
                }

		

                $res = Database::query("select defaultaction from casetypes ct, \"case\" c where c.id = $caseId and c.casetypename = ct.name");
                $line = pg_fetch_array($res);
                if($line['defaultaction'] == "send"){
                        Database::canSendToMembers($caseId);

                }else if($line['defaultaction'] == "sendtwitter"){
                        Database::canTwitter($caseId);
                        Database::canSendToMembers($caseId);

                }else if($line['defaultaction'] == "twitter"){
                        Database::canTwitter($caseId);

                }else if($line['defaultaction'] == "none"){
                        echo "Warning: Someone tried to send a default type with no default action.";

                }else{
                        echo "ERROR: UNKNOWN DEFAULT ACTION FOR CASETYPE";
                }

	     }catch(Exception $e){
		throw $e;
	     }
	}


	/**
	* Sends using the default method of the message type.
	**/
	public static function sendDefaultMethod($caseId){
		$caseId = pg_escape_string($caseId);
		if(!is_numeric($caseId)){
			throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
		}

		$res = Database::query("select defaultaction from casetypes ct, \"case\" c where c.id = $caseId and c.casetypename = ct.name");
		$line = pg_fetch_array($res);
		if($line['defaultaction'] == "send"){
			Database::sendToMembers($caseId);
			
		}else if($line['defaultaction'] == "sendtwitter"){
			Database::twitter($caseId); 
			Database::sendToMembers($caseId);			
			
		}else if($line['defaultaction'] == "twitter"){
			Database::twitter($caseId); 
			
		}else if($line['defaultaction'] == "none"){
			echo "Warning: Someone tried to send a default type with no default action.";
			
		}else{
			echo "ERROR: UNKNOWN DEFAULT ACTION FOR CASETYPE";
		}		
	}
	
	
	
    public static function setMessageAsRead($messageId) {
        $messageId = pg_escape_string($messageId);
		$receiverPrefix = Database::$receiverPrefix;
		if(is_numeric($messageId)){
	        $result = Database::query("update {$receiverPrefix}_inbox set completelyprocessed = now() where id = $messageId");
		}
    }

    /**
     * Skapar ett ärende i databasen.
     */
    public static function createCase($case) {
        $case = Database::setCaseTypeNameAfterKeywordSearch($case);
		
		$status = $case->getStatus();
        $text = $case->getText();
        $firstInboxId = $case->getFirstInboxId();
        $caseTypeName = $case->getCaseTypeName();

        $status = pg_escape_string($status);
        $text = pg_escape_string($text);
        $caseTypeName = pg_escape_string($caseTypeName);
        $text = substr($text, 0, 160);
        $firstInboxId = pg_escape_string($firstInboxId);
		 
		
	
        Database::query("insert into \"case\" (status, text, firstinboxid, casetypename) values ('$status', '$text', $firstInboxId, '$caseTypeName')");
    }
 
	/**
	* Search for keywords in database and set case type accordingly.
	*
	**/
	
	private static function setCaseTypeNameAfterKeywordSearch($case){
		//Search for keywords
		echo 'setCaseTypeNameAfterKeywordSearch';
		if($case->getCaseTypeName() == null || $case->getCaseTypeName() == 'default'){
			$keywords =  array();
			$result2 = Database::query("select keyword, casetypename from casetypeskeywords order by priority asc");
			//Create access table.
			while($line = pg_fetch_array($result2)){
				$keywords[strtolower($line['keyword'])] = $line['casetypename'];
			}
				            
			//Search for keywords in message text.
			foreach($keywords as $key => $name){				
				if(is_numeric(strpos(strtolower($case->getText()), $key))){					
					$case->setCaseTypeName($name);
				}
			}
		}
		return $case;
	}	

    public static function changeCaseState($id, $newState) {
        $id = pg_escape_string($id);
        $newState = pg_escape_string($newState);
        
	if(is_numeric($id)){
		Database::query("update \"case\" set status = '$newState' where id = $id");
	}
    }

    public static function getLastCreatedCase() {
        $result = Database::query("select * from \"case\" where id = (select max(id) from \"case\")");
        if ($line = pg_fetch_array($result)) {
            return new ControlCase($line['id'], $line['text'], $line['status'], $line['casetypename'], $line['firstinboxid']);
        } else {
            return null;
        }
    }

    public static function getCaseByFirstMessageId($firstInboxId) {
        $firstInboxId = pg_escape_string($firstInboxId);
        if(is_numeric($firstInboxId)){
		$return = Database::query("select * from \"case\" where firstinboxid = $firstInboxId");
       	 	if ($line = pg_fetch_array($result)) {
       	    	  return new ControlCase($line['id'], $line['text'], $line['status'], $line['casetypename'], $line['firstinboxid']);
        	} else {
            		return null;
        	}
		}
    }


    /*
    * Sets the number of sms  left on all mobiles to the parameter value 
    */
    public static function setNumberSmsLeftOnAllPhones($newCount){
	$newCount = pg_escape_string($newCount);
	Database::query("update mobiles set smsleft = $newCount, warningsent = 0");	

    }



     /**
     * Updates the text of a case.
     */
    public static function changeCaseText($id, $newText) {
        $id = pg_escape_string($id);
        $newText = pg_escape_string($newText);
        $newText = substr($newText, 0, 160);
	if(is_numeric($id)){
	        Database::query("update \"case\" set text = '$newText' where id = $id");
	}
    }

    /* public static function associateCaseAndInbox($caseId, $inboxId) {
      $caseId = pg_escape_string($caseId);
      $inboxId = pg_escape_string($inboxId);
      Database::query("insert into {Database::$receiverPrefix}_inboxtocase (caseid, inboxid) values ($caseId, $inboxId)");
      } */

    /**
     * Skickar ett meddelande till alla admins. Detta görs med telefonen som pekas
     * på av alternatingPrefix.
     * @param <type> $text
     */
    public static function sendToAdmins($text) {
        $text = pg_escape_string($text);
        $text = substr($text, 0, 160);

	
 
        $mobile = Database::alternatingPrefix();
        $nrSms = Database::query("insert into {$mobile->tablePrefix}_outbox (number, text,phone, priority) select number, '$text','{$mobile->mobileName}',10 from admins");
        Database::reduceSmsLeft($mobile->tablePrefix, $nrSms);
    }

    /**
     * Håll koll på hur många sms som finns kvar på varje mobil. Denna funktion minskar
     * med angivet antal.
     * @param <type> $prefix
     * @param <type> $count
     */
    public static function reduceSmsLeft($prefix, $count = 1) {
        if (is_numeric($count)) {
            Database::query("update mobiles set smsleft = smsleft - {$count}");
        }
    }
 
    /**
     * Returnerar medelandet som skickas som svar på en inkommande request på
     * statistik.
     * @return string
     */
    public static function getStatisticsAndData() {
		$str = "";
	
        $nrUsersQuery = Database::query("select count(1) as antal from members where active = 1");
        $nrUsers = pg_fetch_array($nrUsersQuery);
        $str .= "Vi har " . $nrUsers['antal'] . " användare.";
        
		$waitingResult = Database::query("select * from \"case\" where status = 'WAITING'");
		$str .= "Väntande cases: ";
		while($line = pg_fetch_array($waitingResult)){
			$str .= $line['id'] . ". ";		
		}	
				
        return $str;
    }

    /**
     * Banna användaren med numret $number.
     * @param <type> $number
     */
    public static function banNumber($number) {
        $number = pg_escape_string($number);
	$number = Database::addCountryCode($number);
        Database::query("update members set banned = 1 where number = '{$number}'");
    }

    /*
     * Hämta de tre senaste twittrade meddelandena. Kapa ner till 160 tecken.
     */

    public static function getLastTwitters() {
        $result = Database::query("
		select id, status, text, createdate, firstinboxid 
		from \"case\"
		where status = 'TWITTER'
		order by createdate
		LIMIT 3");

        $sms = "";
        while ($row = pg_fetch_array($result)) {
            $sms .= $row['text'] . ". ";
        }


        $sms = substr($sms, 0, 160);

        return $sms;
    }

	
	
	
    public static function canSendToMembers($caseId){
             //Case id is not a number? Fuck off.
        $caseId = pg_escape_string($caseId);
        if(!is_numeric($caseId)){
                 throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
        }


      // See if this is an allowed thing to do with this casetype.
                //
     $result = Database::query("select allowsend from \"case\" c, casetypes ct where c.id = $caseId and c.casetypename = ct.name");
     $row = pg_fetch_array($result);
     if(!$row['allowsend']){
             throw new Exception($GLOBALS['CASE_TYPE_DOES_NOT_ALLOW_THIS_ACTION']);
    }


     //No such case? return -1
     $result = Database::query("select * from \"case\" where id = $caseId");
     if(pg_num_rows($result)==0){
             throw new Exception($GLOBALS['NO_SUCH_CASE']);
      }
     $row = pg_fetch_array($result);
     if(strlen($row['text'])>$GLOBALS['MAX_SEND_OUT_LENGTH']){
        throw new Exception($GLOBALS['MESSAGE_TO_LONG']);
     }


    }


    /**
     * Skicka texten i detta case till samtliga medlemmar. Dra sedan av detta från
     * antalet kvarvarande sms på mobilerna som använts.
     * @param <type> $caseId
     */
    public static function sendToMembers($caseId) {

	try{
		Database::canSendToMembers($caseId);
	}catch(Exception $e){
		throw $e;
	}


		shuffle(Database::$mobiles);

        //Räkna antalet medlemmar.
        $result = Database::query("select count(*) as antal from members where active = 1");
        $row = pg_fetch_array($result);
        $antal = $row['antal'];

        //Antal sms per mobil, avrundat neråt.
        $smsPerPhone = floor($antal / count(Database::$mobiles));
        Helper::dbgOut('Sending to ' . $antal . ' members. ' . $smsPerPhone . ' smses per phone');
		$sss = Database::$secretSmsString;

        //Skicka detta antal på alla telefoner utom den sista.
        for ($i = 0; $i < count(Database::$mobiles) - 1; $i++) { //Notice: do not include last phone.
            $offset = $i * $smsPerPhone;
            $mobile = Database::$mobiles[$i];

			
            $result = Database::query("insert into {$mobile->tablePrefix}_outbox (number, text,phone, priority)
				select members.number, ('{$sss} ' || \"case\".text || ' /kontrollantkoll'), '{$mobile->mobileName}', 0  from members, \"case\" where \"case\".id = $caseId and members.active = 1
				LIMIT {$smsPerPhone} OFFSET {$offset}");
            Database::$mobiles[$i]->lastSent = pg_affected_rows($result);
        }

        //Skicka kvarvarande SMS på sista mobilen.
        $offset = (count(Database::$mobiles) - 1) * $smsPerPhone;
        $mobile = Database::$mobiles[count(Database::$mobiles) - 1];

		
        $result = Database::query("insert into {$mobile->tablePrefix}_outbox (number, text,phone,priority)
			 select members.number,('{$sss} '|| \"case\".text || ' /kontrollantkoll'), '{$mobile->mobileName}', 0
				from members, \"case\" where \"case\".id = $caseId and members.active = 1 LIMIT 500000 OFFSET {$offset}");

        $mobile->lastSent = pg_affected_rows($result);

        //Update nr smses left in phones.
        for ($i = 0; $i < count(Database::$mobiles); $i++) {
            $lastSent = Database::$mobiles[$i]->lastSent;
            $id = Database::$mobiles[$i]->id;
            Database::query("update mobiles set smsleft = smsleft - {$lastSent} where id = {$id}");
            Database::$mobiles[$i]->lastSent = 0;
        }
    }


    public static function canSetCaseTypeName($caseId, $caseTypeName){

        if(!is_numeric($caseId)){
                throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
        }

        $result = Database::query("select name, twitteradd from casetypes ct where name = '$caseTypeName'");
        if (pg_num_rows($result) == 0) {
                 throw new Exception($GLOBALS['NO_SUCH_CASE_TYPE_NAME']);
        }


   }

    /**
     * Sets case type of case to parameter value. Returns false if the caseTypeName
     * does not exist, otherwise true. Note: this should be handled!
     * 
     */
    public static function setCaseTypeName($caseId, $caseTypeName) {
        $caseId = pg_escape_string($caseId);
        $caseTypeName = pg_escape_string($caseTypeName);

	try{
		Database::canSetCaseTypeName($caseId, $caseTypeName);
	}catch(Exception $e){
		throw $e;
	}

        Database::query("update \"case\" set casetypename = '$caseTypeName' where id = $caseId");
    }


   public static function canTwitter($caseId){
        if(!is_numeric($caseId)){
                throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
        }

        // See if this is an allowed thing to do with this casetype.
        $result = Database::query("select allowtwitter from \"case\" c, casetypes ct where c.id = $caseId and c.casetypename = ct.name");
        $row = pg_fetch_array($result);
        if(!$row['allowtwitter']){
                //return -1; //we are not allowed to send
                throw new Exception($GLOBALS['CASE_TYPE_DOES_NOT_ALLOW_THIS_ACTION']);
        }


        $caseId = pg_escape_string($caseId);
        $res = Database::query("select members.number, twitteradd, \"case\".text from members, \"case\", casetypes ct where \"case\".id = $caseId and ct.name = casetypename");
        $row = pg_fetch_array($res);
        $text = $row['text'] . " " . $row['twitteradd'];

        if(strlen($text) > 140){
                throw new Exception($GLOBALS['MESSAGE_TO_LONG_TO_TWITTER']);
        }


   }

    /**
     * Skicka ut caset på twitter. Note: if the casetypename of the case does not exist, nothing will be sent,
	 *	i.e this is an illegal state which is/should be only possible to achieve through non-UI use.
     * @param <type> $caseId 
     */
    public static function twitter($caseId) {

	try{
		Database::canTwitter($caseId);
	}catch(Exception $e){
		throw $e;
	}

        $caseId = pg_escape_string($caseId);
        $res = Database::query("select members.number, twitteradd, \"case\".text from members, \"case\", casetypes ct where \"case\".id = $caseId and ct.name = casetypename");
        $row = pg_fetch_array($res);
        $text = $row['text'] . " " . $row['twitteradd'];
        Helper::sendTwitter($text);
    }

    /**
     * Hämta ett case och returnera.
     * @param <type> $caseId
     * @return <type>
     */
    public static function getCaseById($caseId) {
        //Max one row in return
        $id = pg_escape_string($caseId);
        if (!is_numeric($id)) {
            throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
        }
        $result = Database::query("select * from \"case\" where id = $id");
        if ($line = pg_fetch_array($result)) {
            return new ControlCase($line['id'], $line['text'], $line['status'], $line['casetypename'], $line['firstinboxid']);
        } else {
            return null;
        }
    }


	private static function addCountryCode($number){
	
		if(substr($number,0,1)=='0'){
			$number = substr($number, 1);
			$number = '+46' . $number;
		}
		return $number;
	}


    /**
     * Skapa en ny medlem med angivet nummer. Finns medlemmen redan, öka medlemstiden med 1 år.
     * @param <type> $number
     */
	public static function addMember($number){
		$number = pg_escape_string($number);	
		$number = Database::addCountryCode($number);
		$result = Database::query("select id, memberuntil, active  from members where number = '{$number}'");
		if($row = pg_fetch_array($result)){
			//Member already exists
			if($row['active'] == 1){
				//Already active member, increase time left
				Database::query("update members set memberuntil = (memberuntil + interval '{$GLOBALS['MEMBERSHIP_LENGTH_POSTGRES_INTERVAL']}') where number = '{$number}'");
			}else{
				//Not active member, set timeout in one year'
				Database::query("update members set memberuntil = (now() + interval '{$GLOBALS['MEMBERSHIP_LENGTH_POSTGRES_INTERVAL']}'), active = 1 where number = '{$number}'");
			}
		
		}else{
			Database::query("insert into members (number, memberuntil) values ('{$number}', (now() + interval '{$GLOBALS['MEMBERSHIP_LENGTH_POSTGRES_INTERVAL']}') )");
		}
	}
	
	
	/**
	* Saves data on an incoming payment. For statistics or later use.
	*/
	public function savePaymentStatistics($nr, $sms, $tariff, $operator){
		
		$nr = pg_escape_string($nr);
		$sms = pg_escape_string($sms);
		$tariff = pg_escape_string($tariff);
		$operator = pg_escape_string($operator);

		Database::query("insert into payment (nr, sms, tariff, operatorname, incomedate) values ('$nr','$sms','$tariff','$operator',now())");

	}
	
	public static function activateMember($number) {
		$number = pg_escape_string($number);
        $result = Database::query("update members set active = 1 where number = '{$number}' ");
		if(pg_affected_rows($result)==0){
			return false;
		}else{
			return true;
		}
	}
	
    /**
     * Deaktiverear en medlem, så att denne inte får framtida utskick.
     * @param <type> $number
     */
    public static function removeMember($number) {
        $number = pg_escape_string($number);
        $result = Database::query("update members set active = 0 where number = '{$number}' ");
		if(pg_affected_rows($result)==0){
			return false;
		}else{
			return true;
		}
    }

	
    /**
     * Statuskontroller. Innefattar:
     * - Skicka SMS och deaktivera medlemmar vars medlemstid gått ut.
     * - Skicka varningar till admin om någon mobil har färre än 1000 sms kvar.
     * - Deaktivera mobiler med färre än 100 sms.
     */
  public static function performStatusControls() {
        $result = Database::query("select number from members where memberuntil < now() and active = 1");
        while ($line = pg_fetch_array($result)) {
            Database::sendSingleMessage($line['number'], $GLOBALS['MEMBERSHIP_OVER']);
        }
        Database::query("update members set active= 0 where memberuntil < now() and active = 1");

        $result = Database::query("select id,active, tablename,isreceiver,smsleft
		from mobiles where smsleft < 100 and warningsent = 0");

	$someoneRanOut = false;
        while ($line = pg_fetch_array($result)) {
            Database::sendToAdmins($GLOBALS['WARNING_NO_MONEY']);
	    $someoneRanOut = true;
        }

        $result = Database::query("update mobiles set warningsent = 1 where
		smsleft < 100 and warningsent = 0");

	if($someoneRanOut){
		Database::getMobiles();
	}

        //Deactivate phone that are running out of fumes.
        // Note: receiver will keep receiving even when deactivated, but it will not
        // send smses.
        //$result = Database::query("update mobiles set active = 0 where smsleft < 100 ");


	Database::query("insert into casetostations (stationid, caseid)
				select s.id, c.id from stations s, \"case\" c where lower(text) like '% '||lower(name)||' %' and analyzed is null and (c.status = 'SENT' or c.status = 'TWITTER');");
	Database::query("update \"case\" c set analyzed = now() where analyzed is null and (c.status = 'SENT' or c.status = 'TWITTER')");
 
    }
	
	
	public static function canSendPrivate($caseId){
             //Case id is not a number? Fuck off.
        $caseId = pg_escape_string($caseId);
        if(!is_numeric($caseId)){
                 throw new Exception($GLOBALS['INCORRECT_ID_FORMAT']);
        }


		 //No such case? return -1
		 $result = Database::query("select * from \"case\" where id = $caseId");
		 if(pg_num_rows($result)==0){
				 throw new Exception($GLOBALS['NO_SUCH_CASE']);
		  }
		 $row = pg_fetch_array($result);
		 if(strlen($row['text'])>$GLOBALS['MAX_SEND_OUT_LENGTH']){
			throw new Exception($GLOBALS['MESSAGE_TO_LONG']);
		 }


    }


	
    /** 
     * Skickar ett enda SMS till en mobil. Använder den alternerande mobilen till detta.
     * @param <type> $number
     * @param <type> $text
     */
    public static function sendSingleMessage($number, $text) {
        $number = pg_escape_string($number);
        $text = pg_escape_string($text);
        $mobile = Database::alternatingPrefix();
	$text = substr($text,0, 160);
        Database::query("insert into {$mobile->tablePrefix}_outbox (number, text, phone, priority) values ('{$number}', '$text','{$mobile->mobileName}', 5)");
        Database::reduceSmsLeft($mobile->tablePrefix);
    }

	
}

?>
