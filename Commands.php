<?php

include_once 'constants.php';
include_once 'iCommand.php';
/**
 * These all implement the whatever pattern. These are the commands. Add here 
 * and in CommandFactory to add a new command.
 */

/**
 * Description of acceptCommand
 *
 * @author pok
 */
class CreateCaseCommand extends iCommand {

    
    private $text;
    private $messageId;

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function getMessageId() {
        return $this->messageId;
    }

    public function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    public function useLastCaseId($lastId) {
        
    }

    public function performCommand() {
	/*$case = new ControlCase(0,$this->text,'WAITING','default',$this->messageId);
        Database::createCase($case);
        $case = Database::getLastCreatedCase();
        return $case->getId();*/
    }

    public function canPerformCommand(){
	try{
		$case = new ControlCase(0,$this->text,'WAITING','default',$this->messageId);
       	 	Database::createCase($case);
       	 	$case = Database::getLastCreatedCase();
	        return $case->getId();			
	}catch(Exception $e){
		throw $e;
	}

    }

}


/**
*/
class SendDefault extends iCommand {

    private $caseId;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            Database::changeCaseState($case->getId(), "SENT");
            
			//Send sms to all members
	    Database::sendDefaultMethod($this->caseId);
            //Database::sendToMembers($this->caseId);
            //Database::twitter($this->caseId);
        }
    }

     public function canPerformCommand(){
        try{
        	Database::canSendDefaultMethod($this->caseId);
	}catch(Exception $e){
                throw $e;
        }

    }

}


/**
 * Command to send out a case to all members.
 */
class SendOutCommand extends iCommand {

    
    private $caseId;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            Database::changeCaseState($case->getId(), "SENT");            
       	    //Send sms to all members
            Database::sendToMembers($this->caseId);            
        }
    }

  public function canPerformCommand(){
        try{
                Database::canSendToMembers($this->caseId);
        }catch(Exception $e){
                throw $e;
        }

    }


}


/**
 * Sends a private message to user.
 */
class SendPrivate extends iCommand {

    
        private $caseId;
    private $newText;

    public function getCaseId() {
        return $this->caseId;
    }
 
    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function getNewText() {
        return $this->newText;
    }

    public function setNewText($newText) {
        $this->newText = $newText;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
		$message = Database::getMessageById($case->firstInboxId);
		
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            Database::changeCaseState($case->getId(), "PRIVATE");            
       	    //Send sms to all members
			
            Database::sendSingleMessage($message->fromNumber, $this->newText);    
        }
    }

  public function canPerformCommand(){
        try{
                Database::canSendPrivate($this->caseId);
        }catch(Exception $e){
                throw $e;
        }

    }


}

/**
 * Command to send out a case to all members and twitters.
 */
class SendOutTwitterCommand extends iCommand {

    
    private $caseId;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            Database::changeCaseState($case->getId(), "SENT");
            //Database::associateCaseAndInbox($case->getId(), $line['id']);

			//Send sms to all members
            Database::sendToMembers($this->caseId);
            Database::twitter($this->caseId);
        }
    }

  public function canPerformCommand(){
        try{
		Database::canTwitter($this->caseId);
                Database::canSendDefaultMethod($this->caseId);
        }catch(Exception $e){
                throw $e;
        }

    }


}

/**
 * Command to reject a message.
 */
class RejectCommand extends iCommand {

    
    private $caseId;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function useLastCaseId($lastId) {

    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            Database::changeCaseState($this->caseId, "REJECTED");
        }
    }

  public function canPerformCommand(){
        try{
        }catch(Exception $e){
                throw $e;
        }
  }


}

/**
 * Command to twitter a command, but not sending it.
 */
class TwitterCommand extends iCommand {

    
    private $caseId;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            Database::changeCaseState($case->getId(), "SENT");

//Send sms to all members
            Database::twitter($this->caseId);
        }
    }

  public function canPerformCommand(){
        try{
                Database::canTwitter($this->caseId);
        }catch(Exception $e){
                throw $e;
        }
  }
}

/**
 * Command to change the text of a command.
 */
class ChangeCommand extends iCommand {

    
    private $caseId;
    private $newText;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function getNewText() {
        return $this->newText;
    }

    public function setNewText($newText) {
        $this->newText = $newText;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case == null) {
            throw new Exception($GLOBALS['NO_SUCH_CASE']);
        }

        if ($case->getStatus() == "WAITING") {
            //Change case text
            Database::changeCaseText($this->caseId, $this->newText);

			//Set case state "sent"
            //Database::changeCaseState($this->caseId, "SENT");

//Send sms to all members
            //$db->sendToMembers($this->caseId);
           // $db->twitter($this->caseId);
        }
    }

  public function canPerformCommand(){
        try{
		$case = Database::getCaseById($this->caseId);
	        if ($case == null) {
        	    throw new Exception($GLOBALS['NO_SUCH_CASE']);
	        }

        }catch(Exception $e){
                throw $e;
        }
  }


}

/**
 * Command to ban a number from getting their smses relayed.
 */
class BanCommand extends iCommand {

    
    private $banNumber;
    private $fromNumber;
    private $messageId;

    public function getMessageId() {
        return $this->messageId;
    }

    public function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    public function getBanNumber() {
        return $this->banNumber;
    }

    public function setBanNumber($banNumber) {
        $this->banNumber = $banNumber;
    }

    public function getFromNumber() {
        return $this->fromNumber;
    }

