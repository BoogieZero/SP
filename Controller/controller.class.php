<?php
include_once($_SERVER['DOCUMENT_ROOT']."/SP/Model/data.class.php");

include_once($_SERVER['DOCUMENT_ROOT']."/SP/log.class.php");

/*
Abstract class for every controller.
*/
abstract class Controller{
    protected $db;  //database reference
    protected $req_data = array();  //data for view from controller
    
    /*
    Instantiates controller.
    */
    public function __construct(){
        $this->db = Data::getInstance();
    }
    
    /*
    Entry point for controller.
    */
    public abstract function work();
    
}
?>
