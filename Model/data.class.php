<?php

include_once($_SERVER['DOCUMENT_ROOT']."/SP/log.class.php");

/*
Represents data model. Provides access to data from database.
*/
class Data{
    //singleton instance
    private static $_instance;
    
    //db
    private static $db_server = "localhost";
    private static $db_name = "web_db";
    private static $db_user = "root";
    private static $db_pass = "";

    //    PDO object
    private $db = null;

    /*
    Returns the only instance of this class.
    @return database object isntance
    */
    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /*
    Instantiates database object
    */
    public function __construct(){
        
        try{
            $this->db = new PDO("mysql:host=".self::$db_server.";dbname=".self::$db_name, self::$db_user, self::$db_pass);
            Log::msg("Connected: ".self::$db_server." ".self::$db_name, __FILE__);
        }catch(PDOException $e){
            Log::msg("DB failed to connect", __FILE__);
            die("Could not connect to DB");
        }
        Log::msg("DB instantiated", __FILE__);

    }    
    
    /*
    Gets info about user given by login.
    @login  user login
    @return user info, null for error
    */
    public function userInfo($login){
        $q = "SELECT * FROM users WHERE login = :login";
        $data = $this->db->prepare($q);
        $data->bindParam(':login', $login);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
                
        if(count($data) != 1){
            Log::msg("duplicates userInfo", __FILE__);
            return null;  
        } 
                
        if(empty($data)) return null;
        return $data[0];
    }
    
    /*
    Validates given password for login.
    @login  user login
    @pass   user password
    @return true for success
    */
    public function isPasswordCorrect($login, $pass){
        $data = $this->userInfo($login);
        if($data == null) return false;
        
        if($data['password'] == $pass){
            return true;
        }else{
            return false;
        }
    }
    
    /*
    Attepts to login user.
    @login  user login
    @pass   user password
    @return true for success
    */
    public function userLogin($login, $pass){
        if(!$this->isPasswordCorrect($login, $pass)){
            //wrong password or username
            return false;
        }else{
            //save user
            $info = $this->userInfo($login);
            $_SESSION["user"] = array();
            $_SESSION["user"]['idusers'] = $info['idusers'];
            $_SESSION["user"]['name'] = $info['name'];
            $_SESSION["user"]['email'] = $info['email'];
            $_SESSION["user"]['login'] = $info['login'];
            Log::msg("user logged in", __FILE__);
            return true;
        }
    }
    
    /*
    Logout current user.
    */
    public function userLogout(){
        if(isset($_SESSION["user"])){
            unset($_SESSION["user"]);
        }
        Log::msg("user logged out", __FILE__);
    }
    
    /*
    Gets currect user info from session.
    @return user info from session
    */
    public function loggedUser(){
        if($this->isUserLogged() === false){
            return null;
        }
        return $_SESSION["user"];
    }
    
    /*
    Returns true if uses is logged.
    @return true for logged user
    */
    public function isUserLogged(){
        if(isset($_SESSION["user"])){
            return true;
        }
        return false;
    }
    
    /*
    Returns permission level of current user.
    @return permission level of currecnt user
    */
    public function thisUserPermissionLevel(){
        
        if($this->isUserlogged()){
            $lvl = $this->userPermissionsLevel($_SESSION["user"]['login']);
            
            if($lvl !== null) return $lvl;
            
        }
        return 0;
    }
    
    /*
    Gets permission level for user with given login.
    @login  user login
    @return permission level
    */
    public function userPermissionsLevel($login){
        $idperm = $this->userInfo($login)['permissions_idpermissions'];
        
        $q = "SELECT `level` FROM permissions WHERE permissions.idpermissions=$idperm";
        
        $data = $this->db->prepare($q);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
        
        if(count($data) != 1){
            Log::msg("duplicates userPermissions", __FILE__);
            return null;  
        }
        return $data[0]['level'];
    }
    
    /*
    Adds new user.
    @login          login
    @name           username
    @pass           password
    @email          email
    @idpermission   id of permission level
    */
    public function addUser($login, $name, $pass, $email, $idpermission){
        Log::msg("insert user", __FILE__);
        $q = "INSERT INTO users (`idusers`, `name`, `login`, `password`, `email`, `permissions_idpermissions`) VALUES (NULL, :name, :login, :password, :email, :idpermissions)";
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":name", $name);
        $data->bindParam(":login", $login);
        $data->bindParam(":password", $pass);
        $data->bindParam(":email", $email);
        $data->bindParam(":idpermissions", $idpermission);    
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
    
