<?php

include_once 'CommandFactory.php';


 

class Sms {

    private $id;
    private $fromNumber;
    private $text;
    private $fromAdmin;
    private $commands;
    private $parseError = false;

    public function __construct($id, $text, $fromNumber, $fromAdmin) {
        $this->setId($id);
		$this->setText($text);
        $this->setFromNumber($fromNumber);
        $this->setFromAdmin($fromAdmin);
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

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
		$this->text = $this->removeMultipleSpaces($text);
    }

    public function getFromAdmin() {
        return $this->fromAdmin;
    }

    public function setFromAdmin($fromAdmin) {
        $this->fromAdmin = $fromAdmin;
    }

    public function getCommands() {
        return $this->commands;
    }
	
	/**
	* Removes multiple spaces from message to prevent syntax problems.
	*/
	private function removeMultipleSpaces($str){
		$oldstr = "";
		//While we got a result, keep removing spaces.
		while($oldstr != $str){
			$oldstr = $str;
			$str = str_replace("  ", " ", $str);
		}	
		return $str;
	}

    /**
     * Gets all the commands from this sms. These are stored in the $commands array.
     */
    public function parseCommands() {	
		
		 
		Helper::dbgOut('  "' . $this->text . '"');
        if ($this->fromAdmin) {
			
            $commandParts = explode($GLOBALS['COMMAND_SPLIT_TOKEN'], $this->text);
			Helper::dbgOut('   From admin. ' . count($commandParts) . " command parts");
			
            foreach ($commandParts as $commandPart) {
				$commandPart = trim($commandPart);
				Helper::dbgOut('   Parsing admin command part: ' . $commandPart);
				
				try{
					$command = CommandFactory::parseAdminCommand($commandPart, $this->fromNumber, $this->id);
				}catch(Exception $e){
					$this->parseError = true;
					throw $e; //Rethrow exception
				}
                
                $this->commands[] = $command;
            }
        } else {		
			/*			
			if($GLOBALS['USE_MESSAGE_CONFIRMATION']){
				if(substr($this->fromNumber,0,3)=='+46' || substr($this->fromNumber,0,1)=='0'){
					Database::sendSingleMessage($this->fromNumber, $GLOBALS['MESSAGE_CONFIRMATION']);
				}
			}*/

            //From user. Do not separate |
			Helper::dbgOut('   From user');
            try{
				$command = CommandFactory::parseUserCommand($this->text, $this->fromNumber, $this->id);
			}catch(Exception $e){
				throw $e; //Rethrow exception
			}
            $this->commands[] = $command;
        }
    }
 
	/**
	* Go through all previously created commands and perform them.
	* (It would be better to separate this into two parts, one which 
	* only checks if it is possible to run them, throws exception otherwise
	* and one which actually runs the command. But it would be a bit of a hassle to change.)
	**/
    public function performAllCommands() {


	//This is the prerun, creates cases and checks if shit can run.
        $lastId = null;
        if($this->commands != null && is_array($this->commands) && !$this->parseError){
                foreach ($this->commands as $command) {


                    if ($lastId != null) {
                        $command->useLastCaseId($lastId);
                     }

                        try{
                                $result = $command->canPerformCommand();
                        }catch(Exception $e){
                                throw $e; //Rethrow exception
                        }


                        if (is_numeric($result)) {
                                //The command resulted in a created send message case. Save for later use.
                                $lastId = $result;
                        }
                }

		//Here wedo the actual sending 
	        foreach ($this->commands as $command) {
	
			try{			
				$result = $command->performCommand();
			}catch(Exception $e){
				throw $e; //Rethrow exception
			}
		}
      }
    }

}

?>
