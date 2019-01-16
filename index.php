<?php
/**
Main entry point.
Gets content from corresponding page to $page= value.
*/
    session_start();

 include_once($_SERVER['DOCUMENT_ROOT']."/SP/log.class.php");
    
    Log::clear_msg();
    Log::msg("entry", __FILE__);
    
    include_once("Controller/con-pages-list.php");
    

    if(isset($_GET["page"])){
//        page is set
        $input = $_GET["page"];
        Log::msg("attr page=".$input, __FILE__);
    }else{
//        page is not set
        $input = null;
        Log::msg("attr page=".$input, __FILE__);
    }
    
    //specific input?
    //get index of desired page
    if(isset($input)){
        $i = array_search($input, $pages);
        if($i === false){     
            //wrong input
            Log::msg("Invalid input - page not available: ".$_GET['page'], __FILE__);
            echo "<html><head><meta charset='utf-8'></head><body>stránka není dostupná</body></html>";
        }
    }else{
        //defalut page
        Log::msg("select default page", __FILE__);
        $i = 0;
        
    }
    
    Log::msg("generate page: ".$pages[$i], __FILE__);
    $page = $pages_con[$i];   
    include($page);
    $con = new $pages_obj[$i];
    $result = $con->work();
    echo $result;
?>
