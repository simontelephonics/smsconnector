<script>
$(function() {
	$("input[name=sipsmsenabled]").click(function(){
		if($(this).val() == "true") {
			$(".fpbx-sipsms").prop("disabled",false);
		} else {
			$(".fpbx-sipsms").prop("disabled",true);
		}
	});
});
</script>
<?php if(!empty($error)) {?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php } ?>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-4 control-label">
						<label for="sipsmsenabled"><?php echo _('SIP SMS enabled')?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="sipsmsenabled"></i>
					</div>
					<div class="col-md-8">
						<span class="radioset">
							<input type="radio" name="sipsmsenabled" class="form-control " id="sipsmsenabled0" value="true" <?php echo ($sipsmsenabled) ? 'checked' : ''?>><label for="sipsmsenabled0"><?php echo _('Yes')?></label>
							<input type="radio" name="sipsmsenabled" class="form-control " id="sipsmsenabled1" value="false" <?php echo (!$sipsmsenabled) ? 'checked' : ''?>><label for="sipsmsenabled1"><?php echo _('No')?></label>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="sipsmsenabled-help" class="help-block fpbx-help-block"><?php echo _('Enable this user to send and receive SMS on capable SIP devices, in addition to Sangoma softphones and UCP.')?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-4 control-label">
						<label for="sipsmsdefaultdid"><?php echo _('Default DID for sending SIP SMS')?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="sipsmsdefaultdid"></i>
					</div>
					<div class="col-md-8">
						<select name="sipsmsdefaultdid" class="form-control fpbx-sipsms" id="sipsmsdefaultdid" <?php echo !$sipsmsenabled ? 'disabled' : ''?>>
                            <?php foreach ($dids as $did)
                            { ?>
							<option value="<?php echo $did; ?>" <?php echo ($did == $sipsmsdefaultdid) ? 'selected' : ''?>><?php echo $did;?></option>
                            <?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="sipsmsdefaultdid-help" class="help-block fpbx-help-block"><?php echo _('This is the DID from which SMS will be sent by default on this user\'s SIP device.')?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-4 control-label">
						<label for="sipsmsemailoffline"><?php echo _('Send email notification for messages received while offline')?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="sipsmsemailoffline"></i>
					</div>
					<div class="col-md-8">
						<span class="radioset">
							<input type="radio" name="sipsmsemailoffline" class="form-control fpbx-sipsms" id="sipsmsemailoffline0" value="true" <?php echo ($sipsmsemailoffline) ? 'checked ' : ''; echo !$sipsmsenabled ? 'disabled' : ''?>><label for="sipsmsemailoffline0"><?php echo _('Yes')?></label>
							<input type="radio" name="sipsmsemailoffline" class="form-control fpbx-sipsms" id="sipsmsemailoffline1" value="false" <?php echo (!$sipsmsemailoffline) ? 'checked ' : ''; echo !$sipsmsenabled ? 'disabled' : ''?>><label for="sipsmsemailoffline1"><?php echo _('No')?></label>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="sipsmsemailoffline-help" class="help-block fpbx-help-block"><?php echo _('Send email notifications to the user if an SMS is received while the SIP device is unregistered or unable to receive it')?></span>
		</div>
	</div>
</div>
