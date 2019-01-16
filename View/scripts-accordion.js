/*global document*/
//Open accordion + highlight button
function accordionSwitch(id_body, id_highlight) {
    var b = document.getElementById(id_body);
    var h = document.getElementById(id_highlight);
    if (b.className.indexOf("w3-hide") !== -1) {
        b.className = b.className.replace(" w3-hide", "");
        h.className += " w3-theme2-d2";
    } else {
        b.className += " w3-hide";
        h.className = h.className.replace(" w3-theme2-d2", "");
    }
}
