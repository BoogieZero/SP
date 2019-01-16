<?php 
include("controller.class.php");

/*
Controller for topics.
*/
class Con_topics extends Controller{
    
    /*
    Returns page based on given attributes. Returns published
    contribution page by default.
    
    POST:
        edit_contr_acc  validate editation or                   new contributions
    GET:
        own_req         own contributions
        edit_contr_req  edit econtribution
        del_contr_req   delete contribution
    */
    public function work(){
        $this->req_data['user_level'] = $this->db->thisUserPermissionLevel();
        
        //post
        if(isset($_POST['action'])){
            Log::msg("POST request: ".$_POST['action'], __FILE__);
            
            if($_POST['action'] == 'edit_contr_acc' && isset($_POST['idcontributions'])){
                return $this->edit_contr_acc($_POST['idcontributions']);
            }
            
        }
        
        //request
        if(isset($_GET['request'])){
            
            if($_GET['request'] == 'own_req'){
                return $this->own_req();
            }
            if($_GET['request'] == 'edit_contr_req'){
                if(!isset($_GET['id']))
                    return $this->contr_edit_req();    
                return $this->contr_edit_req($_GET['id']);
            }
            if($_GET['request'] == 'del_contr_req' && isset($_GET['id'])){
                return $this->contr_delete_req($_GET['id']);
            }
        }
        
        return $this->topics_pub();
    }
    
