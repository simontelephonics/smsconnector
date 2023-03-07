<form action="" method="post" class="fpbx-submit" id="numberform" name="numberform" data-fpbx-delete="config.php?display=smsconnector&action=delete&id=<?php echo $id?>">
<input type="hidden" name='action' value="<?php echo $id?'edit':'add' ?>">
<input type="hidden" id="id" name="id" value="<?php echo $id?>">
<!--DID-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="did"><?php echo _("DID") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="subject"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="did" name="did" value="<?php echo $did?>" <?php echo $did?'readonly':'' ?>>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="subject-help" class="help-block fpbx-help-block"><?php echo _("Enter the DID")?></span>
		</div>
	</div>
</div>
<!--END DID-->
<!--User-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="uid"><?php echo _("User") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="uid"></i>
					</div>
					<div class="col-md-9">
						<?php  ?>
						<select class="form-control" id="uid" name="uid">
							<?php if ($id) {?><option value="<?php echo $uid; ?>" selected><?php echo "$displayname ($username)"; ?></option><?php }?>
							<?php 
								$users = \FreePBX::Userman()->getAllUsers();
								$existingusers = \FreePBX::Smsconnector()->getUsersWithDids();
								foreach($users as $user) { 
									if (!in_array($user['id'], $existingusers)) {
										echo '<option value="' . $user['id'] . '">';
										echo $user['displayname'] . ' (' . $user['username'] . ')</option>';
									}
								} ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="body-help" class="help-block fpbx-help-block"><?php echo _("Select the user associated with the DID")?></span>
		</div>
	</div>
</div>
<!--END User-->
<!--Provider-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="name"><?php echo _("Provider Name") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="name"></i>
					</div>
					<div class="col-md-9">
						<select class="form-control" id="name" name="name">
							<option value="telnyx" <?php echo ($name == 'telnyx')?'selected':'' ?>>Telnyx</option>
							<option value="flowroute" <?php echo ($name == 'flowroute')?'selected':'' ?>>Flowroute</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="body-help" class="help-block fpbx-help-block"><?php echo _("Select the SMS provider")?></span>
		</div>
	</div>
</div>
<!--END Provider-->
</form>