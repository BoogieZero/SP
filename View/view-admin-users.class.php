<?php
include("view-template.class.php");

/*
View for displaying all users
*/
class View_admin_users extends Template{
    
    /*
    Instantiates view.
    @req_data   data from controller
    */
    public function __construct($req_data){
        $this->req_data = $req_data;
        $this->setSidebar($req_data['user_level']);
        
        $this->setTitle("Správa uživatelů");
        $this->setHeader("Správa uživatelů");
        
        if(isset($req_data['edit_success'])){
            if($req_data['edit_success'] === true ){
                $this->setAlert("Informace!", "Změny úspěšně uloženy.", "w3-green");    
            }else{
                $this->setAlert("Chyba!", "Změny se nepodařilo uložit.", "w3-red");    
            }   
        }
        
        $this->setPageContent($this->own_content());
        Log::msg("data_inserted", __FILE__);
    }

    /*
    Generates user row for users table.
    @user   array with user data
    @return generated row
    */
    private function createUser($user){
        $res = "";
        $res .= "<tr>";
        
        $res .= $this->wrapTd($user['name']);
        $res .= $this->wrapTd($user['login']);
        $res .= $this->wrapTd($user['email']);
        $res .= $this->wrapTd($user['level']);
        $res .= $this->wrapTd($this->getEditButton($user['idusers']));
        
        $res .= "</tr>";
        
        return $res;
    }
    
    /*
    Generates page content.
    Table with contributions.
    @return page content
    */
    private function own_content(){
        $res = "";
        $res .= '<div class="w3-card-4 w3-container w3-responsive">';
        $res .= '<table class="w3-table rev_table">';
        $res .= '<tr>
                    <th>Jméno</th>
                    <th>Login</th>
                    <th>Email</th>
                    <th>Práva</th>
                    <th>Editace</th>
                </tr>';
        
        foreach($this->req_data['users'] as $user){
            $res .= $this->createUser($user);
        }
        
        $res .= '</table>';
        $res .= '</div>';
        return $res;
    }
    
    /*
    Creates edit button for user given by id.
    @id     idusers
    @return generated button
    */
    private function getEditButton($id){
        $res = "";
        ob_start();
?>
            <a href="index.php?page=admin&amp;request=edit_user_req&amp;id=<?php echo $id?>" class="w3-btn w3-block w3-theme-d3 w3-hover-theme2">Editace</a>
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