<?php
include("view-template.class.php");

/*
View for displaying all topics.
*/
class View_admin_topics extends Template{
    
    //index counter
    private $i = 0;
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Všechna témata");
        $this->setHeader("Všechna témata");
        
        $this->setScript($this->getAccordionScript());
        $this->setScript($this->getFileReaderScript());
        
        //alerts
        if(isset($req_data['delete_ok']))
            $this->setAlert("Informace!", $req_data['delete_ok'], "w3-green");
        if(isset($req_data['file_delete_ok']))
            $this->setAlert("Informace!", $req_data['file_delete_ok'], "w3-green");
        if(isset($req_data['file_delete_failed']))
            $this->setAlert("Upozornění!", $req_data['file_delete_failed'], "w3-amber");
        if(isset($req_data['assign_success'])){
            if($req_data['assign_success'] == true){
                $this->setAlert("Informace!", "Přiřazení proběhlo úspěšně.", "w3-green");
            }else{
                $this->setAlert("Upozornění!", "Chyba při přiřazení.", "w3-amber");
            }
        }
        if(isset($req_data['edit_success'])){
            if($req_data['edit_success'] == true){
                $this->setAlert("Informace!", "Změna proběhla úspěšně.", "w3-green");
            }else{
                $this->setAlert("Upozornění!", "Chyba při změně.", "w3-amber");
            }
        }
        
        
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
        
        $res .= $this->createModal("assignDropdown", $this->getDropdownContent());
        
        $res .= $this->createModal("fileReader", "");
        
        $res .= '<div class="w3-container w3-theme-d4 w3-center"><p class="no_margin_tb">';
        $res .= 'Otevřená témata';
        $res .= '</p></div>';
        $res .= $this->createContributionsTab($this->req_data['contributions_pending']);
        
        $res .= '<div class="w3-margin-top w3-container w3-theme-d4 w3-center"><p class="no_margin_tb">';
        $res .= 'Uzavřená témata';
        $res .= '</p></div>';
        $res .= $this->createContributionsTab($this->req_data['contributions_done']);
        
        return $res;
    }
    
    /*
    Generates content for reviewer picker.
    @return reviewer picker content
    */
    private function getDropdownContent(){
        $res = "";
        ob_start();
?>
            
            <form action="index.php?page=admin" class="w3-container" method="post">  
                <div class="w3-center w3-theme-d4">Výběr recenzenta</div>
                <p>
                <select size="7" class="w3-theme-d3 w3-input" name="idusers">
<?php
        $res .= ob_get_clean();
        foreach($this->req_data['reviewers'] as $rev){
            $res .= '<option value="'.$rev['idusers'].'">';
            $res .= $rev['login'].'('.$rev['name'].')';
            $res .= "</option>";
        }
        
        ob_start();
?>
                </select>
                <label>Recenzent</label>
                </p>
                
                <input id="input_assign_id" type="hidden" name="idcontributions">
                
                <input type="hidden" name="action" value="review_assign">
                
                <input class="w3-container w3-cell w3-btn w3-theme-d3 w3-hover-theme2" type="submit" value="Potvrdit změny">
                              
            </form>
<?php
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates contributions tab.
    @contributions  array of contributions
    @return contributions table
    */
    private function createContributionsTab($contributions){
        $res = "";
        
        $res .= '<div class="w3-card-4 w3-container w3-responsive">';
        $res .= '<table class="w3-table contr_table">';
        $res .= '<tr>
                    <th>Název</th>
                    <th>Obsah</th>
                    <th>Soubor</th>
                    <th>Publikováno</th>
                    <th>Recenze</th>
                    <th>Smazat</th>
                </tr>';
        
        $res .= $this->createContributions($contributions);
        
        $res .= '</table>';
        $res .= '</div>';
    
        return $res;
    }
    
    /*
    Generates contributions rows.
    @data   array of contributions
    @return rows for contributions tab
    */
    private function createContributions($data){
        $res = "";

        if(empty($data)) return "";
        
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
            $res .= $this->wrapTd($this->getAccordionButton($this->i));
            $res .= $this->wrapTd($this->getDeleteButton($contr));
            
            $res .= "</tr>";
            $res .= $this->createReviews($this->i, $contr['reviews'], $contr['idcontributions']);
            
            $this->i++;
        }
        
        return $res;
    }
    
    /*
    Generates reviews tab.
    @index              index used as part of id for button
    @reviews            array of reviews
    @idcontributions    id of associated contribution
    $return gnerated reviews tab
    */
    private function createReviews($index, $reviews, $idcontribution){
        $res = "";
        ob_start();
?>
        <tr class="contr_row">
            <td colspan="6" id="<?php echo "th_show_$index";?>" class="w3-container w3-hide">
                <div class="w3-responsive w3-card-4">
                    <table class="w3-table">
                        <tr>
                            <td colspan="7">
<?php
        $res .= ob_get_clean();
        $res .= $this->getAssignDropButton($idcontribution);
        ob_start();
?>
                            </td>
                        </tr>
                        <tr>
                            <th>recenzent</th>
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
    Creates review row for reviews table.
    @row    array with review data
    @return gneated row
    */
    private function createReviewRow($row){
        $res = "";
        $res .= "<tr>";
        $res .= $this->wrapTd(
            $this->clean($row['login']));
        $res .= $this->wrapTd(
            $this->clean($row['originality']));
        $res .= $this->wrapTd(
            $this->clean($row['subject']));
        $res .= $this->wrapTd(
            $this->clean($row['grammar']));
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
            include("scripts-accordion.js");
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
            include("scripts-modal.js");
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
    
    /*
    Generates delete button for given contribution.
    @contr  contribution data
    @return generated dele button
    */
    private function getDeleteButton($contr){
        
        $id = $contr['idcontributions'];
        $res = "";
        ob_start();
?>
            <a href="index.php?page=admin&amp;request=del_contr_req&amp;id=<?php echo $id?>" class="w3-btn w3-block w3-theme-d3 w3-hover-theme2">Smazat</a>
<?php
        $res .= ob_get_clean();   
        return $res;
    }
    
    /*
    Generates button for assigning new review to given contribution
    @idcontribution target contribution
    @return generated assign button
    */
    private function getAssignDropButton($idcontribution){
        $res = "";
        $res .= '<div onclick="modal_show_assign(';
        $res .= "'assignDropdown','$idcontribution'";
        $res .= ')" class="w3-btn w3-block w3-theme-d4 w3-hover-theme2">';
        $res .= "Přiřadit recenzenta";
        $res .= '</div>';
        return $res;
    }
    
    /*
    Creates button with decision value for given contribution.
    @contr  target contributions
    */
    private function getDecisionValue($contr){
        if($contr['decision'] >= 1){
            $val = "ANO";
        }else{
            $val = "NE";
        }
        
        $id = $contr['idcontributions'];
        
        $res = "";
        ob_start();
?>
            <a href="index.php?page=admin&amp;request=switch_decision&amp;id=<?php echo $id?>" class="w3-btn w3-block w3-theme-d3 w3-hover-theme2"><?php echo $val;?></a>
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
}
?>