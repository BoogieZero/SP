<?php 
include("view-template.class.php");

/*
View for editing users.
*/
class View_admin_users_edit_form extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Editace uživatele");
        $this->setHeader("Editace uživatele");
        
        $this->setPageContent($this->edit_form());
        
        Log::msg("data_inserted", __FILE__);
    }
    
    /*
    Gets clean value from user in req_data.
    @key    key for value
    @return value
    */
    private function getReqValue($key){
        $res = "";
        if($this->req_data !== null && isset($this->req_data['user'])){
            if(array_key_exists($key, $this->req_data['user'])){
                $res = $this->clean($this->req_data['user'][$key]);
            }
        }
        return $res;
    }
    
    /*
    Generates edit form. Alerts are set for apropriate labels based on flags in req_data.
    @return edit form
    */
    private function edit_form(){
        $res = "";
        ob_start();
?>
            <div class="w3-container w3-theme-d4 w3-center">
                <p class="no_margin_tb">
                    <?php echo $this->getReqValue("name") ?>
                </p>
            </div>
            <form action="index.php?page=admin" class="w3-container" method="post">
            
                <?php echo $this->alertForFailed("name"); ?>
                <p>
                <input class="w3-input w3-theme-l2" type="text" name="name" value="<?php echo $this->getReqValue("name"); ?>">
                <label>Jméno</label></p>    
                
                <?php echo $this->alertForFailed("login"); ?>
                <p>
                <input class="w3-input w3-theme-l2" type="text" name="login" value="<?php echo $this->getReqValue("login"); ?>">
                <label>Login</label></p>
                
                <?php echo $this->alertForFailed("email"); ?>
                <p>
                <input class="w3-input w3-theme-l2" type="text" name="email" value="<?php echo $this->getReqValue("email"); ?>">
                <label>Email</label></p>
                
                <p>
                <input class="w3-input w3-theme-l2" type="password" name="password" value="">
                <label>Heslo</label></p>
                
                <p>
                <select class="w3-btn w3-theme-d3 w3-input" name="permissions">
                  <option <?php echo $this->setSelected($this->getReqValue('level'), 3)?> value="3">Autor</option>
                  <option <?php echo $this->setSelected($this->getReqValue('level'), 2)?> value="2">Recenzent</option>
                  <option <?php echo $this->setSelected($this->getReqValue('level'), 1)?> value="1">Administrátor</option>
                </select>
                <label>Práva</label>
                </p>
                
                <input type="hidden" name="idusers" value="<?php echo $this->getReqValue("idusers") ?>">
                
                <input type="hidden" name="action" value="edit_user_acc">
                
                <input class="w3-container w3-cell w3-btn w3-theme-d3 w3-hover-theme2" type="submit" value="Potvrdit změny">
                              
            </form>
<?php
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Sets selected flag for same values.
    @return same values: "selected"
            otherwise empty string
    */
    private function setSelected($level, $value){
//        echo "lvl:".$level." val:".$value;
        if($value == $level) return "selected";
        return "";
    }
}
?>