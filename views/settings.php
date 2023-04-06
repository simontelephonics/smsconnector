<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

	echo show_help('<p>'._('Set your public hostname in Advanced Settings -> FreePBX Web Address. (AMPWEBADDRESS)').' <a href="config.php?display=advancedsettings#AMPWEBADDRESS" target="_blank"><i class="fa fa-cog fa-spin fa-lg" aria-hidden="true"></i></a></p><p>'._('The webhook for inbound SMS will be in the form: <code>https://AMPWEBADDRESS/smsconn/provider.php?provider=nameprovider</code> where <i>nameprovider</i> is one of <code>telnyx</code>, <code>flowroute</code>, <code>twilio</code>, <code>etc...</code>.').'</p><p>'._('Note that the server must have a valid TLS certificate generated in Certificate Manager and set for the web server to use.').'</p>', _('Webhook Settings'), false, true, "info");

	$template_sec ='
		<div class="section-title" data-for="%%__NAMERAW__%%">
			<h2><i class="fa fa-minus"></i> %%__NAME__%%</h2>
		</div>
		<div class="section" data-id="%%__NAMERAW__%%">
			%%__OPTIONS_LINES__%%
		</div>
		<br/>
	';

	$template_opt = '
		<div class="element-container">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="form-group">
							<div class="col-md-3">
								<label class="control-label" for="%%__PREFIXLINE__%%">%%__LABEL__%%</label>
								<i class="fa fa-question-circle fpbx-help-icon" data-for="%%__PREFIXLINE__%%"></i>
							</div>
							<div class="col-md-9">
								<input type="text" class="form-control %%__CLASS__%%" %%__OTHER_TAGS__%% id="%%__PREFIXLINE__%%" name="providers[%%__NAMERAW__%%][%%__KEY__%%]" value="%%__VAL__%%">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<span id="%%__PREFIXLINE__%%-help" class="help-block fpbx-help-block">%%__HELP__%%</span>
				</div>
			</div>
		</div>
	';

	$template_webhook = '
		<div class="element-container">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="form-group">
							<div class="col-md-3">
								<label class="control-label">'._("Webhook Provider").'</label>
								<i class="fa fa-question-circle fpbx-help-icon" data-for="%%__PREFIXLINE__%%"></i>
							</div>
							<div class="col-md-9">
								<div class="input-group">
									<input type="text" class="form-control" aria-describedby="%%__ID_WEBHOOK__%%" value="%%__WEBHOOK__%%" readonly>
									<span class="input-group-addon webhook-copy" id="%%__ID_WEBHOOK__%%">
										<i class="fa fa-files-o" aria-hidden="true"></i>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<span id="%%__PREFIXLINE__%%-help" class="help-block fpbx-help-block">%%__HELP__%%</span>
				</div>
			</div>
		</div>
	';

?>

<form action="" method="post" class="fpbx-submit" id="smsconnectorprovidersform" name="smsconnectorprovidersform">
<input type="hidden" name='action' value="setproviders">

<?php
	foreach ($settings as $key => $value)
	{
		$html_lines = "";

		$info 	 	= $value['info'];
		$data 	 	= $value['value'];
		$name  	 	= $info['name'];
		$nameraw 	= $info['nameraw'];
		$webhook	= $info['webhook'];

		foreach ($data as $key_data => $value_data)
		{
			$tags 		 = '';
			$info_line   = $info['configs'][$key_data];
			$prefixline  = $nameraw.'-'.$key_data;
			$class_input = empty($info_line['class']) 		? '' : $info_line['class'];
			$tags 		.= empty($info_line['placeholder']) ? '' : sprintf(' placeholder="%s" ', $info_line['placeholder']);

			$replace = array(
				"%%__PREFIXLINE__%%" => $prefixline,
				"%%__LABEL__%%" 	 => $info_line['label'],
				"%%__CLASS__%%" 	 => $class_input,
				"%%__OTHER_TAGS__%%" => $tags,
				"%%__KEY__%%" 		 => $key_data,
				"%%__VAL__%%" 		 => $value_data,
				"%%__HELP__%%" 		 => $info_line['help'],
			);
			$html_lines .= str_replace(array_keys($replace), array_values($replace), $template_opt);
		}
		if (! empty($webhook))
		{
			$api_info = $info['class']->getAPIInfo();
			$help = sprintf('<p>%s <a href="%s" target="_blank">%s</a>%s</p>', _('More information about the API'), $api_info['URL'], _('Click Here') , empty($api_info['VERSION']) ? '' : sprintf(_(' - Supported Version %s'), $api_info['VERSION']));
			$replace  = array(
				"%%__PREFIXLINE__%%" => $nameraw.'-webhook',
				"%%__ID_WEBHOOK__%%" => $nameraw . "_webhook",
				"%%__WEBHOOK__%%" 	 => $webhook,
				"%%__HELP__%%" 		 => $help,
			);
			$html_lines .= str_replace(array_keys($replace), array_values($replace), $template_webhook);
		}

		// NOTE!! The order of the array is important because if %%__OPTIONS_LINES__%% is not defined
		// before %%__NAMERAW__%% the lines using the %%__NAMERAW__%% tag will not be replaced.
		$replace = array(
			"%%__OPTIONS_LINES__%%" => $html_lines,
			"%%__NAME__%%" 			=> $name,
			"%%__NAMERAW__%%" 	 	=> $nameraw,
		);
		echo str_replace(array_keys($replace), array_values($replace), $template_sec);
	}
?>
</form>
