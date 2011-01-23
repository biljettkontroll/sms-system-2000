<?php
class ControlCase{
    private $id;
    private $text;
    private $status;
    private $caseTypeName; 
    private $firstInboxId;
 

    public function __construct($id,$text,$status,$caseTypeName,$firstInboxId) {
        $this->setId($id);
        $this->setText($text);
        $this->setStatus($status);
        $this->setCaseTypeName($caseTypeName);
        $this->setFirstInboxId($firstInboxId);
    }
 

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getCaseTypeName() {
        return $this->caseTypeName;
    }

    public function setCaseTypeName($caseTypeName) {
        $this->caseTypeName = $caseTypeName;
    }

    public function getFirstInboxId() {
        return $this->firstInboxId;
    }

    public function setFirstInboxId($firstInboxId) {
        $this->firstInboxId = $firstInboxId;
    }



}
?>