<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); } ?>

<div id="toolbar-all">
	<a href="#" class="btn btn-default" data-toggle="modal" data-target="#numberForm"><i class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo _("Add Number") ?></a>
	<a href='?display=smsconnector&amp;view=settings' class="btn btn-default"><i class="fa fa-cog"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Provider Settings')?></a>
</div>
<table 
	id="hwgrid"
	data-url="ajax.php?module=smsconnector&command=numbers_list"
	data-toolbar="#toolbar-all"
	data-cache="false"
	data-toggle="table"
	data-show-columns="true"
	data-pagination="true"
	data-show-refresh="true"
	data-search="true"
	data-resizable="true"
	data-sortable="true"
	class="table table-striped">
	<thead>
		<tr>
			<th data-field="did" data-sortable="true" class="col-md-2"><?php echo _("DID")?></th>
			<th data-field="users" data-sortable="true" data-formatter="userFormat" data-class="col-users" class="col-md-7"><?php echo _("Users")?></th>
			<th data-field="name" data-sortable="true" class="col-md-2"><?php echo _("Provider")?></th>
			<th data-field="id" data-formatter="linkFormat" class="col-md-1 text-center"><?php echo _("Action")?></th>
		</tr>
	</thead>
</table>