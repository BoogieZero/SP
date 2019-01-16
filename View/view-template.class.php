<?php
include_once($_SERVER['DOCUMENT_ROOT']."/SP/log.class.php");

/*
Template class for view.
*/
abstract class Template{
    
    //data from controller
    protected $req_data = array();
    
    abstract function __construct($req_data);    
    
//    final product
    protected $head = "";
    protected $body = "";
    
//    attributes
    protected $title = "";
    protected $page_header = "";
    protected $alert = "";
    protected $page_content = "";
    
//    inserts
    private $insert_head = "";
    private $insert_script = "";
    private $insert_body = "";
    private $insert_page_header = "";
    private $insert_content_header = "";
    
    /*
    Generate complete page.
    @return whole page
    */
    public function getPage(){
        return $this->generatePage();
    }
    
    /*
    Appends head for a page.
    */
    protected function createHead(){
        $res = "";
        $res = "<!DOCTYPE html>";
        $res .= '<html lang="cs">';
        $res .= "<head>";
        $res .= "<title>$this->title</title>";
        ob_start();        
//        meta
?>
            <meta charset="UTF-16">
            <meta name="description" content="description">
            <meta name="keywords" content="key words">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
            <link rel="stylesheet" href="w3-my-theme.css">
            <link rel="stylesheet" href="styles.css">
<?php
        $res .= ob_get_clean();
        $res .= $this->insert_head;     //head insert
        $res .= "<script>";
        $res .= $this->insert_script;   //script insert    
        $res .= "</script>";
        $res .= "</head>";              //end of header
        $this->head .= $res;
    }
    
    /*
    Appends body for a page including footer.
    */
    protected function createBody(){
        $res = "";
        $res .= '<body class="w3-theme-l1">';
        $res .= $this->insert_body;     //body insert
        $res .= '<div id="main">';      //main container
        
        //header
        $res .= '<div id="main_header" class="w3-row">';
        $res .= $this->insert_page_header;
        $res .= '<div class="w3-rest w3-container w3-theme-d3">';
        $res .= '<p class="w3-xlarge">'."$this->page_header</p>";
        $res .= "</div>";
        $res .= "</div>";

        $res .= $this->alert;           //alert

        $res .= '<div id="main_content" class="w3-container">'; //content
        $res .= $this->insert_content_header;
        $res .= $this->page_content;    //page content          
        $res .= "</div>";                //end of content
        

        $res .= "</div>";               //end of the main
        $res .= '<footer style="padding-right: 15px">';         //footer
        $res .= '<footer class="floatRight"><p>Copyright &copy; Martin Hamet</p></footer>';
        $res .= "</footer>";
        $res .= "</body>";
       
        $this->body .= $res;
    }
    
    /*
    Sets page header.
    @page_header    page header
    */
    protected function setHeader($page_header){
        $this->page_header .= $page_header;
        Log::msg("set header", __FILE__);
    }
    
    /*
    Appends header content to header insert.
    @content_header header content
    */
    protected function setContentHeader($content_header){
        $res = "";
        ob_start();
?>
        <div class="w3-content w3-large w3-theme2-d3 w3-padding-small">
            <h2><?php echo "$content_header"; ?></h2>
        </div>
<?php
        $res .= ob_get_clean();
        $this->insert_content_header .= $res;
    }
    
    /*
    Generates buttons for side bar based on given permission level.
    @level  permission level
    @return additional buttons for sidebar
    */
    private function fillSidebar($level){
        $res = "";
        //no breaks
        switch($level){
            case 1:
                $res .= '<a href="index.php?page=admin&amp;request=users" class="w3-bar-item w3-button w3-hover-theme2">Uživatelé</a>';
                $res .= '<a href="index.php?page=admin&amp;request=topics" class="w3-bar-item w3-button w3-hover-theme2">Všechna témata</a>';
            case 2: 
                $res .= '<a href="index.php?page=reviews" class="w3-bar-item w3-button w3-hover-theme2">Vlastní recenze</a>';
            case 3: 
                $res .= '<a href="index.php?page=topics&amp;request=own_req" class="w3-bar-item w3-button w3-hover-theme2">Vlastní témata</a>';
            case 0:
            default:
        }
        return $res;
    }
    
