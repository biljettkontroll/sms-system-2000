<?php
/**
 *
 * @author pok
 */
abstract class iCommand {

    private $entireText;

    public abstract function performCommand();
    public abstract function useLastCaseId($lastId);
    
    public abstract function canPerformCommand();

    public function setCommandText($entireText){
        $this->entireText = $entireText;
    }

    public function getCommandText(){
        return $this->entireText;
    }
}
?>
