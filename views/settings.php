<form action="" method="post" class="fpbx-submit" id="hwform" name="hwform">
<input type="hidden" name='action' value="setproviders">
<!--Telnyx-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<h3>Telnyx</h3>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="tapikey"><?php echo _("API Key") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="tapikey"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="tapikey" name="providers[telnyx][0][api_key]" value="<?php if (isset($providers['telnyx'])) echo $providers['telnyx'][0]['api_key']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="subject-help" class="help-block fpbx-help-block"><?php echo _("Enter the Telnyx v2 API key")?></span>
		</div>
	</div>
</div>
<!--END Telnyx-->
<!--Flowroute-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<h3>Flowroute</h3>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="fapikey"><?php echo _("API Key") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="fapikey"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="fapikey" name="providers[flowroute][0][api_key]" value="<?php if (isset($providers['flowroute'])) echo $providers['flowroute'][0]['api_key']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="subject-help" class="help-block fpbx-help-block"><?php echo _("Enter the Flowroute API key")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="fapisecret"><?php echo _("API Secret") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="fapisecret"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="fapisecret" name="providers[flowroute][0][api_secret]" value="<?php if (isset($providers['flowroute'])) echo $providers['flowroute'][0]['api_secret']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="subject-help" class="help-block fpbx-help-block"><?php echo _("Enter the Flowroute API secret")?></span>
		</div>
	</div>
</div>
<!--END Flowroute -->
</form>
