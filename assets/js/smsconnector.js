//This format's the action column
function linkFormat(value, row, idx){
	var html = '<a href="#" data-toggle="modal" data-target="#numberForm" data-id="' + row['didid'] + '" data-did="' + row['did'] + '" data-uid="' + row['uid'] + '" data-provider="' + row['name'] + '" ><i class="fa fa-pencil"></i></a>';
	html += '&nbsp;';
	html += '<a href="#" data-id="' + row['didid'] + '" data-did="' + row['did'] + '" id="del" data-idx="' + idx + '" ><i class="fa fa-trash"></i></a>';
	return html;
}

function userFormat(value, row, idx)
{
	var html = '';
	$.each(value, function(uid, user_info)
	{
		html += '<span class="label label-default label-username">'
		html += (user_info['displayname'] == "") ? user_info['username'] : sprintf("%s (%s)", user_info['username'], user_info['displayname']);
		html += '</span>';
	});
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


function updateInputSelects()
{
	var status_return = true;

	$('#uidsNumber').empty();
	$('#available_users').empty();
	$('#selected_users').empty();

	$('#providerNumber').empty();

	$.ajax({
		type: "POST",
		url: window.FreePBX.ajaxurl,
		data: {
			module  : 'smsconnector',
			command : 'get_selects',
		},
		async: false,
		success: function(response)
		{
			if (response.status)
			{
				$.each(response.data.users, function(user_id, user_display) {
					$("#available_users").append(sprintf('<li class="list-group-item" data-uid="%s">%s</li>',user_id, user_display));
				});
				$.each(response.data.providers, function(provider_id, provider_display) {
					$('#providerNumber').append($('<option>', {
						value: provider_id,
						text: provider_display
					}));
				});
			}
			else
			{
				fpbxToast(response.message, '', 'error');
				status_return = false;
			}
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
			status_return = false;
		}
	});
	return status_return;
}

$('#numberForm').on('shown.bs.modal', function () {
	$("#available_users").scrollTop(0);
	$("#selected_users").scrollTop(0);
});

$('#numberForm').on('show.bs.modal', function (e) {
	var id = $(e.relatedTarget).data('id');
	var showModal = true;
	
	$('.element-container').removeClass('has-error');

	$(".input-warn").remove();

	var number	 = "";
	var provider = "";
	var users	 = "";
	var uids 	 = "";

	if (id == null || id == undefined || id == "")
	{
		var title 	 = _("New Number");
		var btn_send = _("Create New");

		var didNumber_readonly = false;
	}
	else
	{
		var title 	 = sprintf(_("Edit Number (%s)"), id);
		var btn_send = _("Save Changes");

		var didNumber_readonly = true;

		$.ajax({
			type: "POST",
			url: window.FreePBX.ajaxurl,
			data: {
				module	: 'smsconnector',
				command	: 'numbers_get',
				id		: id,
			},
			async: false,
			success: function(response)
			{
				if (response.status)
				{
					number 	 = response.data.did;
					provider = response.data.name;
					users 	 = response.data.users;
					uids 	 = Object.keys(users);
				}
				else
				{
					fpbxToast(response.message, '', 'error');
					showModal = false;
				}
			},
			error: function(xhr, status, error)
			{
				fpbxToast(sprintf(_('Error: %s'), error), '', 'error');
				showModal = false;
			}
		});
	}

	if (showModal && updateInputSelects())
	{
		$this = this;

		if (uids !== "")
		{
			$('#available_users > li').each(function()
			{
				if (uids.includes($(this).data('uid').toString())) {
					$(this).appendTo('#selected_users');
				}
			});
			$("#uidsNumber").val(uids);
		}

		$("#submitForm").text(btn_send);
		$("#submitForm").prop("disabled", false);

		$(this).find('.modal-title').text(title);
	
		$("#idNumber").val(id);

		$('#didNumber').prop('readonly', didNumber_readonly);
		$("#didNumber").val(number);

		$("#providerNumber").val(provider);
	}

	if (!showModal)
	{
		e.preventDefault();
	}
});

//add / update Number
$('#submitForm').on('click', function () {
	$this = this;

 	var id 		 = $("#idNumber").val();
	var did 	 = $("#didNumber").val();
	var uids 	 = $("#uidsNumber").val();
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
	if (uids === '' || uids === null || uids === undefined)
	{
		warnInvalid($('#uidsNumber'), _('User list cannot be blank'));
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
			uidsNumber: uids,
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
	var id  = $(this).data('id');
	var did = $(this).data('did');

	if (id === "" || id === undefined || id === null)
	{
		fpbxToast(_("ID not detected!"), '', 'error');
		return;
	}

	fpbxConfirm(
		sprintf(_("Are you sure you want to delete the DID (%s)?"), did),
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

$(document).ready(function()
{
	$(".UserList").on('click', 'li', function (e) {
		if (e.ctrlKey || e.metaKey) {
			$(this).toggleClass("selected");
		} else {
			$(this).addClass("selected").siblings().removeClass('selected');
		}
	}).sortable({
		connectWith: ".UserList",
		delay: 150, //Needed to prevent accidental drag when trying to select
		revert: 0,
		helper: function (e, item) {
			//Basically, if you grab an unhighlighted item to drag, it will deselect (unhighlight) everything else
			if (!item.hasClass('selected')) {
				item.addClass('selected').siblings().removeClass('selected');
			}
			
			//////////////////////////////////////////////////////////////////////
			//HERE'S HOW TO PASS THE SELECTED ITEMS TO THE `stop()` FUNCTION:
			
			//Clone the selected items into an array
			var elements = item.parent().children('.selected').clone();
			
			//Add a property to `item` called 'multidrag` that contains the 
			//  selected items, then remove the selected items from the source list
			item.data('multidrag', elements).siblings('.selected').remove();
					
			//Now the selected items exist in memory, attached to the `item`,
			//  so we can access them later when we get to the `stop()` callback
			
			//Create the helper
			var helper = $('<li/>');
			return helper.append(elements);
		},
		stop: function (e, ui) {
			//Now we access those items that we stored in `item`s data!
			var elements = ui.item.data('multidrag');
			
			//`elements` now contains the originally selected items from the source list (the dragged items)!!
			
			//Finally we insert the selected items after the `item`, then remove the `item`, since 
			//  item is a duplicate of one of the selected items.
			ui.item.after(elements).remove();
			elements.removeClass('selected');
			updateUsers();
		}
	
	});

});

function updateUsers(){
    var optionTexts = [];
	$("#selected_users li").each(function() {
		optionTexts.push($(this).data("uid"))
	});
    $('#uidsNumber').val(optionTexts);
}