    /*
    Gets all users taht are able to create reviews.
    @return array of reviewers
    */
    public function reviewers(){
        $q='SELECT users.idusers, users.name, users.login, users.email, users.permissions_idpermissions, permissions.level
            FROM users
            LEFT JOIN permissions
            ON users.permissions_idpermissions = permissions.idpermissions
            WHERE permissions.level <= 2';
        
        $data = $this->db->prepare($q);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
        
        if(empty($data)) return null;
        
        return $data;
    }
    
    /*
    Gets all users.
    @return array of users
    */
    public function users(){
        $q='SELECT users.idusers, users.name, users.login, users.email, users.permissions_idpermissions, permissions.level
            FROM users
            LEFT JOIN permissions
            ON users.permissions_idpermissions = permissions.idpermissions';
        
        $data = $this->db->prepare($q);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
        
        if(empty($data)) return null;
        
        return $data;
    }
    
    /*
    Gets info about user given by id.
    @id     user id
    @return user info
    */
    public function idUser($id){
        $q="SELECT users.idusers, users.name, users.login, users.email, users.permissions_idpermissions, permissions.level
            FROM users
            LEFT JOIN permissions
            ON users.permissions_idpermissions = permissions.idpermissions 
            WHERE users.idusers = :id";
        
        $data = $this->db->prepare($q);
        $data->bindParam(':id', $id);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
                
        if(count($data) != 1){
            Log::msg("duplicates userInfo", __FILE__);
            return null;  
        } 
                
        if(empty($data)) return null;
        return $data[0];
    }
    
    /*
    Gets contribution with given id.
    @id     idcontributions
    @return contribution info
    */
    public function idContribution($id){
        $q="SELECT * FROM contributions WHERE idcontributions = :id";
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();

        if(empty($data)) return null;
        
        if(count($data) != 1){
            Log::msg("duplicates contributions", __FILE__);
            return null;  
        }
        
        return $data[0];
    }
    
