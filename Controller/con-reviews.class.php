<?php 
include("controller.class.php");

/*
Controller for reviews.
*/
class Con_reviews extends Controller{
    
    /*
    Returns page based on given attributes. Returns own reviews page by default.
    
    POST:
        edit_rev_acc    validate editation
    GET:
        edit_rev_req    editation
    */
    public function work(){
        $this->req_data['user_level'] = $this->db->thisUserPermissionLevel();
        
        //unauthorized author or guest
        if($this->req_data['user_level'] > 2)
            return "";
        
        //post
        if(isset($_POST['action'])){
            Log::msg("POST request: ".$_POST['action'], __FILE__);
            if($_POST['action'] == 'edit_rev_acc'){    //login
                return $this->edit_acc($_POST['idreviews']);
            }
        }
        
        //request
        if(isset($_GET['request'])){
            if($_GET['request'] == 'edit_rev_req'){
                return $this->edit_req($_GET['id']);
            }
        }
        
        return $this->own_req();
    }
    
    /*
    Populates page for own reviews.
    @return own reviews page
    */
    private function own_req(){
        Log::msg("own reviews", __FILE__);
        
        $logged = $this->db->loggedUser();
        $user = $logged['login'];
        //pending
        $data = $this->db->userReviews($user, true);

        $this->req_data['reviews_pending'] = $data;
        
        //published
        $data = $this->db->userReviews($user, false);
        $this->req_data['reviews_done'] = $data;
        
        include_once("View/view-reviews.class.php");
        $view = new View_reviews($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Checks ownership of given review with a current user.
    @id     idreviews
    @return true if user owns given review
    */
    private function check_review_ownership($id){
        if($_SESSION["user"]["idusers"] != $id){
                //failed
                return false;
        }
        return true;
    }
    
    /*
    Populates page for edditing owned and not published review. 
    @id     idreviews
    @return page for edditing review
    */
    private function edit_req($id){
        Log::msg("edit request", __FILE__);
        
        $data = $this->db->idReview($id);
        
        if(!$this->check_review_ownership($data["users_idusers"])){
            return "";  //failed
        }
        
        if($data['decision'] >= 1){
            //published
            return "";
        }
        
        $this->req_data['review'] = $data;
        include("View/view-reviews-edit-form.class.php");
        $view = new View_reviews_edit_form($this->req_data);
        $result = $view->getPage();
        return $result;
    }
    
    /*
    Validation of data from edit form for review.
    During validation are set apropriate error values.
    @id     idreviews
    @return in case of successful validation: own reviews page
            otherwise edit review page
    */
    private function edit_acc($id){
        Log::msg("edit review post", __FILE__);

        $data = $this->db->idReview($id);
        
        if(!$this->check_review_ownership($data["users_idusers"])){
            return "";  //failed
        }
        
        if($data['decision'] >= 1){
            //published
            return "";
        }
        
        $orig = $_POST['originality'];
        $subj = $_POST['subject'];
        $gram = $_POST['grammar'];
        $corr = $_POST['correctness'];
        $comm = $_POST['comment'];
        $reco = $_POST['recommend'];
        
        $failed = false;
        
        //originality
        if(!$this->inRange($orig)){
            $this->req_data['originality_f'] = "Hodnota musí být v rozmezí 0-9.";
            $failed = true;
        }else{
            unset($this->req_data['originality_f']);
        }
        
        //subject
        if(!$this->inRange($subj)){
            $this->req_data['subject_f'] = "Hodnota musí být v rozmezí 0-9.";
            $failed = true;
        }else{
            unset($this->req_data['subject_f']);
        }
        
        //grammar
        if(!$this->inRange($gram)){
            $this->req_data['grammar_f'] = "Hodnota musí být v rozmezí 0-9.";
            $failed = true;
        }else{
            unset($this->req_data['grammar_f']);
        }
        
        //correctness
        if(!$this->inRange($corr)){
            $this->req_data['correctness_f'] = "Hodnota musí být v rozmezí 0-9.";
            $failed = true;
        }else{
            unset($this->req_data['correctness_f']);
        }
        
        //recommend
        if(!$this->inRange($reco)){
            $this->req_data['recommendation_f'] = "Hodnota musí být v rozmezí 0-9.";
            $failed = true;
        }else{
            unset($this->req_data['recommendation_f']);
        }
        
        if($failed){
            return $this->edit_req($id);
        }
        
        if($this->db->editReview($id, $orig, $subj, $gram, $corr, $comm, $reco) === false){
            //failed
            $this->req_data['edit_success'] = false;
            return $this->own_req();
        }
        
        $this->req_data['edit_success'] = true;
        return $this->own_req();
    }
    
    /*
    Checks if the given value is in range (0, 9)
    @value  value
    @return true if @value is in the range
    */
    private function inRange($value){
        if($value == "") return true;
        if($value > 9 || $value < 0){
            return false;
        }
        return true;
    }
}
?>