    /*
    Generates sidebar for page based on given permission level.
    @level  permission level
    */
    protected function setSidebar($level){
        //scripts
        ob_start();
        include("scripts-sidebar.js");    //scripts: open, close sidebar
        $this->insert_script .= ob_get_clean();
        
        //sidebar
        ob_start();
?>
        <div id="sidebar" class="w3-sidebar w3-bar-block w3-card w3-animate-left w3-theme-d1">
            <button id="sidebar_button_close" class="w3-bar-item w3-large w3-theme-d3 w3-hover-theme2 w3-button" onclick="w3_close()">
                <p class="w3-xlarge w3-right-align">Menu &#9776;</p>
            </button>
            <a href="index.php?page=intro" class="w3-bar-item w3-button w3-hover-theme2">O konferenci</a>
            <a href="index.php?page=login" class="w3-bar-item w3-button w3-hover-theme2">Login</a>
            <a href="index.php?page=topics" class="w3-bar-item w3-button w3-hover-theme2">Témata</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-theme2">Termíny</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-theme2">Organizátoři</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-theme2">Místo konání</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-theme2">Sponzoři</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-theme2">Pokyny pro autory</a>
            <hr>
        
<?php
        echo $this->fillSidebar($level);
?>
        </div>
<?php      
        $this->insert_body .= ob_get_clean();
        
        //sidebar button
        ob_start();
?>
            <div id="sidebar_button_open" class="w3-col w3-container w3-button w3-xlarge w3-theme-d3 w3-hover-theme2" onclick="w3_open()">
              <p>Menu &#9776;</p>
            </div>

<?php
        $this->insert_page_header .= ob_get_clean();
        Log::msg("set sidebar", __FILE__);
    }
    
    /*
    Sets page title
    */
    protected function setTitle($title){
        $this->title = $title;
        Log::msg("set title", __FILE__);
    }
    
    /*
    Appends allert insert from given attributes.
    @head       alert type
    @message    message
    @class      class definig color
    */
    protected function setAlert($head, $message, $class){
        $res = $this->createAlert($head, $message, $class);
        $this->alert .= $res;
        Log::msg("set alert", __FILE__);
    }
    
    /*
    Creates alert from given attributes.
    @head       alert type
    @message    message
    @class      class definig color
    @return     generates alert panel
    */
    protected function createAlert($head, $message, $class){
        $res = "";
        ob_start();
?>
            <div class="w3-panel alert <?php echo $class; ?> ">
                <h4><?php echo $head; ?></h4>
                <p><?php echo $message; ?></p>
            </div>
<?php
        $res .= ob_get_clean();
        return $res;
    }
    
    /*
    Generates alert for given key. If @key_f value exist, alert is created. req_data[@key_f] value is used as message
    @key    key for req_data value
    @return alert panel if @key_f value exists in req_data
    */
    protected function alertForFailed($key){
        $res = "";
        if($this->req_data !== null){
            if(array_key_exists($key."_f", $this->req_data)){
                $res = $this->createAlert("Upozornění!", $this->req_data[$key."_f"], "w3-amber");
            }    
        }
        return $res;
    }
    
    /*
    Sets page main content.
    */
    protected function setPageContent($page_content){
        $this->page_content = $page_content;
        Log::msg("set page_content", __FILE__);
    }
    
    /*
    Appends all parts together and creates whole page.
    @return whole page
    */
    protected function generatePage(){
        $this->createHead();
        $this->createBody();
        
        $result = "";
        $result .= $this->head;
        $result .= $this->body;
        
        Log::msg("page constructed", __FILE__);
        return $result;
    }
    
    /*
    Appends given scipts to script insert.
    */
    protected function setScript($script){
        $this->insert_script .= $script;
    }
    
    /*
    Strips @value of spaces and element tags.
    Used for safe output of text to page.
    */
    protected function clean($value){
        $res = htmlspecialchars($value);
        $res = trim($res);
        return $res;
    }
    
    /*
    Generates modal window with given id and content.
    Id of span element with exit button is @id_insert.
    @id         modal window id
    @content    content of modal window
    @return     generated modal window
    */
    protected function createModal($id, $content){
        $res = "";

        ob_start();
?>
           <div id="<?php echo $id ?>" class="w3-modal w3-card-4">
                <div class="w3-modal-content">
                  <div class="w3-card-4 w3-container w3-theme-d1">
                    <span onclick="modal_hide('<?php echo $id ?>')" class="w3-button w3-display-topright w3-theme-d3 w3-hover-theme2 w3-xlarge">&times;</span>
                      <div id="<?php echo $id."_insert"?>"></div>
<?php
        $res .= ob_get_clean();
        $res .= $content;
        ob_start();
?>
                      
                  </div>
                </div>
            </div>
<?php
        $res .= ob_get_clean();

        return $res;
    }
    
    /*
    Creates button for oppening modal window with given id.
    If filename is set it is used as second attribute in call to open modal window.
    @id     modal window id
    @name   button text
    @data   filename
    @return generates button
    */
    protected function createModalButton($id, $name, $data = null){
        $res = "";
        $res .= '<button onclick="modal_show(';
        $res .= "'$id'";
        if($data ==! null){
            $res .= ",'$data'";
        }
        $res .= ')" class="w3-btn w3-theme-d3 w3-hover-theme2">';
        $res .= $name;
        $res .= '</button>';
        return $res;
    }
}
?>
