<?php
include("view-template.class.php");

/*
View for displaying owned topics.
*/
class View_topics_own extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Vlatní témata");
        $this->setHeader("Vlastní témata");
        
        //alerts
        if(isset($req_data['edit_success'])){
            if($req_data['edit_success'] === true ){
                $this->setAlert("Informace!", "Změny úspěšně uloženy.", "w3-green");    
            }else{
                $this->setAlert("Chyba!", "Změny se nepodařilo uložit.", "w3-red");    
            }   
        }
        if(isset($req_data['add_success']))
            $this->setAlert("Informace!", "Nové téma úspěšně přidáno.", "w3-green");
        if(isset($req_data['delete_ok']))
            $this->setAlert("Informace!", $req_data['delete_ok'], "w3-green");
        if(isset($req_data['file_delete_ok']))
            $this->setAlert("Informace!", $req_data['file_delete_ok'], "w3-green");
        if(isset($req_data['file_delete_failed']))
            $this->setAlert("Upozornění!", $req_data['file_delete_failed'], "w3-amber");
        
        $this->setScript($this->getAccordionScript());
        $this->setScript($this->getFileReaderScript());
        
        $this->setPageContent($this->own_content());
        
        Log::msg("data_inserted", __FILE__);
    }
    
    /*
    Generates page content.
    Table with contributions.
    @return page content
    */
    private function own_content(){
        $res = "";
        
        $res .= $this->createModal("fileReader", "");
        
        $res .= $this->getAddButton();
        
        $res .= '<div class="w3-card-4 w3-container w3-responsive">';
        $res .= '<table class="w3-table contr_table">';
        $res .= '<tr>
                    <th>Název</th>
                    <th>Obsah</th>
                    <th>Soubor</th>
                    <th>Publikováno</th>
                    <th>Recenze</th>
                    <th>Editace</th>
                </tr>';
        
        $res .= $this->createContributions();
        
        $res .= '</table>';
        $res .= '</div>';
        
        
        
        return $res;
    }
    
    /*
    Populates table by contributions from req_data.
    $return rows with contributions and it's reviews.
    */
    private function createContributions(){
        $res = "";
        
        $data = $this->req_data['contributions'];
        
        if(empty($data)) return "";
        
        $i = 0;

        foreach($data as $contr){
            $res .= "<tr>";
            $res .= $this->wrapTd(
                $this->clean($contr['name']));
            $res .= $this->wrapTd(
                $this->clean($contr['content']));
            $res .= $this->wrapTd($this->getFileReaderButton(
                $this->clean($contr['file'])));
            $res .= $this->wrapTd(
                $this->getDecisionValue($contr));
            $res .= $this->wrapTd($this->getAccordionButton($i));
            $res .= $this->wrapTd($this->getEditButton($contr));
            
            $res .= "</tr>";
            $res .= $this->createReviews($i, $contr['reviews']);
            
            $i++;
        }
        
        return $res;
        
    }
    
    /*
    Returns text value of decision from given contribution.
    @return text value of decision for given contribution
    */
    private function getDecisionValue($contr){
        if($contr['decision'] >= 1){
            return "ANO";
        }else{
            return "NE";
        }
    }
    
    /*
    Generates table with reviews in one row of parrent table.
    @return reviews table in row
    */
    private function createReviews($index, $reviews){
        $res = "";
        ob_start();
?>
        
        <tr class="contr_row">
            <td colspan="6" id="<?php echo "th_show_$index";?>" class="w3-container w3-hide">
                <div class="w3-responsive w3-card-4">
                    <table class="w3-table">
                        <tr>
                            <th>originalita</th>
                            <th>předmět</th>
                            <th>gramatika</th>
                            <th>korektnost</th>
                            <th>komentář</th>
                            <th>doporučeno</th>
                        </tr>
<?php
        $res .= ob_get_clean();
        foreach($reviews as $rev){       
            $res .= $this->createReviewRow($rev);           
        }   
        ob_start();
?>  
                    </table>
                </div>
            </td>
        </tr>
<?php          
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates one row for reviews table.
    */
    private function createReviewRow($row){
        $res = "";
        $res .= "<tr>";
        $res .= $this->wrapTd(
            $this->clean($row['originality']));
        $res .= $this->wrapTd(
            $this->clean($row['subject']));
        $res .= $this->wrapTd($row['grammar']);
        $res .= $this->wrapTd(
            $this->clean($row['correctness']));
        $res .= $this->wrapTd(
            $this->clean($row['comment']));
        $res .= $this->wrapTd(
            $this->clean($row['recommend']));
        $res .= "</tr>";
        return $res;
    }
    
    /*
    Gets script to display reviews table.
    @return script for accordion toggle
    */
    private function getAccordionScript(){
        $res = "";
        ob_start();
            include("scripts-accordion.js");  //show toggle
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Gets script to display modal window.
    @return script for modal window toggle
    */
    private function getFileReaderScript(){
        $res = "";
        ob_start();
            include("scripts-modal.js"); //show toggle
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates accordion toggle button with id combines with @i.
    @i      id index
    @return accordion toggle button
    */
    private function getAccordionButton($i){
        $res = "";
        
        $res .= "<button";
        $res .= " id='th_light_$i'";
        
        $res .= ' onclick="accordionSwitch(';
        $res .= "'th_show_$i',"."'th_light_$i'";
        $res .= ')"';
        
        $res .= 'class="w3-btn w3-block w3-theme-d3 w3-hover-theme2">';
        $res .= "Recenze";
        $res .= "</button>";
        
        return $res;
    }
    
    /*
    Generates filereader button for given file.
    @file   filename
    @return filereader button
    */
    private function getFileReaderButton($file){
        if($file == "") return "";
        
        $res = "";
        $res .= $this->createModalButton("fileReader", $file, $file);
        return $res;
    }
    
    private function getEditButton($contr){
        if($contr['decision'] >= 1) return "";
        $id = $contr['idcontributions'];
        $res = "";
        ob_start();
?>
            <a href="index.php?page=topics&amp;request=edit_contr_req&amp;id=<?php echo $id?>" class="w3-btn w3-block w3-theme-d3 w3-hover-theme2">Editace</a>
<?php
        $res .= ob_get_clean();   
        return $res;
    }
    
    /*
    Wraps given content in <td></td> tags.
    $return wrapped @content
    */
    private function wrapTd($content){
        $res = "<td>".$content."</td>";
        return $res;
    }
    
    /*
    Generates button for adding new contributions.
    @return add button
    */
    private function getAddButton(){
        $res = "";
        ob_start();
?>
            <a href="index.php?page=topics&amp;request=edit_contr_req" class="w3-btn add_btn w3-block w3-theme-d3 w3-hover-theme2">Přidat nové téma</a>
<?php
        $res .= ob_get_clean();
        return $res;
    }
}
?>