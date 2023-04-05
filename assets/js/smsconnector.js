//This format's the action column
function linkFormat(value, row, idx){
	var html = '<a href="#" data-toggle="modal" data-target="#numberForm" data-id="' + row['id'] + '" data-did="' + row['did'] + '" data-uid="' + row['uid'] + '" data-provider="' + row['name'] + '" ><i class="fa fa-pencil"></i></a>';
	html += '&nbsp;';
	html += '<a href="#" data-id="' + row['id'] + '" id="del" data-idx="' + idx + '" ><i class="fa fa-trash"></i></a>';
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


//initailise data when modal opens
$('#numberForm').on('show.bs.modal', function (e) {
	var id = $(e.relatedTarget).data('id');

	$('.element-container').removeClass('has-error');

	$(".input-warn").remove();

	if (id == null || id == undefined || id == "")
	{
		var title 	 = _("New Number");
		var btn_send = _("Create New");

		var didNumber_readonly = false;

		var number	 = "";
		var user	 = "";
		var provider = "";
	}
	else
	{
		var title 	 = sprintf(_("Edit Number (%s)"), id);
		var btn_send = _("Save Changes");

		var didNumber_readonly = true;
		
		// TODO: Implement get data via AJAX, now get data from buttom edit.
		var number	 = $(e.relatedTarget).data('did');
		var user	 = $(e.relatedTarget).data('uid');
		var provider = $(e.relatedTarget).data('provider');
	}

	$this = this;

	$("#submitForm").text(btn_send);
	$("#submitForm").prop("disabled", false);

	$(this).find('.modal-title').text(title);
	
	$("#idNumber").val(id);

	$('#didNumber').prop('readonly', didNumber_readonly);
	$("#didNumber").val(number);

	// TODO: Implement get data list via ajax, now generate list in process generate page
	$("#uidNumber").val(user);
	$("#providerNumber").val(provider);

});

//add / update Number
$('#submitForm').on('click', function () {
	$this = this;

 	var id 		 = $("#idNumber").val();
	var did 	 = $("#didNumber").val();
	var uid 	 = $("#uidNumber").val();
	var provider = $("#providerNumber").val();

	if (id === '' || id === null || id === undefined)
	{
		var typeUpdate = "new";
	}
	else
	{
		var typeUpdate = "edit";
	}

	if (did === '' || did === null || did === undefined)
	{
		warnInvalid($('#didNumber'), _('DID cannot be blank'));
		return;
	}
	if (uid === '' || uid === null || uid === undefined)
	{
		warnInvalid($('#uidNumber'), _('User cannot be blank'));
		return;
	}
	if (provider === '' || provider === null || provider === undefined)
	{
		warnInvalid($('#providerNumber'), _('Provider cannot be blank'));
		return;
	}

 	$(this).prop("disabled", true);
	$($this).text( typeUpdate == "edit" ? _("Updating..."): _("Adding..."));

	var post_data = {
		module: 'smsconnector',
		command: 'numbers_update',
		data: {
			type: typeUpdate,
			id: id,
			didNumber: did,
			uidNumber: uid,
			providerNumber: provider,
		}
	};

	$.post(window.FreePBX.ajaxurl, post_data)
  	.done(function(data)
	{
 		if (data.status)
		{
			fpbxToast(data.message, '', 'success');
 			$('#hwgrid').bootstrapTable('refresh', {});
 			$("#numberForm").modal('hide');
 		}
		else
		{
			fpbxToast(data.message, '', 'error');
 		}
  	})
  	.fail(function(jqXHR, textStatus, errorThrown)
	{
		fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
  	})
	.always(function()
	{
		$($this).text( typeUpdate == "edit" ? _("Save Changes"): _("Create New"));
		$($this).prop("disabled", false);
	});

});

//Delete Number
$(document).on('click', '[id="del"]', function () {
	var id = $(this).data('id');

	if (id === "" || id === undefined || id === null)
	{
		fpbxToast(_("ID not detected!"), '', 'error');
	}

	fpbxConfirm(
		_("Are you sure you want to delete the Number?"),
		_("Yes"), _("No"),
		function () {
			var post_data = {
				module: 'smsconnector',
				command: 'numbers_delete',
				id: id,
			};
			$.post(window.FreePBX.ajaxurl, post_data)
			.done(function (data)
			{
				if (data.status == true)
				{
					$('#hwgrid').bootstrapTable('refresh', { silent: true });
				}
				fpbxToast(data.message, '', data.status == true ? 'success' : 'error');
			})
			.fail(function(jqXHR, textStatus, errorThrown)
			{
				fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
			});
		}
	);
});