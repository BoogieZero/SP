<?php
include("controller.class.php");

/*
Controller for admins.
*/
class Con_admin extends Controller{
    
    /*
    Returns page based on given attributes. Only users with a administrator level privileges are allowed here.
    
    POST:
        edit_user_acc   editation of user account
        review_assign   assignment of a new review
    GET:
        users               manage users
        edit_user           edit user
        topics              manage topics
        del_contr_req       delete contribution
        switch_decision     published switch
    */
    public function work(){
        $this->req_data['user_level'] = $this->db->thisUserPermissionLevel();
        
        if($this->req_data['user_level'] != 1 ){
            //only for admins
            return "";
        }
        
        //post
        if(isset($_POST['action'])){
            Log::msg("POST request: ".$_POST['action'], __FILE__);
            
            if($_POST['action'] == 'edit_user_acc' && isset($_POST['idusers'])){
                return $this->edit_user_acc($_POST['idusers']);
            }
            
            if($_POST['action'] == 'review_assign'){
                return $this->review_assign();   
            }
            
        }
        
        //request
        if(isset($_GET['request'])){
            
            if($_GET['request'] == 'users'){
                return $this->users();
            }
            if($_GET['request'] == 'edit_user_req' && isset($_GET['id'])){
                return $this->edit_user_req($_GET['id']);
            }
            
            if($_GET['request'] == 'topics'){   
                return $this->topics();
            }
            if($_GET['request'] == 'del_contr_req' && isset($_GET['id'])){
                return $this->contr_delete_req($_GET['id']);
            }
            if($_GET['request'] == 'switch_decision' && isset($_GET['id'])){   
                return $this->switch_decision($_GET['id']);
            }
        }
        
        //invalid
        return "";
    }
    
    /*
    Switches published flag on contribution given by id.
    @id     idcontribution
    @return all topics page
    */
    private function switch_decision($id){
        $data = $this->db->idContribution($id);
        if($data === null){
            $this->req_data['edit_success'] = false;
            return $this->topics();  
        } 
        
        if($data['decision'] >= 1){
            $dec = 0;    
        }else{
            $dec = 1;
        }
        $ok = $this->db->editContribution($id, null, null, null, $dec);
        
        if($ok === false){
            $this->req_data['edit_success'] = false;
        }
        
        $this->req_data['edit_success'] = true;
        return $this->topics();
    }
    
    /*
    Assigns new review for given contribution to target user.
    @return all topics page
    */
    private function review_assign(){
        $idusers = $_POST['idusers'];
        $idcontr = $_POST['idcontributions'];
        
        if($this->db->assignReview($idusers, $idcontr) === false){
                $this->req_data['assign_success'] = false;
        }else{
            $this->req_data['assign_success'] = true;
        }
        return $this->topics();
    }
    
    /*
    Creates array of all published/not published contributions and it's reviews with login of reviewer.
    @pending    true for published
    @array of all contributions
    */
    private function getContributions($pending){
        $data = $this->db->allContributions($pending);
        
        if($data === null) return null;
        
        $res = array();
        $id;
        $rev = array();
        
        foreach($data as $row){
            $id = $row['idcontributions'];
            if(!array_key_exists($id, $res)){
                //new contribution
                $res[$id]['idcontributions'] = $row['idcontributions'];
                $res[$id]['name'] = $row['name'];
                $res[$id]['content'] = $row['content'];
                $res[$id]['file'] = $row['file'];
                $res[$id]['decision'] = $row['decision'];
            }
            //review
            $rev['login'] = $row['login'];
            $rev['originality'] = $row['originality'];
            $rev['subject'] = $row['subject'];
            $rev['grammar'] = $row['grammar'];
            $rev['correctness'] = $row['correctness'];
            $rev['comment'] = $row['comment'];
            $rev['recommend'] = $row['recommend'];
            
            $res[$id]['reviews'][] = $rev;
        }
        
        return $res;
    }
    
    /*
    Deletes given contribution.
    @id     idcontribution
    @return all topics page
    */
    private function contr_delete_req($id){
        Log::msg("delete request", __FILE__);
        
        $data = $this->db->idContribution($id);
        $file = $data['file'];
        $this->deleteFile($file);
        
        if(!$this->db->deleteContribution($id)){
            //failed
            return "";
        }
        
        $this->req_data['delete_ok'] = "Téma bylo úspěšně smazáno.";
        return $this->topics();
    }
    
    /*
    Deletes file from server directory and sets appropriate flags.
    @file   filename for deletion
    */
    private function deleteFile($file){
        if($file === null) return;
        $target_dir = "Files/";
        $target_file = $target_dir.$file;
        if(file_exists($target_file)){
            if(unlink($target_file)){
                $this->req_data['file_delete_ok'] = "Starý soubor '$file' byl úspěšně smazán.";
            }else{
                $this->req_data['file_delete_failed'] = "Starý soubor '$file' se nepodařilo smazat.";
            }
        }else{
            $this->req_data['file_delete_failed'] = "Starý soubor '$file' již neexistuje.";
        }
    }
    
    /*
    Populates page for all contributions.
    @return all topics page
    */
    private function topics(){
        $this->req_data['contributions_pending'] = $this->getContributions(true);
        $this->req_data['contributions_done'] = $this->getContributions(false);
        $this->req_data['reviewers'] = $this->db->reviewers();
        
        include_once("View/view-admin_topics.class.php");
        $view = new View_admin_topics($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Populates page for all users.
    @return all users page
    */
    private function users(){
        $data = $this->db->users();
        if($data === null) return "";
        
        $this->req_data['users'] = $data;
        
        include_once("View/view-admin-users.class.php");
        $view = new View_admin_users($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Populates page for edditing user given by @id.
    @id     idusers
    @return page for edditing user
    */
    private function edit_user_req($id){
        $data = $this->db->idUser($id);
        if($data === null) return "";
        
        $this->req_data['user'] = $data;
        
        include_once("View/view-admin-user-edit-form.class.php");
        $view = new View_admin_users_edit_form($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Validation of data from edit user form. During validation apropriate flags are set.
    @id idusers
    @return successful edit: all users page
            otherwise edit user page
    */
    private function edit_user_acc($id){
        
        $data = array();

        $name = $_POST['name'];
        $data['name'] = $name;
        $login = $_POST['login'];
        $data['login'] = $login;
        $email = $_POST['email'];
        $data['email'] = $email;
        $password = $_POST['password'];
        $level = $_POST['permissions'];
        $data['level'] = $level;
        
        $failed = false;
        
        //name
        if(empty($name) === true){
            $this->req_data['name_f'] = "chybí jméno";
            $failed = true;
        }else{
            unset($this->req_data['name_f']);
        }
        
        //login
        if(empty($login) === true){
            $this->req_data['login_f'] = "chybí login";
            $failed = true;
        }else{
            unset($this->req_data['login_f']);
        }
        
        //email
        if(empty($email) === true || strpos($email, '@') === false){
            $this->req_data['email_f'] = "neplatný email";
            $failed = true;
        }else{
            unset($this->req_data['email_f']);
        }
        
        //password
        if(empty($password)){
            $password = null;
        }
        
        $this->req_data['user'] = $data;
        
        if($failed){
            return $this->edit_user_req($id);
        }else{
            if($this->db->editUser($id, $name, $login, $password, $email, $level) === false){
                //failed
                $this->req_data['edit_success'] = false;
            }else{
                //ok
                $this->req_data['edit_success'] = true;
            }
            return $this->users();
        }
        
    }
}
?>