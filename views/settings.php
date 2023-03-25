<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
	
	$providers = $settings['providers'];
?>



<form action="" method="post" class="fpbx-submit" id="smsconnectorprovidersform" name="smsconnectorprovidersform">
<input type="hidden" name='action' value="setproviders">
<!--Telnyx-->
<div class="row">
	<div class="col-md-12">
		<h3><?php echo _('Telnyx'); ?></h3>
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
						<input type="text" class="form-control" id="tapikey" name="providers[telnyx][api_key]" value="<?php if (isset($providers['telnyx'])) echo $providers['telnyx'][0]['api_key']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="tapikey-help" class="help-block fpbx-help-block"><?php echo _("Enter the Telnyx v2 API key")?></span>
		</div>
	</div>
</div>
<!--END Telnyx-->
<!--Flowroute-->
<div class="row">
	<div class="col-md-12">
		<h3><?php echo _('Flowroute'); ?></h3>
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
						<input type="text" class="form-control" id="fapikey" name="providers[flowroute][api_key]" value="<?php if (isset($providers['flowroute'])) echo $providers['flowroute'][0]['api_key']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="fapikey-help" class="help-block fpbx-help-block"><?php echo _("Enter the Flowroute API key")?></span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="fapisecret"><?php echo _("API Secret") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="fapisecret"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="fapisecret" name="providers[flowroute][api_secret]" value="<?php if (isset($providers['flowroute'])) echo $providers['flowroute'][0]['api_secret']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="fapisecret-help" class="help-block fpbx-help-block"><?php echo _("Enter the Flowroute API secret")?></span>
		</div>
	</div>
</div>
<!--END Flowroute -->
<!--Twilio-->
<div class="row">
	<div class="col-md-12">
		<h3><?php echo _('Twilio'); ?></h3>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="twapikey"><?php echo _("Account SID") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="twapikey"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="twapikey" name="providers[twilio][api_key]" value="<?php if (isset($providers['twilio'])) echo $providers['twilio'][0]['api_key']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="twapikey-help" class="help-block fpbx-help-block"><?php echo _("Enter the Twilio account SID")?></span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="twapisecret"><?php echo _("Auth Token") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="twapisecret"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="twapisecret" name="providers[twilio][api_secret]" value="<?php if (isset($providers['twilio'])) echo $providers['twilio'][0]['api_secret']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="twapisecret-help" class="help-block fpbx-help-block"><?php echo _("Enter the Twilio Auth Token")?></span>
		</div>
	</div>
</div>
<!--END Flowroute -->
</form>

<h3><?php echo _('Webhook settings'); ?></h3>
<p><?php echo _('Set your public hostname in Advanced Settings -> FreePBX Web Address. (AMPWEBADDRESS)'); ?></p>
<p><?php echo _('The webhook for inbound SMS will be in the form: <code>https://AMPWEBADDRESS/smsconn/provider.php</code> where <i>provider</i> is either <code>telnyx</code> or <code>flowroute</code>.')?></p>
<p><?php echo _('Note that the server must have a valid TLS certificate generated in Certificate Manager and set for the web server to use.'); ?></p>