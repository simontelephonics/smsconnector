<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

	echo show_help('<p>'._('Set your public hostname in Advanced Settings -> FreePBX Web Address. (AMPWEBADDRESS)').'</p><p>'._('The webhook for inbound SMS will be in the form: <code>https://AMPWEBADDRESS/smsconn/provider.php?provider=nameprovider</code> where <i>nameprovider</i> is one of <code>telnyx</code>, <code>flowroute</code>, <code>twilio</code>, <code>etc...</code>.').'</p><p>'._('Note that the server must have a valid TLS certificate generated in Certificate Manager and set for the web server to use.').'</p>', _('Webhook Settings'), false, true, "info");
?>
<form action="" method="post" class="fpbx-submit" id="smsconnectorprovidersform" name="smsconnectorprovidersform">
<input type="hidden" name='action' value="setproviders">

<?php
	foreach ($settings as $key => $value)
	{
		$info = $value['info'];
		$data = $value['value'];

		$name = $info['name'];
		$nameraw = $info['nameraw'];
		?>


<div class="section-title" data-for="<?php echo  $nameraw?>">
    <h2><i class="fa fa-minus"></i> <?php echo  $name; ?></h2>
</div>
<div class="section" data-id="<?php echo $nameraw?>">

		<?php
		foreach ($data as $key_data => $value_data)
		{
			$info_line = $info['configs'][$key_data];
			$prefixline = $nameraw.'-'.$key_data;
			$class_input = empty($info_line['class']) ? '' : $info_line['class'];
			?>

	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="<?php echo $prefixline; ?>"><?php echo $info_line['label']; ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $prefixline; ?>"></i>
						</div>
						<div class="col-md-9">
							<input type="text" class="form-control <?php echo $class_input; ?>" id="<?php echo $prefixline; ?>" name="providers[<?php echo $nameraw; ?>][<?php echo $key_data; ?>]" value="<?php echo $value_data; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="<?php echo $prefixline; ?>-help" class="help-block fpbx-help-block"><?php echo $info_line['help']; ?></span>
			</div>
		</div>
	</div>

			<?php
		}
		?>
</div>
<br/>
		<?php
	}
?>
</form>
