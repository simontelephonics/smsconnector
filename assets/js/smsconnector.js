//This format's the action column
function linkFormat(value){
	var html = `<a href="?display=smsconnector&view=form&id=${value}"><i class="fa fa-edit"></i></a>&nbsp;`;
	html += `<a class="delAction" href="?display=smsconnector&action=delete&id=${value}"><i class="fa fa-trash"></i></a>`;
	return html;
}

$(document).ready(function() {
	$(".webhook-copy").click(function() {
		var input = $(this).siblings("input");
		input.select();
	  	document.execCommand("copy");
	  	window.getSelection().removeAllRanges();
		fpbxToast(_("URL copied to clipboard"), '', 'success' );
	});
});