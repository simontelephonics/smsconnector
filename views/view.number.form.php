<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); } ?>

<!--Add number Modal -->
<div class="modal fade" id="numberForm" tabindex="-1" role="dialog" aria-labelledby="numberForm" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo _("Loading...") ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idNumber" value="" />
                <!--DID-->
                <div class="element-container" style="margin-top: 1rem;">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label class="control-label" for="didNumber"><?php echo _("DID") ?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="didNumber"></i>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <input type="text" name="didNumber" class="form-control " id="didNumber" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="didNumber-help" class="help-block fpbx-help-block"><?php echo _("Enter the DID, including the country code. North America numbers must start with 1!") ?></span>
                        </div>
                    </div>
                </div>
                 <!-- Block Users -->
                <div class="element-container" style="margin-top: 1rem;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-3 col-lg-3 col-sm-12">
                                        <label class="control-label" for="uidsNumber"><?php echo _('Users')?></label>
                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="uidsNumber"></i>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="uidsNumber-help" class="help-block fpbx-help-block">
                                                    <?php echo _('Select the users associated with the DID')?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-lg-9 col-sm-12">
                                        <input type="hidden" class="form-control" name="uidsNumber" id="uidsNumber" value="" readonly>
                                        <div class="BoxUsersList">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="alert alert-info" role="alert">
                                                        <?php echo _("Available"); ?>
                                                    </div>
                                                    <ul class="UserList list-group" id="available_users"></ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="alert alert-info" role="alert">
                                                        <?php echo _("Selected"); ?>
                                                    </div>
                                                    <ul class="UserList list-group" id="selected_users"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Block Users -->
                <!--PROVIDER-->
                <div class="element-container" style="margin-top: 1rem;">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label class="control-label" for="providerNumber"><?php echo _("Provider") ?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="providerNumber"></i>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <select name="providerNumber" id="providerNumber" class="form-control" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="providerNumber-help" class="help-block fpbx-help-block"><?php echo _("Select the SMS provider. Only providers configured with credentials in the Provider Settings screen will be listed.") ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close") ?></button>
                <button type="button" class="btn btn-success" id="submitForm"><?php echo _("Save Changes") ?></button>
            </div>
        </div>
    </div>
</div>
