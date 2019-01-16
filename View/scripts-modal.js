/*global document*/
//open modal with file data
function modal_show(id, file) {
    'use strict';
    
    if (file !== undefined) {
        var filePath = "Files/";
        var obj = document.createElement("OBJECT");
        obj.data = filePath.concat(file);
        document.getElementById(id.concat("_insert")).appendChild(obj);
    }
    
    document.getElementById(id).style.display = "block";
}

//Open assign review modal window
function modal_show_assign(id, idcontr) {
    'use strict';
    
    document.getElementById("input_assign_id").value = idcontr;
    document.getElementById(id).style.display = "block";
}

//Close moda window
function modal_hide(id) {
    'use strict';
    document.getElementById(id).style.display = "none";
}