    public function setFromNumber($fromNumber) {
        $this->fromNumber = $fromNumber;
    }

    public function useLastCaseId($lastId) {
        
    }

    public function performCommand() {
        Database::banNumber($this->banNumber);
//$case = new ControlCase("BAN " . $this->number, "BAN", "default", $this->messageId);
//Database::createCase($case);
        Database::sendSingleMessage($this->fromNumber, $GLOBALS['USER_HAS_BEEN_BANNED']);
    }


  public function canPerformCommand(){
        try{

        }catch(Exception $e){
                throw $e;
        }
  }



}

/**
 * Command to change the case type name of a message.
 */
class SetCaseTypeCommand extends iCommand {

    
    private $caseId;
    private $newCaseTypeName;

    public function getCaseId() {
        return $this->caseId;
    }

    public function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    public function getNewCaseTypeName() {
        return $this->newCaseTypeName;
    }

    public function setNewCaseTypeName($newCaseTypeName) {
        $this->newCaseTypeName = $newCaseTypeName;
    }

    public function useLastCaseId($lastId) {
        if ($this->caseId == $GLOBALS['PREVIOUS_CASE_ID']) {
            $this->caseId = $lastId;
        }
    }

    public function performCommand() {
        $case = Database::getCaseById($this->caseId);
        if ($case->getStatus() == 'WAITING') {
            Database::setCaseTypeName($this->caseId, $this->newCaseTypeName);
        }
    }


  public function canPerformCommand(){
        try{
        	Database::canSetCaseTypeName($this->caseId, $this->newCaseTypeName);
	}catch(Exception $e){
                throw $e;
        }
  }



}

class StatisticsCommand extends iCommand {
 
    
    private $fromNumber;

    public function getFromNumber() {
        return $this->fromNumber;
    }

    public function setFromNumber($fromNumber) {
        $this->fromNumber = $fromNumber;
    }

    public function useLastCaseId($lastId) {
        
    }

    public function performCommand() {
        $str = Database::getStatisticsAndData();
        Database::sendSingleMessage($this->fromNumber, $str);
//$db->createCase("STATISTICS", $str, $line['id']);
//$case = pg_fetch_array($db->getCaseByFirstMessageId($line['id']));
//$db->associateCaseAndInbox($case['id'], $line['id']);
    }

    
  public function canPerformCommand(){
        try{
        }catch(Exception $e){
                throw $e;
        }
  }


}

class UnsubscribeCommand extends iCommand {

    
    private $fromNumber;

    public function getFromNumber() {
        return $this->fromNumber;
    }

    public function setFromNumber($fromNumber) {
        $this->fromNumber = $fromNumber;
    }

    public function useLastCaseId($lastId) {
        
    }

    public function performCommand() {
        Database::removeMember($this->fromNumber);
        Database::sendSingleMessage($this->fromNumber, $GLOBALS['YOU_HAVE_QUIT']);

//$db->createCase("STATISTICS", $str, $line['id']);
//$case = pg_fetch_array($db->getCaseByFirstMessageId($line['id']));
//$db->associateCaseAndInbox($case['id'], $line['id']);
    }

	
  public function canPerformCommand(){
        try{
        }catch(Exception $e){
                throw $e;
        }
  }


}


class HelpCommand extends iCommand {

    
    private $fromNumber;

    public function getFromNumber() {
        return $this->fromNumber;
    }

    public function setFromNumber($fromNumber) {
        $this->fromNumber = $fromNumber;
    }

    public function useLastCaseId($lastId) {
        
    }

    public function performCommand() {
        Database::sendSingleMessage($this->fromNumber, $GLOBALS['USER_HELP_TEXT']);
    }

	
  public function canPerformCommand(){
        try{
        }catch(Exception $e){
                throw $e;
        }
  }
}





class ResubscribeCommand extends iCommand {
    private $fromNumber;

    public function getFromNumber() {
        return $this->fromNumber;
    }

    public function setFromNumber($fromNumber) {
        $this->fromNumber = $fromNumber;
    }

    public function useLastCaseId($lastId) {
        
    }

    public function performCommand() {
        Database::activateMember($this->fromNumber);
        Database::sendSingleMessage($this->fromNumber, $GLOBALS['YOU_ARE_REACTIVATED']);
    }

	
  public function canPerformCommand(){
        try{
        }catch(Exception $e){
                throw $e;
        }
  }


}





class UserSuggestionCommand extends iCommand {

    
    private $fromNumber;
    private $text;
    private $messageId;

    public function getFromNumber() {
        return $this->fromNumber;
    }

    public function setFromNumber($fromNumber) {
        $this->fromNumber = $fromNumber;
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function getMessageId() {
        return $this->messageId;
    }

    public function setMessageId($messageId) {
        $this->messageId = $messageId;
    } 

    public function performCommand() {

		$case = new ControlCase(0,$this->text,'WAITING','default',$this->messageId);
        
        //Create case.
        Database::createCase($case);

        $case = Database::getLastCreatedCase();

        //Send SMSes to admin group
        Database::sendToAdmins($case->getId() . ", " . $this->fromNumber . ", typ: " . $case->getCaseTypeName() . ", säger: " . $this->text);

        return $case->getId();
    }

    public function useLastCaseId($lastId) {
        
    }

  public function canPerformCommand(){
        try{
        }catch(Exception $e){
                throw $e;
        }
  }


}

?>