    /*
    Deletes contribution with given id.
    @id     idcontributions
    @return true for success
    */
    public function deleteContribution($id){
        Log::msg("delete contribution", __FILE__);
        $q="DELETE FROM contributions WHERE idcontributions = :id;";
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
    
    /*
    Adds new contribtuion.
    @id         idusers
    @name       name
    @content    content
    @file       file
    */
    public function addContribution($id, $name, $content, $file){
        Log::msg("add contribution", __FILE__);
        $q="INSERT INTO contributions (users_idusers, name, content, file)
            VALUES (:id, :name, :content, :file)";
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        $data->bindParam(":name", $name);
        $data->bindParam(":content", $content);
        $data->bindParam(":file", $file);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
    
    /*
    Edits contribution with given id on non null attributes.
    @id         idcontributions
    @name       name
    @content    content
    @file       filename
    @decision   decision
    @return     true for success
    */
    public function editContribution($id, $name, $content, $file, $decision){
        Log::msg("edit contribution", __FILE__);
        $q='UPDATE contributions SET ';
        $set = "";
        if($name !== null) $set .= "name = :name,";
        if($content !== null) $set .= "content = :content,";
        if($file !== null) $set .= "file = :file,";
        if($decision !== null) $set .= "decision = :decision,";
        $q .= substr($set, 0, -1);  //cut the last ','
        $q .= ' WHERE idcontributions = :id;';
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        if($name !== null) 
            $data->bindParam(":name", $name);
        if($content !== null) 
            $data->bindParam(":content", $content);
        if($file !== null) 
            $data->bindParam(":file", $file);
        if($decision !== null) 
            $data->bindParam(":decision", $decision);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
   
    /*
    Gets contributions owned by user with given login.
    @login  user login
    @return owned contributions
    */
    public function userContributions($login){
        $id = $this->userInfo($login)['idusers'];
        $q="SELECT idcontributions, name, content, file, decision, reviews.originality, reviews.subject, reviews.grammar, reviews.correctness, reviews.comment, reviews.recommend
        FROM contributions
        LEFT JOIN reviews
        ON contributions.idcontributions = reviews.contributions_idcontributions
        WHERE contributions.users_idusers = :id";
        
        $data = $this->db->prepare($q);
                
        $data->bindParam(":id", $id);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
        
        if(empty($data)) return null;
        
        return $data;
    }
    
    /*
    Gets all contributions.
    @pending    true for published
                false for not published
    @return array of contributions
    */
    public function allContributions($pending){
        $q="SELECT idcontributions, contributions.name, content, file, decision, reviews.users_idusers, reviews.originality, reviews.subject, reviews.grammar, reviews.correctness, reviews.comment, reviews.recommend, users.login
            FROM contributions
            LEFT JOIN reviews
            ON contributions.idcontributions = reviews.contributions_idcontributions
            LEFT JOIN users
            ON users.idusers = reviews.users_idusers
            WHERE contributions.decision ";
        if($pending){
            $q .= "= 0";
        }else{
            $q .= "!= 0";
        }
        
        $data = $this->db->prepare($q);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
        
        if(empty($data)) return null;
        
        return $data;
    }
    
    /*
    Adds new review to target user for given contribution.
    @idusers            target user id
    @idcontributions    id of the contribution
    */
    public function assignReview($idusers, $idcontributions){
        Log::msg("assign review", __FILE__);
        $q="INSERT INTO reviews (reviews.users_idusers, reviews.contributions_idcontributions)
VALUES(:idu, :idc)";
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":idu", $idusers);
        $data->bindParam(":idc", $idcontributions);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
    
    /*
    Edits review on non null attributes.
    @id             idreviews
    @originality    originality
    @subject        subject
    @grammar        grammar
    @correctness    correctness
    @comment        comment
    @recommend      recommend
    @return     true for success
    */
    public function editReview($id, $originality, $subject, $grammar, $correctness, $comment, $recommend){
        Log::msg("edit review", __FILE__);
        
        $q='UPDATE reviews SET ';
        $set = "";
        if($originality !== null) $set .= "originality = :originality,";
        if($subject !== null) $set .= "subject = :subject,";
        if($grammar !== null) $set .= "grammar = :grammar,";
        if($correctness !== null) $set .= "correctness = :correctness,";
        if($comment !== null) $set .= "comment = :comment,";
        if($recommend !== null) $set .= "recommend = :recommend,";
        $q .= substr($set, 0, -1);  //cut the last ','
        $q .= ' WHERE idreviews = :id;';
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        if($originality !== null) 
            $data->bindParam(":originality", $originality);
        if($subject !== null) 
            $data->bindParam(":subject", $subject);
        if($grammar !== null) 
            $data->bindParam(":grammar", $grammar);
        if($correctness !== null) 
            $data->bindParam(":correctness", $correctness);
        if($comment !== null) 
            $data->bindParam(":comment", $comment);
        if($recommend !== null) 
            $data->bindParam(":recommend", $recommend);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
    
    /*
    Gets info for review given by id.
    @id     idreviews
    @return review info
    */
    public function idReview($id){
        $q="SELECT contributions.idcontributions, contributions.name, contributions.content, contributions.file, contributions.decision, reviews.idreviews, reviews.users_idusers, reviews.originality, reviews.subject, reviews.grammar, reviews.correctness, reviews.comment, reviews.recommend 
            FROM reviews
            LEFT JOIN contributions
            ON reviews.contributions_idcontributions = contributions.idcontributions
            WHERE idreviews = :id";
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();

        if(empty($data)) return null;
        
        if(count($data) != 1){
            Log::msg("duplicates reviews", __FILE__);
            return null;  
        }
        
        return $data[0];
    }
    
    /*
    Gets reviews for user with given login.
    @login      user login
    @pending    true for non published reviews
                false for published reviews
    @return     array of reviews
    */
    public function userReviews($login, $pending){
        
        $id = $this->userInfo($login)['idusers'];
        $q="SELECT *
            FROM reviews
            LEFT JOIN contributions
            ON reviews.contributions_idcontributions = contributions.idcontributions
            WHERE reviews.users_idusers = :id
            AND contributions.decision ";
        if($pending){
            $q .= "= 0";
        }else{
            $q .= "!= 0";
        }
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $id);
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return null;
        }
        
        $data = $data->fetchAll();
        if(empty($data)) return null;
        
        return $data;
    }
    
    /*
    Edits user on non null attributes.
    @userid         idusers
    @name           name
    @login          login
    @pass           password
    @email          email
    @idpermissions  id of perrmisions level
    @return true for success
    */
    public function editUser($userid, $name, $login, $pass, $email, $idPermission){
        Log::msg("edit user", __FILE__);
        
        $q='UPDATE users SET ';
        $set = "";
        if($name !== null) $set .= "name = :name,";
        if($login !== null) $set .= "login = :login,";
        if($pass !== null) $set .= "password = :password,";
        if($email !== null) $set .= "email = :email,";
        if($idPermission !== null) $set .= "permissions_idpermissions = :permissions,";
        $q .= substr($set, 0, -1);  //cut the last ','
        $q .= ' WHERE idusers = :id;';
        
        $data = $this->db->prepare($q);
        
        $data->bindParam(":id", $userid);
        if($name !== null) 
            $data->bindParam(":name", $name);
        if($login !== null) 
            $data->bindParam(":login", $login);
        if($pass !== null) 
            $data->bindParam(":password", $pass);
        if($email !== null) 
            $data->bindParam(":email", $email);
        if($idPermission !== null) 
            $data->bindParam(":permissions", $idPermission);
        
        
        
        if($data->execute() === false){
            Log::msg("query err: ".print_r($data->errorInfo(), true), __FILE__);
            return false;
        }else{
            return true;
        }
    }
}


?>
