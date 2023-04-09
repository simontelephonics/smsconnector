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
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <label class="control-label" for="didNumber"><?php echo _("DID") ?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="didNumber"></i>
                            </div>
                            <div class="col-md-8 col-lg-8 col-sm-12">
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
                <!--USER-->
                <div class="element-container" style="margin-top: 1rem;">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <label for="uidNumber" class="control-label"><?php echo _("User") ?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="uidNumber"></i>
                            </div>
                            <div class="col-md-8 col-lg-8 col-sm-12">
                                <select name="uidNumber" id="uidNumber" class="form-control" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="uidNumber-help" class="help-block fpbx-help-block"><?php echo _("Select the user associated with the DID") ?></span>
                        </div>
                    </div>
                </div>
                <!--PROVIDER-->
                <div class="element-container" style="margin-top: 1rem;">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <label class="control-label" for="providerNumber"><?php echo _("Provider") ?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="providerNumber"></i>
                            </div>
                            <div class="col-md-8 col-lg-8 col-sm-12">
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
