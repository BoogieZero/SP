/*global document*/
// Open side bar + offset page
function w3_open() {
    document.getElementById("sidebar").style.display = "block";
    document.getElementById("main").style.marginLeft = document.getElementById("sidebar").offsetWidth + "px";
    document.getElementById("sidebar_button_open").style.display = "none";
}
// Close side bar - offset page
function w3_close() {
    document.getElementById("sidebar").style.display = 'none';
    document.getElementById("main").style.marginLeft = "0%";
    document.getElementById("sidebar_button_open").style.display = "block";
}
