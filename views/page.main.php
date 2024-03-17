<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
	
	switch(empty($request['view'])?'':$request['view'])
	{
		case 'settings':
			$subhead = _('Provider Settings');
			$content = $smsconnector->showPage('settings');
			break;

		case 'main':
		case 'grid':
		default:
			$subhead = _('List Numbers');
			$content = $smsconnector->showPage('grid');
	}
?>

<div class="container-fluid">
	<h1><?php echo _("SMS Connector")?></h1>
	<h2><?php echo $subhead?></h2>
	<div class="display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
