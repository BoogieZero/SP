<?php
include("view-template.class.php");
/*
View for displaying intro.
*/
class View_intro extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->setSidebar($req_data['user_level']);
        $this->setTitle("Úvod");
        $this->setHeader("Úvod");
        $this->setPageContent($this->getBody());
        Log::msg("data inserted", __FILE__);
    }
    
    /*
    Generates content of this page.
    */
    private function getBody(){
        $res = "";
        ob_start();
?>
            <h1>Vítejte v konferečním systému WEB</h1>
            <p>Stránky byly vytvořeny jako součást semestrání práce předmětu KIV/WEB.</p>
<?php
        $res .= ob_get_clean();
        return $res;
    }
}
?>