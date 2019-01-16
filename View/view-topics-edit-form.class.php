<?php
include("view-template.class.php");

/*
View for editing owned contribution.
*/
class View_topics_edit_form extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Editace tématu");
        $this->setHeader("Editace tématu");
        
        $this->setScript($this->getChooseFileScript());
        
        $this->setPageContent($this->edit_form());
        
        Log::msg("data_inserted", __FILE__);
    }
    
    /*
    Gets clean value from contributions in req_data.
    @key    key for value
    @return value
    */
    private function getReqValue($key){
        $res = "";
        if($this->req_data !== null && isset($this->req_data['contribution'])){
            if(array_key_exists($key, $this->req_data['contribution'])){
                $res = $this->clean($this->req_data['contribution'][$key]);
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
//        print_r($this->req_data);
        ob_start();
?>
            <form action="index.php?page=topics" class="w3-container" method="post" enctype="multipart/form-data">
            
                <?php echo $this->alertForFailed("name"); ?>
                <p>
                <input class="w3-input w3-theme-l2" type="text" name="name" value="<?php echo $this->getReqValue("name"); ?>">
                <label>Název</label></p>    
                
                <?php echo $this->alertForFailed("content"); ?>
                <p>
                <input class="w3-input w3-theme-l2" type="text" name="content" value="<?php echo $this->getReqValue("content"); ?>">
                <label>Obsah</label></p>
                
                <?php echo $this->alertForFailed("file"); ?>
                <p>
                <label for="file_inpt_1" id="file_inpt_1_lb" class="w3-input w3-theme-l2 w3-hover-theme2">
                <?php echo $this->getReqValue("file"); ?>&nbsp;
                </label>
                <input id="file_inpt_1" style="display:none" type="file" name="file" accept="application/pdf"  onchange="chooseFile('file_inpt_1')">
                <label>Soubor</label></p>   
                
                <input type="hidden" name="idcontributions" value="<?php echo $this->getReqValue("idcontributions") ?>">
                
                <input type="hidden" name="action" value="edit_contr_acc">
                
                <input class="w3-container w3-cell w3-btn w3-theme-d3 w3-hover-theme2" type="submit" value="Potvrdit změny">
                
                <?php echo $this->getDeleteButton(); ?>                
            </form>
<?php
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates button for deletion of contribution if the contributions was not published yet.
    @return delete button
    */
    private function getDeleteButton(){
        $contr = $this->req_data['contribution'];
        //not published
        if(isset($contr['decision']) && $contr['decision'] >= 1) return "";
        //new contribution
        if(isset($contr['idcontributions']) == false) return "";
        
        $id = $this->getReqValue('idcontributions');
        
        $res = "";
        $res .= '<a href="index.php?page=topics&request=del_contr_req&id=';
        $res .= "$id". '" ';
        $res .= 'class="w3-container w3-cell w3-btn w3-theme-d3 w3-hover-theme2 floatRight">Smazat téma</a>';
        return $res;
    }
    
    /*
    Gets choose file script. Used for updating filename in from based on filepicker.
    @return choose file script
    */
    private function getChooseFileScript(){
        $res = "";
        ob_start();
            include("scripts-choose-file.js"); //show toggle
        $res .= ob_get_clean();
        return $res;
    }
}
?>