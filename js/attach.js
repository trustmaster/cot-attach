var attDisp = 0;
// Add attachment row
function addAttach() {
	var box = document.getElementById("att_box");
	var file = document.getElementById("att_file" + attDisp);
	if(box.style.display == "none") box.style.display = "";
	if(file != null) {
		file.style.display = "";
		attDisp++;
	}
}
