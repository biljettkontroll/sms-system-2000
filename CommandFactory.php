<?php

include_once 'constants.php';
include_once 'Commands.php';




/**
 * Factory pattern helper class which creates the commands from the 
 */
class CommandFactory {

    public static function parseAdminCommand($text, $fromNumber, $messageId) {
		try{
		
			$parts = explode(' ', $text);		
			if (count($parts) > 0 && in_array(trim(strtolower($parts[0])), $GLOBALS['ADMIN_COMMANDS'])) {

				$parts[0] = trim(strtolower($parts[0]));
				Helper::dbgOut("Command is: " . $parts[0]);
				

				if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['SEND_OUT_CASE']) {
					if (count($parts) != 2) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['SEND_OUT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}

					if($parts[1] != $GLOBALS['PREVIOUS_CASE_ID'] && !is_numeric($parts[1])){
						throw new Exception($GLOBALS['NOT_AN_ID']);
					}
					
					$command = new SendOutCommand();
					$command->setCaseId($parts[1]);
					
				}else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['SEND_DEFAULT_METHOD']) {
					if (count($parts) != 2) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['SEND_DEFAULT_METHOD'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}

					$command = new SendDefault();
					$command->setCaseId($parts[1]);
					
					
				}else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['SEND_OUT_TWITTER_CASE']) {
					if (count($parts) != 2) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['SEND_OUT_TWITTER_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}
					
					if($parts[1] != $GLOBALS['PREVIOUS_CASE_ID'] && !is_numeric($parts[1])){
						throw new Exception($GLOBALS['NOT_AN_ID']);
					}

					$command = new SendOutTwitterCommand();
					$command->setCaseId($parts[1]);
					
				}else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['REJECT_CASE']) {
					if (count($parts) != 2) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}

					$command = new RejectCommand();
					$command->setCaseId($parts[1]);
				} else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['CHANGE_CASE']) {
					Helper::dbgOut("Change case called");
					if (count($parts) < 3) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}
					//Combine all remaining to one string
					$parts = explode(' ', $text, 3);

					
					if($parts[1] != $GLOBALS['PREVIOUS_CASE_ID'] && !is_numeric($parts[1])){
						throw new Exception($GLOBALS['NOT_AN_ID']);
					}
					
					$command = new ChangeCommand();
					$command->setCaseId($parts[1]);
					$command->setNewText($parts[2]);
				} else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['TWITTER_CASE']) {
					if (count($parts) != 2) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}

					if($parts[1] != $GLOBALS['PREVIOUS_CASE_ID'] && !is_numeric($parts[1])){
						throw new Exception($GLOBALS['NOT_AN_ID']);
					}
					
					$command = new TwitterCommand();
					$command->setCaseId($parts[1]);
					
				} else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['CREATE_CASE']) {
					Helper::dbgOut("Create case called");
					if (count($parts) < 2) { 
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}
					$parts = explode(' ', $text, 2);

					$command = new CreateCaseCommand();
					$command->setMessageId($messageId);
					$command->setText($parts[1]);
				} else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['BAN_NUMBER']) {
					if (count($parts) != 2) {
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}

					$command = new BanCommand();
					$command->setBanNumber($parts[1]);
					$command->setFromNumber($fromNumber);
					$command->setMessageId($messageId);
				} else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['SET_CASE_TYPE']) {
					Helper::dbgOut("Set case type called");
					if (count($parts) != 3) {
						Helper::dbgOut("Set Case did not get 3 parameters, but " . count($parts));
						throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}
	 
	 
					if($parts[1] != $GLOBALS['PREVIOUS_CASE_ID'] && !is_numeric($parts[1])){
						throw new Exception($GLOBALS['NOT_AN_ID']);
					}
	 
					$command = new SetCaseTypeCommand();
					$command->setCaseId(trim($parts[1]));
					$command->setNewCaseTypeName(trim($parts[2]));
				} else if ($parts[0] == $GLOBALS['ADMIN_COMMANDS']['STATISTICS']) {
					if (count($parts) != 1) {
					   throw new Exception($GLOBALS['ADMIN_COMMANDS']['REJECT_CASE'] . $GLOBALS['INCORRECT_PARAMETER_COUNT']);
					}

					$command = new StatisticsCommand();
					$command->setFromNumber($fromNumber);
				}
				
				return $command;
			} else {
				//No such command!
				if($parts[0]==null){
					throw new Exception($GLOBALS['NO_COMMAND_GIVEN']);
				}else{
					throw new Exception($GLOBALS['NO_SUCH_COMMAND'] . $parts[0]);
				}
			}
			$command->setCommandText($text);
		}catch(Exception $e){
			throw $e; //rethrow exception
		} 
    }

    //public static function parseUserCommand() {
	public static function parseUserCommand($text, $fromNumber, $messageId) {
 
        $parts = explode(' ', $text);
		Helper::dbgOut("nr of parts: " . count($parts));
        $parts[0] = strtolower($parts[0]);
        if (count($parts) > 0) {

            if ($parts[0] == $GLOBALS['USER_COMMANDS']['UNSUBSCRIBE']) {
                if (count($parts) != 1) {
					throw new Exception($GLOBALS['UNSUBSCRIBE_TOO_MANY_ARGUMENTS']);
					//Helper::dbgOut("Starts with unsubscribe, but incorrect number of parts");
                    //return -1;
                }

                $command = new UnsubscribeCommand();
                $command->setFromNumber($fromNumber);
				
			} else if ($parts[0] == $GLOBALS['USER_COMMANDS']['RESUBSCRIBE']) {
                if (count($parts) != 1) {
					throw new Exception($GLOBALS['RESUBSCRIBE_TOO_MANY_ARGUMENTS']);					
                }

                $command = new ResubscribeCommand();
                $command->setFromNumber($fromNumber);
				
				
            } else if ($parts[0] == $GLOBALS['USER_COMMANDS']['JOIN']) {
                if (count($parts) != 1) {
					throw new Exception($GLOBALS['JOIN_TOO_MANY_ARGUMENTS']);					
                }
 
                $command = new UnsubscribeCommand();
                $command->setFromNumber($fromNumber);
            }else{
				Helper::dbgOut("    It's a new message suggestion. " . $fromNumber . ", " . $text . ", " . $messageId);
                //It is a message suggestion
                $command = new UserSuggestionCommand();
                $command->setFromNumber($fromNumber);
                $command->setText($text);
				$command->setMessageId($messageId);

				//Send confirmation message. (TODO: This might not be the most logical place for this?)
				if($GLOBALS['USE_MESSAGE_CONFIRMATION']){
					if(substr($this->fromNumber,0,3)=='+46' || substr($this->fromNumber,0,1)=='0'){
						Database::sendSingleMessage($this->fromNumber, $GLOBALS['MESSAGE_CONFIRMATION']);
					}
				}
				
            }
			return $command;

        } else {
            throw new Exception($GLOBALS['EMPTY_SMS_FROM_MEMBER']);					
        }
    } 

}
 
?>
