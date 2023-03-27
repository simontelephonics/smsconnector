<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); } ?>

<div id="toolbar-all">
	<a href='?display=smsconnector&amp;view=form' class="btn btn-default"><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Add Number')?></a>
	<a href='?display=smsconnector&amp;view=settings' class="btn btn-default"><i class="fa fa-cog"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Provider Settings')?></a>
</div>
<table 
	id="hwgrid"
	data-url="ajax.php?module=smsconnector&command=getJSON&jdata=grid"
	data-toolbar="#toolbar-all"
	data-cache="false"
	data-toggle="table"
	data-show-columns="true"
	data-pagination="true"
	data-show-refresh="true"
	data-search="true"
	data-resizable="true"
	class="table table-striped">
	<thead>
		<tr>
			<th data-field="id" class="col-md-1"><?php echo _("Id")?></th>
			<th data-field="did" class="col-md-3"><?php echo _("DID")?></th>
			<th data-field="username" class="col-md-3"><?php echo _("User ID")?></th>
			<th data-field="name" class="col-md-4"><?php echo _("Provider")?></th>
			<th data-field="id" data-formatter="linkFormat" class="col-md-2"><?php echo _("Action")?></th>
		</tr>
	</thead>
</table>
