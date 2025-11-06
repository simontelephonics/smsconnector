<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Internal extends providerBase
{
    public function __construct()
    {
     	parent::__construct();
        $this->name     = _('Internal');
        $this->nameRaw  = 'internal';
        $this->configInfo = array(); // No config fields needed
    }

    public function sendMedia($id, $to, $from, $message = null)
    {
     	// Not supporting MMS for internal
        return false;
    }

    public function sendMessage($id, $to, $from, $message = null)
    {
     	try {
            $connector = $this->FreePBX->Sms->loadAdaptor('Smsconnector');
            $msgid = $connector->getMessage($to, $from, '', $message, null, null, null);
            $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $message, null, 'Smsconnector', null);
            $this->setDelivered($id);
            return true;
        } catch (\Exception $e) {
            freepbx_log(FPBX_LOG_ERROR, sprintf(_("Internal provider failed to deliver ext-to-ext SMS: %s"), $e->getMessage()));
            return false;
        }
    }

    public function callPublic($connector)
    {
     	// Internal-only, not listening for public webhooks
        return 405;
    }
}


