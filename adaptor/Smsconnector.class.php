<?php
namespace FreePBX\modules\Sms\Adaptor;
use PDO;
class Smsconnector extends \FreePBX\modules\Sms\AdaptorBase {
    /**
	* Create the Adaptor\Smsconnector Class statically while checking to make sure the class hasn't already been loaded
	*
	* @param object Database Object
	* @return object Adaptor\Smsconnector object
	*/
	static function &create() {
		static $obj;
		if (!isset($obj)) {
			$obj = new Smsconnector();
		}
		return $obj;
	}

    /**
     * Extend AdaptorBase send/receive methods with our specific cases
     */

    public function sendMedia($to,$from,$cnam,$message=null,$files=array(),$time=null,$adaptor=null,$emid=null,$chatId='') {
        // store in database
        $retval = array();
        try {
            $retval['id'] = parent::sendMedia($to, $from, $cnam, $message, $files, $time, 'Smsconnector', $emid, $chatId);
            $retval['status'] = true;
        } catch (\Exception $e) {
            throw new \Exception('Unable to store media: '.$e->getmessage());
        }
        
        // generate media urls
        $media_urls = array();
        $ampWebAddress = $this->FreePBX->Config->get_conf_setting('AMPWEBADDRESS');
        if (empty($ampWebAddress)) { // we're going to make an educated guess and make an HTTPS URL from the external IP
            $ampWebAddress = $this->FreePBX->Sipsettings->getConfig('externip');
         }
        $sql = 'SELECT id, name FROM sms_media WHERE mid = :mid';
        $stmt = $this->db->prepare($sql);
		$stmt->bindParam(':mid', $retval['id'], \PDO::PARAM_INT);
		$stmt->execute();
        $media = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($media as $media_item) {
            $media_urls[] = 'https://' . $ampWebAddress . '/smsconn/media.php?id=' . $media_item['id'] . '&name=' . $media_item['name'];
        }
        // look up provider info containing name and api credentials
        $provider = $this->lookUpProvider($from);

        // send via connector
        switch ($provider['name']) {

            case 'telnyx':
                $req = array(
                    'from' => '+'.$from, 
                    'to' => '+'.$to, 
                    'media_urls' => $media_urls
                );
                if ($message) {
                    $req['text'] = $message;
                }
                $this->sendTelnyx($provider, $req, $retval['id']);
                break;

            case 'flowroute':
                $attr = array(
                    "to" => '+'.$to,
                    "from" => '+'.$from,
                    "is_mms" => "true",
                    "media_urls" => $media_urls
                );
                if ($message) {
                    $attr['body'] = $message;
                }
                $req = json_encode(
                    array(
                        "data" => array(
                            "type" => "message",
                            "attributes" => $attr
                        )
                    )
                );
                $this->sendFlowroute($provider, $req, $retval['id']);
                break;
        }
        
        return $retval;
    }

    public function sendMessage($to,$from,$cnam,$message,$time=null,$adaptor=null,$emid=null,$chatId='') {
        // store in database
        $retval = array();
        try {
            $retval['id'] = parent::sendMessage($to, $from, $cnam, $message, $time, 'Smsconnector', $emid, $chatId);
            $retval['status'] = true;
        } catch (\Exception $e) {
            throw new \Exception('Unable to store message: '.$e->getMessage());
        }
    
        // look up provider info containing name and api credentials
        $provider = $this->lookUpProvider($from);

        // send via connector
        switch ($provider['name']) {

            case 'telnyx':
                $req = array(
                    'from' => '+'.$from, 
                    'to' => '+'.$to, 
                    'text' => $message
                );
                $this->sendTelnyx($provider, $req, $retval['id']);
                break;

            case 'flowroute':
                $req = json_encode(
                    array(
                        "data" => array(
                            "type" => "message",
                            "attributes" => array(
                                "to" => '+'.$to,
                                "from" => '+'.$from,
                                "body" => $message
                            )
                        )
                    )
                );
                $this->sendFlowroute($provider, $req, $retval['id']);
                break;
        }

        return $retval;
    }

    private function sendTelnyx($provider, $payload, $mid) {
        require_once(__DIR__.'/include/telnyx-php/init.php');
        \Telnyx\Telnyx::setApiKey($provider['api_key']);
        try {
            $telnyxResponse = \Telnyx\Message::Create($payload);
            freepbx_log(FPBX_LOG_INFO, $telnyxResponse, true);
            $this->setDelivered($mid);
        } catch (\Exception $e) {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }

    private function sendFlowroute($provider, $payload, $mid) {
        $options = array("auth" => array($provider['api_key'], $provider['api_secret']));
        $headers = array("Content-Type" => "application/vnd.api+json");
        $url = 'https://api.flowroute.com/v2.2/messages';
        $session = \FreePBX::Curl()->requests($url);
        try {
            $flowrouteResponse = $session->post('', $headers, $payload, $options);
            freepbx_log(FPBX_LOG_INFO, $flowrouteResponse->body, true);
            $this->setDelivered($mid);
        } catch (\Exception $e) {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }

    public function getMessage($to,$from,$cnam,$message,$time=null,$adaptor=null,$emid=null) {
        return parent::getMessage($to, $from, $cnam, $message, $time, 'Smsconnector', $emid);
    }

    public function updateMessageByEMID($emid,$message,$adaptor=null) {
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
		$sql = 'SELECT p.name, p.api_key, p.api_secret FROM smsconnector_providers AS p INNER JOIN ' .
				'smsconnector_relations AS r ON p.id = r.providerid ' .
				'INNER JOIN sms_dids AS d ON r.didid = d.id ' .
				'WHERE d.did = :did';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':did', $did, \PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetchObject();
		return array( 
            'name' => $row->name,
            'api_key' => $row->api_key,
            'api_secret' => $row->api_secret
        );
	}

    /**
     * Set an outbound message to delivered
     * 
     * @param int $id message id
     */
    private function setDelivered($id)
    {
        $sql = 'UPDATE sms_messages SET delivered = 1 where id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $this;
    }
}
