/*global document*/
//Update value on filepicker change
function chooseFile(id) {
    var label = id.concat("_lb");
    var fileName = document.getElementById(id).files.item(0).name;
    document.getElementById(label).innerHTML = fileName;
}