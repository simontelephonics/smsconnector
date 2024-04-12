<?php
namespace FreePBX\modules\Sms\Adaptor;
use PDO;
class Smsconnector extends \FreePBX\modules\Sms\AdaptorBase {
    // Properties to be set by parent constructor - fix for PHP8
    public $db;
    public $FreePBX;
    public $emoji;

    /**
     * Extend AdaptorBase send/receive methods with our specific cases
     */

    public function sendMedia($to,$from,$cnam,$message=null,$files=array(),$time=null,$adaptor=null,$emid=null,$chatId='')
    {
        // Store in database
        $retval = array();
        try 
        {
            $retval['id'] = parent::sendMedia($to, $from, $cnam, $message, $files, $time, 'Smsconnector', $emid, $chatId);
            $retval['status'] = true;
        } 
        catch (\Exception $e) 
        {
            throw new \Exception('Unable to store media: '.$e->getmessage());
        }
        
        // Look up provider info containing name and api credentials
        $provider     = $this->lookUpProvider($from);
        $providerInfo = $this->FreePBX->Smsconnector->getProvider($provider);
        if (!empty($providerInfo))
        {
            if (! empty($providerInfo['class']))
            {
                $providerInfo['class']->sendMedia($retval['id'], $to, $from, $message);
            }
        }
        $retval['emid'] = $emid; // TODO: set this from API call result
        return $retval;
    }

    public function sendMessage($to,$from,$cnam,$message,$time=null,$adaptor=null,$emid=null,$chatId='')
    {
        // Clean up 'to' number if we got one with a +. Only seems to apply to mobile app. UCP
        // and desktop apps format number before invoking this module.
        $to = ltrim($to, '+');

        if (preg_match('/^[2-9][0-9]{2}[2-9][0-9]{6}$/', $to)) // rewrite NANP for mobile app
        {
            $to = sprintf('1%s', $to);
        }

        // Store in database
        $retval = array();
        try 
        {
            $retval['id'] = parent::sendMessage($to, $from, $cnam, $message, $time, 'Smsconnector', $emid, $chatId);
            $retval['status'] = true;
        } 
        catch (\Exception $e) 
        {
            throw new \Exception('Unable to store message: '.$e->getMessage());
        }

        // look up provider info containing name and api credentials
        $provider = $this->lookUpProvider($from);

        $providerInfo = $this->FreePBX->Smsconnector->getProvider($provider);
        if (!empty($providerInfo))
        {
            if (! empty($providerInfo['class']))
            {
                $providerInfo['class']->sendMessage($retval['id'], $to, $from, $message);
            }
        }
        $retval['emid'] = $emid; // TODO: set this from API call result
        return $retval;
    }

    public function getMessage($to,$from,$cnam,$message,$time=null,$adaptor=null,$emid=null) 
    {
        return parent::getMessage($to, $from, $cnam, $message, $time, 'Smsconnector', $emid);
    }

    public function updateMessageByEMID($emid,$message,$adaptor=null) 
    {
        return parent::updateMessageByEMID($emid, $message, 'Smsconnector');
    }

    /**
	 * Looks up provider info given DID
	 * 
	 * @param string $did 
	 * @return array hash containing provider details
	 */
	private function lookUpProvider($did)
	{
        $sql = 'SELECT providerid FROM smsconnector_relations as r ' .
                'INNER JOIN sms_dids AS d ON r.didid = d.id ' .
                'WHERE d.did = :did';

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':did', $did, \PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetchObject();

        return $row->providerid;
	}

    public function dialPlanHooks(&$ext, $engine, $priority)
    {
        if ($engine != "asterisk") { return; }
        $section = 'smsconnector-messages';
        $p = '_X.';
        $ext->add($section, $p, '', new \ext_NoOp('Processing outbound SIP SMS'));
        $ext->add($section, $p, '', new \ext_AGI('sipsmsconn.agi'));
        $ext->add($section, $p, '', new \ext_Hangup);
    }
}
