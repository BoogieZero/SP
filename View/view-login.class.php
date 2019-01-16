<?php
include("view-template.class.php");

/*
View for login page.
*/
class View_login extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $logged = $this->req_data['logged'];
        $data = $this->req_data['data'];
        $register = $this->req_data['register'];
        
        if(!isset($this->req_data['user_level'])){
            $this->req_data['user_level'] = 0;
        }
        
        $this->setSidebar($this->req_data['user_level']);       
        
        if($register === false){
            //login
            $this->login($logged, $data);
        }else{
            //register
            $this->register();
        }
        
        Log::msg("data inserted", __FILE__);
    }
    
    /*
    Sets page content based on state of user (logged, failed login)
    @logged true if user tried to login
    @data   data information about logged user
    */
    private function login($logged, $data){
        $this->setTitle("Přihlášení");
        
        //want to login
        if($logged == false && $data == null){
            $this->setHeader("Přihlášení");
            $this->setPageContent($this->loginForm());
        }
    
        //failed to login
        if($logged == true && $data == null){
            $this->setHeader("Přihlášení");
            $this->setAlert('Chyba!', 'špatný login nebo heslo', 'w3-red');
            $this->setPageContent($this->loginForm());
        }
        
        //successfuly logged
        if($logged == true && $data != null){
            $this->setHeader("Přihlášen");
            $this->setPageContent($this->showLogged($data));
        }
    }
    
    /*
    Sets page content for register form.
    */
    private function register(){
        $this->setTitle("Registrace");
        $this->setHeader("Registrace");
        $this->setPageContent($this->regForm());
    }
    
    /*
    Generates content for displaying logged user info.
    @data   information about logged user
    @return generated content
    */
    private function showLogged($data){
        $res = "";
        
        $res .= $this->logged_top($this->clean($data['name']));
        
        $res .= '<div class="w3-responsive">';
        $res .= '<table class="w3-table">';
        $res .= "<tr><td>Jméno:</td><td>".$this->clean($data['name'])."</td></tr>";
        $res .= "<tr><td>Login:</td><td>".$this->clean($data['login'])."</td></tr>";
        $res .= "<tr><td>email:</td><td>".$this->clean($data['email'])."</td></tr>";
        $res .= '</table>';
        
        $res .= "</div>";
                
        return $res;
    }
    
    /*
    Generates bar with logged user name including logou button.
    @name   logged user name
    @return generated bar
    */
    private function logged_top($name){
        $res = "";
        ob_start();
?>
            <div class="w3-bar w3-theme-d2">
                <div class="w3-bar-item">Přihlášen jako:</div>
                <div class="w3-bar-item">
                    <?php echo $this->clean($name);?>
                </div>
                <a href="index.php?page=login&amp;request=logout" class="w3-bar-item w3-button w3-theme-d3 w3-hover-theme2">Odhlásit se</a>
            </div>
<?php
        $res .= ob_get_clean();
        return $res;
    }
    
    
    /*
    Gets clean value from req_data.
    @key    key for value
    @return value
    */
    private function getReqValue($key){
        $res = "";
        if($this->req_data !== null){
            if(array_key_exists($key, $this->req_data)){
                $res = $this->req_data[$key];
            }
        }
        return $this->clean($res);
    }
    
    /*
    Generates ragistration form. Alerts are set for apropriate labels based on flags in req_data.
    @return registration form
    */
    private function regForm(){
        $res = "";
        
        ob_start();
?>
        <form action="index.php?page=login" class="w3-container" method="post">
            
            <?php echo $this->alertForFailed("name"); ?>
            <p>
            <input class="w3-input w3-theme-l2" type="text" name="name" value="<?php echo $this->getReqValue("name"); ?>">
            <label>Jméno</label></p>
            
            <?php echo $this->alertForFailed("email"); ?>
            <p>
            <input class="w3-input w3-theme-l2" type="text" name="email" value="<?php echo $this->getReqValue("email"); ?>">
            <label>email</label></p>
            
            <?php echo $this->alertForFailed("login"); ?>
            <p>
            <input class="w3-input w3-theme-l2" type="text" name="login" value="<?php echo $this->getReqValue("login"); ?>">
            <label>Login</label></p>

            <?php echo $this->alertForFailed("password1"); ?>
            <p>
            <input class="w3-input w3-theme-l2" type="password" name="password1">
            <label>Heslo</label></p>
            
            <?php echo $this->alertForFailed("password2"); ?>
            <p>
            <input class="w3-input w3-theme-l2" type="password" name="password2">
            <label>Heslo znovu</label></p>
            
            <input type="hidden" name="action" value="register">
            
            <input class="w3-container w3-cell w3-btn w3-theme-d3 w3-hover-theme2" type="submit" value="Registrovat">
        </form>
<?php
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates login form.
    @return login form
    */
    private function loginForm(){
        $res = "";
        
        ob_start();
?>
    
        <form action="index.php?page=login" class="w3-container" method="post">
            <p>
            <input class="w3-input w3-theme-l2" type="text" name="login">
            <label>Login</label></p>

            <p>
            <input class="w3-input w3-theme-l2" type="password" name="password">
            <label>Heslo</label></p>
            
            <input type="hidden" name="action" value="login">
            
            <input class="w3-cell w3-btn w3-container w3-theme-d3 w3-hover-theme2 floatLeft" type="submit" value="Přihlásit">

            <a href="index.php?page=login&amp;request=register" class="w3-cell w3-btn w3-container w3-theme-d3 w3-hover-theme2 floatRight">Registrovat</a>
            
        </form>
<?php        
        $res = ob_get_clean();
        return $res;
    }
}
?>