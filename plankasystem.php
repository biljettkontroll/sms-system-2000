<?php

include 'Database.php';
include_once 'constants.php';
include_once 'Sms.php';
include_once 'ControlCase.php';

Database::connect();

//Main loop of system
while (true) {
    Helper::dbgOut('Running. ' . date("Y-m-d H:i:s"));
    Database::performStatusControls();
    $newMessages = Database::getNewMessages();
  
    //Do we have new messages?
    if (count($newMessages) > 0) {
        Helper::dbgOut(" New message(s).");

        //Loop through all new messages
        foreach ($newMessages as $message) {

            Helper::dbgOut("   Processing message.");

            //Parse commands			
			try{
				$message->parseCommands();
			}catch(Exception $e){
				Database::commandFailed($message, $GLOBALS['COULD_NOT_PARSE'] . $e->getMessage());
			}
			
			
			//Perform commands			
			try{
				$message->performAllCommands();
			}catch(Exception $e){
				Database::commandFailed($message, $GLOBALS['COULD_NOT_PERFORM'] . $e->getMessage());
			}
			
            Database::setMessageAsRead($message->getId());
        }

    } else {
        Helper::dbgOut("No new messages.");
    }

    sleep($GLOBALS['CHECK_FOR_MESSAGE_INTERVAL_IN_SECONDS']);
}
//Done. Kill db connection.
Database::disconnect();
?>
