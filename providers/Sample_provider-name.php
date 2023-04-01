<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Name extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('Name');
        $this->nameRaw  = 'name';

        $this->configInfo = array(
            'option1' => array(
                'type'        => 'string', 
                'label'       => _('Option'),
                'help'        => _("Help text for the option."),
                'default'     => 'default value',
                'class'       => '',
                'required'    => true, // True to set this property as required to make the provider available.
                'placeholder' => _('Text to display in the input when it is empty.'),
            ),
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        $config = $this->getConfig($this->nameRaw);

        /* code */
        dbug($config['option1']);
        
        return true;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $config = $this->getConfig($this->nameRaw);

        /* code */
        dbug($config['option1']);
        
        return true;
    }

    public function callPublic($connector)
    {
        $return_code = 202;
        /* code */
        return $return_code;
    }
}