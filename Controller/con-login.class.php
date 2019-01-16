<?php
include("controller.class.php");

/*
Controller for login.
*/
class Con_login extends Controller{
    
    /*
    Returns page based on given attributes. Returns login page by default.
    
    POST:
        login       validation of login
        register    validation of register
    GET:
        register    new user registration
    */
    public function work(){
        $this->req_data['user_level'] = $this->db->thisUserPermissionLevel();
        
        //logged?
        if($this->db->isUserLogged()){
            $result = $this->login_done();
            return $result;
        }
        
        //post?
        if(isset($_POST['action'])){
            Log::msg("POST request: ".$_POST['action'], __FILE__);
            if($_POST['action'] == 'login'){    //login
                return $this->login_acc();
            }
            if($_POST['action'] == 'register'){ //register
                return $this->register_acc();
            }
            
        }
        
        //request?
        if(isset($_GET['request']) && $_GET['request'] == 'register'){
            return $this->register_req();
        }
                
        
        return $this->login_req();
    }
    
    /*
    Creates login page.
    */
    private function login_req(){
        Log::msg("login request", __FILE__);
        include_once("View/view-login.class.php");
        $this->req_data['logged'] = false;
        $this->req_data['data'] = null;
        $this->req_data['register'] = false;        
        $view = new View_login($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Populates page for logged user.
    @return logged user: user info page
            otherwise login page
    */
    private function login_done(){
        Log::msg("already logged", __FILE__);
        
        //logout?
        if(isset($_GET['request']) && $_GET['request'] == 'logout'){
            //logout
            $this->db->userLogout();
            $this->req_data["user_level"] = 0;
        }else{
            $this->req_data["user_level"] = $this->db->thisUserPermissionLevel();
        }
        
        //user data
        $user = $this->db->loggedUser();
        if($user == null){
            //no active user
            return $this->login_req();  
        }
        
        //display info
        include("View/view-login.class.php");
        $this->req_data['logged'] = true;
        $this->req_data['data'] = $user;
        $this->req_data['register'] = false;
        $view = new View_login($this->req_data);
        return $view->getPage();
    }
    
    /*
    Validation of data from login page.
    @return successfuly logged: user infor page
            otherwise login page
    */
    private function login_acc(){
        Log::msg("login post", __FILE__);
        $login = $_POST['login'];
        $login = htmlspecialchars($login);
        $pass = $_POST['password'];
        $pass = htmlspecialchars($pass);
        
        //login check
        if(!$this->db->userLogin($login, $pass)){
            //failed
            include_once("View/view-login.class.php");
            $this->req_data['logged'] = true;
            $this->req_data['data'] = null;
            $this->req_data['register'] = false;
            $view = new View_login($this->req_data);
            return $view->getPage();
        }else{
            //success
            return $this->login_done();
        }
    }
    
    /*
    Populates page for registration.
    @return register page
    */
    private function register_req(){
        Log::msg("register request", __FILE__);
        include_once("View/view-login.class.php");
        
        $this->req_data['logged'] = false;
        $this->req_data['data'] = null;
        $this->req_data['register'] = true;
        $view = new View_login($this->req_data);
        return $view->getPage();
    }
    
    /*
    Validation of data from register page. During validation apropriate error flags are set.
    @return successful registration: login page
            otherwise register page
    */
    private function register_acc(){
        Log::msg("register post", __FILE__);
        
        $failed = false;
        
        //name
        $name = trim($_POST['name']);
        $this->req_data['name'] = $name;
        if(empty($name) === true){
            $this->req_data['name_f'] = "chybí jméno";
            $failed = true;
        }else{
            unset($this->req_data['name_f']);
        }
        
        //email
        $email = trim($_POST['email']);
        $this->req_data['email'] = $email;
        if(empty($email) === true || strpos($email, '@') === false){
            $this->req_data['email_f'] = "neplatný email";
            $failed = true;
        }else{
            unset($this->req_data['email_f']);
        }
        
        //login
        $login = trim($_POST['login']);
        $this->req_data['login'] = $login;
        if(empty($login) === true){
            $this->req_data['login_f'] = "chybí login";
            $failed = true;
        }elseif($this->db->userInfo($login) !== null){
            $req_data['login_f'] = "login je obsazený";
            $failed = true;
        }else{
            unset($this->req_data['login_f']);            
        }
        
        //password1
        $psw1 = trim($_POST['password1']);
//        $req_data['password1'] = $psw;
        if(empty($psw1) === true){
            $this->req_data['password1_f'] = "chybí heslo";
            $failed = true;
        }else{
            unset($this->req_data['password1_f']);   
        }
        
        //passwod2
        $psw2 = trim($_POST['password2']);
//        $req_data['password2'] = $psw;
        if(empty($psw2) === true){
            $this->req_data['password2_f'] = "chybí heslo";
            $failed = true;
        }elseif($psw1 != $psw2){
            $this->req_data['password2_f'] = "hesla se neshodují";
            $failed = true;
        }else{
            unset($this->req_data['password2_f']);   
        }
        
        
        if($failed){
            return $this->register_req();    
        }else{
            $this->db->addUser($_POST['login'], $_POST['name'], $_POST['password1'], $_POST['email'], 3);
            unset($this->req_data);
            return $this->login_req();
        }
    }
}
?>
