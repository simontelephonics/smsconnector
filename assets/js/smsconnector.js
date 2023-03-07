//This format's the action column
function linkFormat(value){
	var html = `<a href="?display=smsconnector&view=form&id=${value}"><i class="fa fa-edit"></i></a>&nbsp;`;
	html += `<a class="delAction" href="?display=smsconnector&action=delete&id=${value}"><i class="fa fa-trash"></i></a>`;
	return html;
}