    /*
    Gets contribution from databse with it's reviews.
    Creates array of contributions with it's reviews.
    @return     array of contributions
    */
    private function processContributions($data){
        $res = array();
        $id;
        $rev = array();
        
        foreach((array)$data as $row){
            $id = $row['idcontributions'];
            if(!array_key_exists($id, $res)){
                //new contribution
                $res[$id]['idcontributions'] = $row['idcontributions'];
                $res[$id]['name'] = $row['name'];
                $res[$id]['content'] = $row['content'];
                $res[$id]['file'] = $row['file'];
                $res[$id]['decision'] = $row['decision'];

            }
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
    Populates page for published contributions.
    @return published contributions page
    */
    private function topics_pub(){
        //published
        $data = $this->db->allContributions(false);
        
        $res = $this->processContributions($data);
        
        $this->req_data['contributions'] = $res;

        include_once("View/view-topics_pub.class.php");
        $view = new View_topics_pub($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Populates page for own contributions.
    @return own contributions page
    */
    private function own_req(){
        Log::msg("own topics", __FILE__);
        
        $logged = $this->db->loggedUser();
        $user = $logged['login'];
        $data = $this->db->userContributions($user);

        //if($data === null) return "";
        $res = $this->processContributions($data);
        
        $this->req_data['contributions'] = $res;
        
        include_once("View/view-topics_own.class.php");
        $view = new View_topics_own($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Deletes contribution with given id.
    Only for it's owner if the contribution wasn't published yet.
    @id     idcontributions
    @return own contributions page
    */
    private function contr_delete_req($id){
        Log::msg("delete request", __FILE__);
        $data = $this->db->idContribution($id);
      
        //owner?
        if(!$this->check_contribution_ownership($data["users_idusers"])){
            return "";  //failed
        }
        
        if($data['decision'] >= 1){
            //published
            return "";
        }
        
        $file = $data['file'];
        $this->deleteFile($file);
        
        if(!$this->db->deleteContribution($id)){
            //failed
            return "";
        }
        
        $this->req_data['delete_ok'] = "Téma bylo úspěšně smazáno.";
        return $this->own_req();
    }
    
    /*
    Populates page for edditing owned and not published contribution. In case the @id is set to empty string returned page is used as form for new contribution instead.
    @id     idcontribution
    @return page for edditing/creating contribution
    */
    private function contr_edit_req($id = ""){
        if($id == ""){
            //add new
            Log::msg("add request", __FILE__);
            $data = array();
        }else{
            //edit
            Log::msg("edit request", __FILE__);
            
            $data = $this->db->idContribution($id);
            //owner?
            if(!$this->check_contribution_ownership($data["users_idusers"])){
                return "";  //failed
            }

            if($data['decision'] >= 1){
                //published
                return "";
            }
        }
                
        //merge overwrite original data by req_data from previous attempts
        if(isset($this->req_data['contribution'])){
            $this->req_data['contribution'] = array_merge($data, $this->req_data['contribution']);
        }else{
            //original data from db
            $this->req_data['contribution'] = $data;
        }

        include("View/view-topics-edit-form.class.php");
        $view = new View_topics_edit_form($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Validation of data from edit/new form for contribution. For a new contribution is @id set to empty string. If new file is uploaded old vesion is deleted.
    During validation are set apropriate error values.
    @id     idcontribution
    @return in case of successful validation: own topics page
            otherwise edit/new contribution page
    */
    private function edit_contr_acc($id = ""){
        
        if($id == ""){
            //add contribution
            Log::msg("add contributions post", __FILE__);
            $data = array();
        }else{
            //edit contribution
            Log::msg("edit contributions post", __FILE__);
            $data = $this->db->idContribution($id);
            if(!$this->check_contribution_ownership($data["users_idusers"])){
                return "";  //failed
            }

            if($data['decision'] >= 1){
                //published
                return "";
            }
        }
    
        $name = $_POST['name'];
        $this->req_data['contribution']['name'] = $name;
        $content = $_POST['content'];
        $this->req_data['contribution']['content'] = $content;
        $failed = false;
        
        //name
        if(empty($name) === true){
            $this->req_data['name_f'] = "chybí jméno";
            $failed = true;
        }else{
            unset($this->req_data['name_f']);
        }
        
        //content
        if(empty($content) === true){
            $this->req_data['content_f'] = "chybí obsah";
            $failed = true;
        }else{
            unset($this->req_data['content_f']);
        }
        
        //file
        if(empty($data['file'])){
            $can_be_empty = false;
        }else{
            $can_be_empty = true;
            $oldFile = $data['file'];
        }
        
        //empty file?
        if($this->file_is_empty()){
            //file is empty
            if($can_be_empty){
                //ok
                $file = null;
            }else{
                //file has to be set
                $this->req_data['file_f'] = "nebyl vybrán soubor";
                return $this->contr_edit_req($id);
            }
        }else{
            //file is NOT empty
            $file = $this->file_check();
            if($file === false){
                //cannot be uploaded
                return $this->contr_edit_req($id);
            }
            //can be uploaded -> ok
        }
        
        if($failed){
            //back to form
            return $this->contr_edit_req($id);
        }
            
        //upload
        if($file !== null){
            if($this->file_upload() === false){
                //upload failed
                return $this->contr_edit_req($id);
            }
        }
        
        //edit, add
        if($id != ""){
            //edit existing
            if($this->db->editContribution($id, $name, $content, $file, null) === false){
                //failed
                $this->req_data['file_f'] = "záznam do databáze se nezdařil";
                //delete uploaded file
                $this->deleteFile($file);
                return $this->contr_edit_req($id);
            }else{
                //ok
                $this->req_data['edit_success'] = true;
                
                //delete old file
                if($file !== null)
                    $this->deleteFile($oldFile);
                
                return $this->own_req();
            }    
        }else{
            //add new
            $user = $this->db->loggedUser();
            $userId = $user['idusers'];
            
            if($this->db->addContribution($userId, $name, $content, $file) === false){
                //failed
                $this->deleteFile($file);
                $this->req_data['file_f'] = "záznam do databáze se nezdařil";
                return $this->contr_edit_req($id);
            }else{
                //ok
                $this->req_data['add_success'] = true;
                
                return $this->own_req();
            }
        }
        
    }
    
    /*
    Returns true if file was set in $_FILES.
    */
    private function file_is_empty(){
        if($_FILES['file']['error'] == UPLOAD_ERR_NO_FILE){
            return true;
        }else{
            return false;
        }
    }
    
    /*
    Checks if file in $_FILES has valid attributes.
    Files has to be .pdf and it has to have unique name on server.
    @return successful validation: filename
            otherwise false
    */
    private function file_check(){
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->req_data['file_f'] = "příliš velký soubor";
                return false;
            default:
                $this->req_data['file_f'] = "chyba při nahrávání";
                return false;
        }
        
        $target_dir = "Files/";
        $filename = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $filename;
        
        $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
        //pdf?
        if($fileType != "pdf"){
            $this->req_data['file_f'] = "soubor není typu .pdf";
            return false;
        }
        
        //exist?
        if(file_exists($target_file)){
            $this->req_data['file_f'] = "soubor s tímto názvem již existuje";
            return false;
        }
        
        return $filename;
    }
    
    /*
    Uploads file from $_FILES to server directory.
    @return true for successful upload
    */
    private function file_upload(){
        $target_dir = "Files/";
        $filename = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $filename;
        
        //upload
        if(!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)){
            //failed to upload
            $this->req_data['file_f'] = "chyba při nahrávání souboru";
            return false;
        }
        
        return true;
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
    Checks ownership of contribution with current user.
    @id     idcontribution
    @return true if user owns given contribution
    */
    private function check_contribution_ownership($id){
        if($_SESSION["user"]["idusers"] != $id){
                //failed
                return false;
        }
        return true;
    }
    
}

?>