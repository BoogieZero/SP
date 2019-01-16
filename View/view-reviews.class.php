<?php
include("view-template.class.php");

/*
View for displaying owned reviews.
*/
class View_reviews extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Vlatní recenze");
        $this->setHeader("Vlastní recenze");
        
        //alerts
        if(isset($req_data['edit_success'])){
            if($req_data['edit_success'] === true){
                $this->setAlert("Informace!", "Změny úspěšně uloženy.", "w3-green");    
            }else{
                $this->setAlert("Chyba!", "Změny se nepodařilo uložit.", "w3-red");
            }   
        }
        $this->setScript($this->getFileReaderScript());
        
        $this->setPageContent($this->own_content());
        
        Log::msg("data_inserted", __FILE__);
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
    Generates edit button for edditing review.
    @rev    array with review data
    @return generated edit button
    */
    private function getEditButton($rev){
        if($rev['decision'] >= 1){
            return "ANO";  
        } 
        $id = $rev['idreviews'];
        $res = "";
        ob_start();
?>
            <a href="index.php?page=reviews&amp;request=edit_rev_req&amp;id=<?php echo $id?>" class="w3-btn w3-block w3-theme-d3 w3-hover-theme2">Editace</a>
<?php
        $res .= ob_get_clean();   
        return $res;
    }
    
    /*
    Generates row for review table.
    @rev    array with review data
    @return generated row
    */
    private function createReviewRow($rev){
        $res = "";
        $res .= "<tr>";
        $res .= $this->wrapTd($rev['name']);
        $res .= $this->wrapTd($this->getFileReaderButton($rev['file']));
        $res .= $this->wrapTd($this->getEditButton($rev));
        $res .= $this->wrapTd($rev['originality']);
        $res .= $this->wrapTd($rev['subject']);
        $res .= $this->wrapTd($rev['grammar']);
        $res .= $this->wrapTd($rev['correctness']);
        $res .= $this->wrapTd($rev['comment']);
        $res .= $this->wrapTd($rev['recommend']);
        $res .= "</tr>";
        return $res;
    }
    
    /*
    Generates reviews table.
    @reviews    array of reviews
    @return     generate reviews table
    */
    private function createReviews($reviews){
        $res = "";
        ob_start();
?>
            <div class="w3-responsive w3-card-4">
                <table class="w3-table rev_table">
                    <tr>
                        <th>Název</th>
                        <th>Soubor</th>
                        <th>Publikováno</th>
                        <th>Originalita</th>
                        <th>Předmět</th>
                        <th>Gramatika</th>
                        <th>Korektnost</th>
                        <th>Komentář</th>
                        <th>Doporučeno</th>
                    </tr>
<?php
        $res .= ob_get_clean();
        if($reviews != null){
            foreach($reviews as $rev){       
            $res .= $this->createReviewRow($rev);           
            }    
        }
        ob_start();
?>  
                </table>
            </div>
<?php          
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates content of this page. Two tables with reviews published and not published.
    */
    private function own_content(){
        $res = "";
        
        $res .= $this->createModal("fileReader", "");
        
        $res .= '<div class="w3-container w3-theme-d4 w3-center"><p class="no_margin_tb">';
        $res .= 'Otevřená témata';
        $res .= '</p></div>';
        $res .= $this->createReviews($this->req_data['reviews_pending']);
        
        $res .= '<div class="w3-margin-top w3-container w3-theme-d4 w3-center"><p class="no_margin_tb">';
        $res .= 'Uzavřená témata';
        $res .= '</p></div>';
        $res .= $this->createReviews($this->req_data['reviews_done']);
        
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