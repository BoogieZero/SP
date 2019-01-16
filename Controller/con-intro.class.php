<?php
include("controller.class.php");

/*
Controller for intro page.
*/
class Con_intro extends Controller{
    public function work(){
        $this->req_data["user_level"] = $this->db->thisUserPermissionLevel();
        Log::msg("page request", __FILE__);
        include("View/view-intro.class.php");
        $view = new View_intro($this->req_data);
        return $view->getPage();
    }
    
}
?>