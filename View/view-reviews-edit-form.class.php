<?php 
include("view-template.class.php");

/*
View for edditing own reviews.
*/
class View_reviews_edit_form extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Editace recenze");
        $this->setHeader("Editace recenze");
        
        
        $this->setPageContent($this->edit_form());
        
        Log::msg("data_inserted", __FILE__);
    }
    
    /*
    Gets clean value from review in req_data.
    @key    key for value
    @return value
    */
    private function getReqValue($key){
        $res = "";
        if($this->req_data !== null && isset($this->req_data['review'])){
            if(array_key_exists($key, $this->req_data['review'])){
                $res = $this->clean($this->req_data['review'][$key]);
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
            <form action="index.php?page=reviews" class="w3-container" method="post">
            
                <?php echo $this->alertForFailed("originality"); ?>
                <p class="w3-container w3-section">
                    <input name="originality" type="range" min="0" max="9" value="<?php echo $this->getReqValue("originality"); ?>" class="slider w3-input w3-theme-l2">
                    <label>Originalita</label>
                </p>
                                
                <?php echo $this->alertForFailed("subject"); ?>
                <p class="w3-container w3-section">
                    <input name="subject" type="range" min="0" max="9" value="<?php echo $this->getReqValue("subject"); ?>" class="slider w3-input w3-theme-l2">
                    <label>Předmět</label>
                </p>
                
                <?php echo $this->alertForFailed("grammar"); ?>
                <p class="w3-container w3-section">
                    <input name="grammar" type="range" min="0" max="9" value="<?php echo $this->getReqValue("grammar"); ?>" class="slider w3-input w3-theme-l2">
                    <label>Gramatika</label>
                </p>
                
                <?php echo $this->alertForFailed("correctness"); ?>
                <p class="w3-container w3-section">
                    <input name="correctness" type="range" min="0" max="9" value="<?php echo $this->getReqValue("correctness"); ?>" class="slider w3-input w3-theme-l2">
                    <label>Korektnost</label>
                </p>
                
                <?php echo $this->alertForFailed("comment"); ?>
                <p class="w3-container w3-section">
                    <input class="w3-input w3-theme-l2" type="text" name="comment" value="<?php echo $this->getReqValue("comment"); ?>">
                    <label>Komentář</label>
                </p>
                
                <?php echo $this->alertForFailed("recommend"); ?>
                <p class="w3-container w3-section">
                    <input name="recommend" type="range" min="0" max="9" value="<?php echo $this->getReqValue("recommend"); ?>" class="slider w3-input w3-theme-l2">
                    <label>Doporučení</label>
                </p>
                
                <input type="hidden" name="idreviews" value="<?php echo $this->getReqValue("idreviews") ?>">
                
                <input type="hidden" name="action" value="edit_rev_acc">
                
                <input class="w3-container w3-cell w3-btn w3-theme-d3 w3-hover-theme2" type="submit" value="Potvrdit změny">
            </form>
<?php
        $res .= ob_get_clean();
        return $res;
    }
}
?>