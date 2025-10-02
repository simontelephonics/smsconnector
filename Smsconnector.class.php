<?php
namespace FreePBX\modules;
use BMO;
use FreePBX_Helpers;
use PDO;
class Smsconnector extends FreePBX_Helpers implements BMO
{
	const adapterName = 'Smsconnector';

	public $providers;

	public $FreePBX 	= null;
	protected $Database = null;
	protected $Userman 	= null;
	protected $tables 	= array(
		'relations' => 'smsconnector_relations',
	);
	protected $tablesSms = array(
		'routing' => 'sms_routing',
		'dids'	  => 'sms_dids',
	);
	
	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->FreePBX 	= $freepbx;
		$this->Database = $freepbx->Database;
		$this->Userman 	= $freepbx->Userman;

		$this->loadProviders();
	}

	/**
	 * smsAdaptor loaded by SMS module hook (loadAdapter)
	 *
	 * @param string $adaptor Adaptor name
	 * @return Smsconnector adaptor object
	 */
	public function smsAdaptor($adaptor) 
	{
		if(!class_exists('\FreePBX\modules\Sms\Adaptor\Smsconnector')) {
			include __DIR__.'/adaptor/Smsconnector.class.php';
		}
		return new \FreePBX\modules\Sms\Adaptor\Smsconnector($adaptor);
	}

	/**
	 * Installer run on fwconsole ma install
	 *
	 * @return void
	 */
	public function install()
	{
		outn(_("Creating link to public folder..."));
		$link_public = sprintf("%s/smsconn", $this->FreePBX->Config->get("AMPWEBROOT"));
		$link_public_module = sprintf("%s/admin/modules/smsconnector/public", $this->FreePBX->Config->get("AMPWEBROOT"));
		if (! file_exists($link_public))
		{
			symlink($link_public_module, $link_public);
			out(_("Done"));
		}
		else
		{
			out(_('Skip: the path already exists!'));
		}
		// if daemon got installed in 16.0.13, remove it
		if (class_exists('FreePBX\modules\Pm2') && $this->FreePBX->Pm2->getStatus("smsconnector-sipsms"))
		{
			$this->FreePBX->Pm2->delete("smsconnector-sipsms");
		}
	}

	/**
	 * Uninstaller run on fwconsole ma uninstall
	 *
	 * @return void
	 */
	public function uninstall()
	{
		outn(_("Removing public folder link..."));
		$link_public = sprintf("%s/smsconn", $this->FreePBX->Config->get("AMPWEBROOT"));
		if(file_exists($link_public))
		{
			if(is_link($link_public))
			{
				unlink($link_public);
				if( ! file_exists($link_public))
				{
					out(_("Done"));
				}
				else
				{
					out(_("Error: the path still exists!"));
				}
			}
			else
			{
				out(_("Skip: the path is not a symbolic link!"));
			}
		}
	}

	/**
	 * Processes form submission and pre-page actions.
	 *
	 * @param string $page Display name
	 * @return void
	 */
	public function doConfigPageInit($page)
	{
		/** getReq provided by FreePBX_Helpers see https://wiki.freepbx.org/x/0YGUAQ */
		$action	   = $this->getReq('action', '');
		$providers = $this->getReq('providers');

		switch ($action) 
		{
			case 'setproviders':
				return $this->updateProviders($providers);
				break;
		}
	}

	public function getRightNav($request) 
	{
		switch($request['view'])
		{
			case 'settings':
				return load_view(dirname(__FILE__).'/views/rnav.php', array());
				break;
			default:
				//No show Nav
		}
	}

	/**
	 * Adds buttons to the bottom of pages per set conditions
	 *
	 * @param array $request $_REQUEST
	 * @return void
	 */
	public function getActionBar($request)
	{
		if ('smsconnector' == $request['display']) 
		{
			if (!isset($_GET['view'])) 
			{
				return [];
			}
			$buttons = [
				'reset' => [
					'name' => 'reset',
					'id' => 'reset',
					'value' => _("Reset")
				],
				'submit' => [
					'name' => 'submit',
					'id' => 'submit',
					'value' => _("Submit")
				]
			];
			return $buttons;
		}
	}

	/**
	 * Returns bool permissions for AJAX commands
	 * https://wiki.freepbx.org/x/XoIzAQ
	 * @param string $command The ajax command
	 * @param array $setting ajax settings for this command typically untouched
	 * @return bool
	 */
	public function ajaxRequest($req, &$setting)
	{
		// ** Allow remote consultation with Postman **
		// ********************************************
		// $setting['authenticate'] = false;
		// $setting['allowremote'] = true;
		// return true;
		// ********************************************
		switch($req)
		{
			case "get_selects":
			case "numbers_list":
			case "numbers_get":
			case "numbers_update":
			case "numbers_delete":
				return true;
				break;

			default:
				return false;
		}
		return false;
	}

	/**
	 * Handle Ajax request
	 */
	public function ajaxHandler()
	{
		$command = $this->getReq("command", "");
		$data_return = false;

		switch ($command)
		{
			case 'get_selects':
				$data['users'] 	   = array();
				$data['providers'] = array();
				foreach($this->Userman->getAllUsers() as $user)
				{
					$data['users'][$user['id']] = empty($user['displayname']) ? $user['username'] : sprintf('%s (%s)', $user['displayname'], $user['username']);
				}
				foreach ($this->getAvailableProviders() as $provider => $info)
				{
					$data['providers'][$info['nameraw']] = $info['name'];
				}
				$data_return = array("status" => true, 'data' => $data);
				break;

			case 'numbers_list':
				$data_return = $this->getList();
				break;

			case 'numbers_get':
				$id 	= $this->getReq("id", null);
				if (empty($id))
				{
					$data_return = array("status" => false, "message" => _("ID is missing!"));
				}
				else if (! $this->isExistDIDByID($id))
				{
					$data_return = array("status" => false, "message" => _("ID does not exist!"));
				}
				else
				{
					$dataId = $this->getNumber($id);
					$data_return = array("status" => true, "data" => $dataId);
				}
				break;

			case 'numbers_update':
				$getdata = $this->getReq("data", array());
				$id   = $getdata['id'];
				$did  = $getdata['didNumber'];
				$uids = $getdata['uidsNumber'];
				$name = $getdata['providerNumber'];

				$uids = explode(",", $uids);
				try
				{
					if ($getdata['type'] == 'edit')
					{
						$this->updateNumber($uids, $did, $name);
						$data_return = array("status" => true, "message" => _("Number updated successfully"));
					}
					else
					{
						$this->addNumber($uids, $did, $name);
						$data_return = array("status" => true, "message" => _("Number created successfully"));
					}
				}
				catch (\Exception $e)
				{
					$data_return = array("status" => false, "message" => $e->getMessage());
				}
				
				break;

			case 'numbers_delete':
				$id = $this->getReq("id", null);
				if (empty($id))
				{
					$data_return = array("status" => false, "message" => _("ID is missing!"));
				}
				else if (! $this->isExistDIDByID($id))
				{
					$data_return = array("status" => false, "message" => _("ID does not exist!"));
				}
				else if ($this->deleteNumber($id))
				{
					$data_return = array("status" => true, "message" => _("Number delete successfully"));
				}
				else
				{
					$data_return = array("status" => false, "message" => _("Number delete failed!"));
				}
				break;

			default:
				$data_return = array("status" => false, "message" => _("Command not found!"), "command" => $command);
		}
		return $data_return;
	}

	/**
	 * getProviderSettings 
	 * @return array returns an associative array
	 */
	public function getProviderSettings()
	{
		return array('providers' => $this->getAll('provider'));
	}

	/**
	 * getAvailableProviders
	 * @return array list of providers that are configured and available for use
	 */
	public function getAvailableProviders()
	{
		$retlist = array();
		$list = $this->getProvider("");
		foreach ($list as $key => $value)
		{
			if ($value['class']->isAvailable())
			{
				$retlist[$key] = $value;
			}
		}
		return $retlist;
	}

	/**
	 * getList gets a list of numbers and their associations
	 * @return array 
	 */
	public function getList()
	{
		$sql = sprintf('SELECT r.id, rt.didid, r.providerid AS name, GROUP_CONCAT(DISTINCT rt.uid) AS users, rt.did FROM %s AS rt ' .
		'INNER JOIN %s as r ON rt.didid = r.didid ' .
		'WHERE rt.adaptor = "%s" ' .
		'GROUP BY r.id, rt.didid, r.providerid, rt.did', $this->tablesSms['routing'], $this->tables['relations'], self::adapterName);
		
		$data = $this->Database->query($sql)->fetchAll(\PDO::FETCH_NAMED);
		foreach ($data as $key => &$value)
		{
			$value['users'] = $this->getInfoUserByID($value['users'], true);
		}
		return $data;
	}

	/**
	 * getNumber Gets an individual item by smsconnector_relations.ID
	 * @param  int $id Item ID
	 * @return array Returns an associative array with id, subject and body.
	 */
	public function getNumber($id)
	{
		$data_return = null;
		if ($this->isExistDIDByID($id))
		{
			$sql = sprintf('SELECT r.id, rt.didid, r.providerid AS name, GROUP_CONCAT(DISTINCT rt.uid) AS users, rt.did FROM %s as rt ' .
			'INNER JOIN %s as r ON rt.didid = r.didid ' .
			'WHERE rt.adaptor = "%s" AND rt.didid = :id ' .
			'GROUP BY r.id, rt.didid, r.providerid, rt.did', $this->tablesSms['routing'], $this->tables['relations'], self::adapterName);
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetchObject();
			$data_return = [
				'id' 		  => $row->id,
				'didid' 	  => $row->didid,
				'name'		  => $row->name,
				'users' 	  => $this->getInfoUserByID($row->users, true),
				'did' 		  => $row->did,
			];
		}
		return $data_return;
	}

	/**
	 * getInfoUserByID We obtain the information of the users with the userman module.
	 * @param mixed $uid			UID or array of UIDs of the users that we want to obtain information.
	 * @param bool $needExplode		True if we need to exploit the uids, False (default) if nothing needs to be done.
	 * @param string $explodeChar	The character the used for the explode (default ',').
	 * @param array $info_return	The array of the information that we wish to obtain (default userman and displayname).
	 * @return array				Array of the information the users.
	 */
	private function getInfoUserByID($uid, $needExplode = false, $explodeChar = ",", $info_return = null)
	{
		if ($needExplode && ! is_array($needExplode))
		{
			$uid = explode($explodeChar, $uid);
		}
		if (! is_array($uid))
		{
			$uid = array($uid);
		}

		$data_return = array();
		if (is_null($info_return))
		{
			$info_return = array('username', 'displayname');
		}
		foreach ($uid as $userid)
		{
			$user_info = $this->Userman->getUserByID($userid);
			$data_user = array('uid' => $userid);

			foreach ($info_return as $option)
			{
				$data_user[$option] = $user_info[$option];
			}
			$data_return[$userid] = $data_user;
		}
		return $data_return;
	}

	/* Helper functions */
	public function getUsersByDid($did)
	{
		return $this->FreePBX->Sms->getAssignedUsers($did);
	}

	public function getDidsByUser($uid)
	{
		return $this->FreePBX->Sms->getDIDs($uid);
	}

	public function getUidByDefaultExtension($extension)
	{
		return $this->Userman->getUserByDefaultExtension($extension)['id'];
	}

	public function getSipDefaultDidByUid($uid)
	{
		return $this->Userman->getModuleSettingByID($uid, 'smsconnector', 'sipsmsdefaultdid', true, false);
	}

	/**
	 * getSIPMessageDeviceByUserID
	 * @param int $uid			    UID of the users that we want to obtain information.
	 * @return mixed				extension number or NULL
	 */
	public function getSIPMessageDeviceByUserID($uid)
	{
		if ($this->Userman->getModuleSettingByID($uid, 'smsconnector', 'sipsmsenabled', false, false))
		{
			$user = $this->Userman->getUserByID($uid);
			$extension = $user['default_extension'];
			$device = $this->FreePBX->Core->getDevice($extension);

			// only select PJSIP devices that have been set with the appropriate message_context
			if ($device['tech'] == 'pjsip' && $device['message_context'] == 'smsconnector-messages')
			{
				return $extension;
			}
		}
		return NULL;
	}

	/**
	 * processOutboundSip			Used by AGI script to send SMS via connector
	 * @param string $to			To (destination)
	 * @param string $from			Extension that is sending the SMS
	 * @param string $messageBody	SMS body
	 * @return bool
	 */
	public function processOutboundSip($to, $from, $messageBody)
	{
		freepbx_log(FPBX_LOG_INFO, sprintf(_("Processing SIP SMS from %s to %s"), $from, $to), true);
		$did = $actualTo = NULL;
		$allowedToSend = false;
		$retval = true;
		if (preg_match('/^\+?(\d+)\+\+?(\d+)$/', $to, $matches))
		{
			$did = $matches[1];
			$actualTo = $matches[2];
		}
		elseif (preg_match('/^\+?(\d+)$/', $to, $matches))
		{
			$actualTo = $matches[1];
		}

		$uid = $this->getUidByDefaultExtension($from);
		if ($this->Userman->getModuleSettingByID($uid, 'smsconnector', 'sipsmsenabled', false, false))
		{
			if ($did)
			{
				if (in_array($did, $this->getDidsByUser($uid))) { $allowedToSend = true; }
			}
			else
			{
				if ($defaultDid = $this->getSipDefaultDidByUid($uid))
				{
					$did = $defaultDid;
					$allowedToSend = true;
				}
			}
		}

		if ($allowedToSend && $actualTo)
		{
			$formattedTo = (preg_match('/^[2-9][0-9]{2}[2-9][0-9]{6}$/', $actualTo)) ? '1'.$actualTo : $actualTo; // format NANP

			$adaptor = $this->FreePBX->Sms->getAdaptor($did);
			if(is_object($adaptor)) {
				$o = $adaptor->sendMessage($formattedTo, $did, '', $messageBody);
				if(! $o['status']) {
					freepbx_log(FPBX_LOG_INFO, sprintf(_("Outbound message failed: %s"), $o['message']), true);
					$retval = false;
				}
			} else {
				freepbx_log(FPBX_LOG_INFO, sprintf(_("No adaptor found for DID %s"), $did), true);
				$retval = false;
			}
		}
		else
		{
			freepbx_log(FPBX_LOG_INFO, sprintf(_("%s tried to send SMS from %s but is not allowed!"), $from, $did), true);
			$retval = false;
		}

		return $retval;
	}

	/**
	 * inboundHookForSip			Hooked by AdaptorBase getMessage on inbound SMS. Relays SMS to SIP devices if enabled.
	 * @param string $to			To (destination)
	 * @param string $from			caller ID
	 * @param string $message		SMS body
	 */
	public function inboundHookForSip($id, $to, $from, $cnam, $message, $time, $adaptor, $emid, $threadid, $didid)
	{
		// lookup users for DID
		$uids = $this->getUsersByDid($to);

		foreach ($uids as $uid)
		{
			// get device that can receive SMS
			$device = $this->getSIPMessageDeviceByUserID($uid);

			if ($device)
			{
				if ($to != $this->getSipDefaultDidByUid($uid))
				{
					// format the caller ID to include the DID, which will allow replying via non-default DID
					$sipFrom = sprintf("%s+%s", $to, $from);
				}
				else
				{
					$sipFrom = $from;
				}

				// get contacts
				$result = $this->FreePBX->astman->send_request('Getvar', array('Variable' => "PJSIP_DIAL_CONTACTS($device)"));
				$contacts = array();
				if (!empty($result['Value']))
				{
					$contacts = explode('&', $result['Value']);
				}

				if (!empty($contacts))
				{
					foreach ($contacts as $contact) // message all registered
					{
						$sipTo = sprintf("pjsip:%s", substr($contact, 6)); // replace "PJSIP/" with "pjsip:"
						$result = $this->FreePBX->astman->MessageSend($sipTo, $sipFrom, $message);
						if ($result['Response'] == 'Error')
						{
							freepbx_log(FPBX_LOG_INFO, sprintf(_("Error sending message to %s: %s"), $contact, $result['Message']));
						}
					}
				}
				else // no contacts registered - send email if enabled
				{
					if ($this->Userman->getModuleSettingByID($uid, 'smsconnector', 'sipsmsemailoffline', false, false)) {
						// if no email address defined for user, does nothing
						//TODO: Generate body from template.
						$body = sprintf(_("While offline, you received an SMS from %s to %s:\n\n%s"), $from, $to, $message);
						//TODO: Allow setting the subject message
						$subject = _('SMS received while offline');
						$this->Userman->sendEmail($uid, $subject, $body);
					}
				}
			}
		}
	}

	/**
	 * addNumber Add a number
	 * @param int $uid userman user id
	 * @param string $did DID
	 * @param string $name name of the SMS provider
	 */
	public function addNumber($uid, $did, $name, $checkExists = true)
	{
		if (! is_array($uid))
		{
			$uid = array($uid);
		}

		if (preg_match('/^[2-9]\d{2}[2-9]\d{6}$/', $did)) // ten digit NANP, make it 11-digit
		{
			$did = '1'.$did;
		}

		if ( ($name == "") || ($did == "") || (empty($uid)))
		{
			throw new \Exception(_('Necessary data is missing!'));
		}
		else if (($checkExists == true) && ($this->isExistDID($did)))
		{
			if (! empty($this->getUsersByDid($did))) // DID exists with users assigned.
				throw new \Exception(_('The DID already exists!'));
				// but if no users assigned, the DID is abandoned. We can proceed to add it again.
		}

		$this->FreePBX->Sms->addDIDRouting($did, $uid, self::adapterName);

		$sql = sprintf("SELECT id FROM %s WHERE did = :did", $this->tablesSms['dids']);
		$sth = $this->Database->prepare($sql);
		$sth->execute(array(':did' => $did));
		$didid = $sth->fetchColumn();

		if (! empty($didid))
		{
			$sql = sprintf('INSERT INTO %s (didid, providerid) VALUES (:didid, :provider) ON DUPLICATE KEY UPDATE providerid = :provider', $this->tables['relations']);
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':didid', $didid, \PDO::PARAM_INT);
			$stmt->bindParam(':provider', $name, \PDO::PARAM_STR);
			$stmt->execute();
			return true;
		}

		return false;
	}

	/**
	 * updateNumber Updates the given ID
	 * @param  int $uid userman user ID
	 * @param  string $did DID
	 * @param  string $name provider name
	 * @return bool          Returns true on success or false on failure
	 */
	public function updateNumber($uid, $did, $name)
	{
		return $this->addNumber($uid, $did, $name, false);
	}
	
	/**
	 * deleteNumber Deletes the given number by smsconnector_relations.didid
	 * @param  int $id      ID
	 * @return bool         Returns true on success or false on failure
	 */
	public function deleteNumber($id)
	{
		if ($this->isExistDIDByID($id))
		{
			$sql = sprintf('DELETE FROM %s WHERE didid = :id', $this->tables['relations']);
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			$stmt->execute();

			$sql = sprintf('DELETE FROM %s WHERE didid = :id', $this->tablesSms['routing']);
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			$stmt->execute();

			return true;
		}
		return false;
	}

	/**
	 * isExistDIDByID Check if the ID exists using the ID or DIDID (table relations)
	 * @param int $id           ID
	 * @param bool $useddidid	True used column DIDID, False used column ID (defualt DIDID).
	 * @return bool             Returns true on existe of false if is not exist or not definded id.
	 */
	public function isExistDIDByID($id, $useddidid = true)
	{
		if (trim($id) != "")
		{
			$sql  = sprintf('SELECT COUNT(*) FROM %s WHERE %s = :id', $this->tables['relations'], ($useddidid ? 'didid' : 'id'));
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->fetchColumn() > 0)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * isExistDID Check if the Did exist
	 * @param string $did Number DID
	 * @return bool Returns true on exist and False if not exist.
	 */
	public function isExistDID($did)
	{
		$data_raturn = false;
		if (trim($did) != "")
		{
			$sql  = sprintf('SELECT COUNT(*) FROM %s as r INNER JOIN %s AS d ON r.didid = d.id WHERE d.did = :did', $this->tables['relations'], $this->tablesSms['dids']);
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':did', $did, \PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->fetchColumn() > 0)
			{
				$data_raturn = true;
			}
		}
		return $data_raturn;
	}

	/**
	 * updateProviders
	 * @param array hash of provider settings from form
	 * @return bool success or failure
	 */
	public function updateProviders($providers)
	{
		foreach ($providers as $provider => $creds)
		{
			$this->setProviderConfig($provider, $creds);
		}
		return true;
	}

	/**
	 * getUsersWithDids
	 * @return array of user IDs associated with SMS DIDs
	 */
	public function getUsersWithDids() 
	{
		$sql = sprintf('SELECT DISTINCT uid FROM %s', $this->tablesSms['routing']);
		return $this->Database->query($sql)->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * This returns html to the main page
	 *
	 * @return string html
	 */
	public function showPage($page, $params = array())
	{
		$request = $_REQUEST;
		$data = array(
			"smsconnector" => $this,
			'request' 	   => $request,
			'page' 	  	   => $page,
		);
		$data = array_merge($data, $params);

		switch ($page)
		{
			case 'main':
				$data_return = load_view(__DIR__ . '/views/page.main.php', $data);
				break;

			case 'grid':
				$data_return  = load_view(__DIR__ . '/views/view.number.grid.php', $data);
				$data_return .= load_view(__DIR__ . '/views/view.number.form.php', $data);
				break;

			case 'settings':
				foreach ($this->listProviders() as $provider)
				{
					$data['settings'][$provider]['info'] = $this->getProvider($provider);
					// unset($data['settings'][$provider]['info']['class']);
					$data['settings'][$provider]['value'] = $this->getProviderConfig($provider);
				}
				$data_return = load_view(__DIR__ . '/views/settings.php', $data);
				break;

			case 'userman':
				$data['userman'] =& $this->Userman;
				$data_return = load_view(__DIR__ . '/views/view.userman.user.php', $data);
				break;

			default:
				$data_return = sprintf(_("Page Not Found (%s)!!!!"), $page);
		}
		return $data_return;
	}

	public function usermanShowPage() 
	{
		$request = $_REQUEST;
		if(isset($request['action'])) 
		{
			switch($request['action']) 
			{
				case 'adduser':
				case 'showuser':
					$sipSmsEnabled 	  = null;
					$defaultDid       = null;
					$emailOffline	  = null;

					if(isset($request['user']))
					{
						$user 		   = $this->Userman->getUserByID($request['user']);
						$sipSmsEnabled = $this->Userman->getModuleSettingByID($user['id'],'smsconnector','sipsmsenabled',true);
						$defaultDid    = $this->Userman->getModuleSettingByID($user['id'],'smsconnector','sipsmsdefaultdid',true);
						$emailOffline  = $this->Userman->getModuleSettingById($user['id'],'smsconnector','sipsmsemailoffline',true);
					}
					return array(
						array(
							'title' => _('SMS Connector'),
							'rawname' => 'smsconnector',
							'content' => $this->showPage('userman', array(
								'error' => empty($error)?'':$error,
								'sipsmsenabled' => $sipSmsEnabled,
								'sipsmsdefaultdid' => $defaultDid,
								'sipsmsemailoffline' => $emailOffline,
								'dids' => !empty($request['user']) ? $this->getDidsByUser($request['user']) : array()
							)),
						)
					);
					break;
			}
		}
	}

	public function usermanAddUser($id, $display, $data)
	{
		$this->usermanUpdateUser($id, $display, $data);
	}

	public function usermanUpdateUser($id, $display, $data)
	{
		$post = $_POST;
		if($display == 'userman' && isset($post['type']) && $post['type'] == 'user')
		{
			if(isset($post['sipsmsenabled']))
			{
				if($post['sipsmsenabled'] == "true")
				{
					$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsenabled', true);
					$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsdefaultdid', !empty($post['sipsmsdefaultdid']) ? $post['sipsmsdefaultdid'] : null);
					if (!empty($post['sipsmsemailoffline']))
					{
						$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsemailoffline', ($post['sipsmsemailoffline'] == "true") ? true : false);
					}
					else
					{
						$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsemailoffline', null);
					}
				}
				elseif($post['sipsmsenabled'] == "false")
				{
					$this->Userman->setModuleSettingByID($id,'smsconnector','sipsmsenabled',false);
				}
				else
				{
					$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsenabled', null);
					$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsdefaultdid', null);
					$this->Userman->setModuleSettingByID($id, 'smsconnector', 'sipsmsemailoffline', null);
				}
			}
		}
	}

	public function usermanDelUser($id, $display, $data) {
		freepbx_log(FPBX_LOG_INFO, sprintf(_("SMS Connector received delete request for user id %s ; removing all DID assignments"), $id));
		$this->FreePBX->Sms->addUserRouting($id,array(),'Smsconnector'); // "add" with empty array is delete
	}

	private function loadProviders()
    {
		include_once dirname(__FILE__) . "/providers/providerBase.php";
        $this->providers = array();
        foreach (glob(dirname(__FILE__) . "/providers/provider-*.php") as $filename)
        {
            if (file_exists($filename))
            {
                include_once $filename;

                preg_match('/provider-(.*)\.php/i', $filename, $matches);
				$this_provider_name       = $matches[1];
				$this_provider_name_full  = sprintf("FreePBX\modules\Smsconnector\Provider\%s", $this_provider_name);
				$this_provider_name_lower = strtolower($this_provider_name);

                if(class_exists($this_provider_name_full))
                {
					$this_provider_class = new $this_provider_name_full();					

					$this->providers[$this_provider_name_lower]['name']    	  = $this_provider_class->getName();
					$this->providers[$this_provider_name_lower]['nameraw'] 	  = $this_provider_class->getNameRaw();
					$this->providers[$this_provider_name_lower]['configs']	  = $this_provider_class->getConfigInfo();
					$this->providers[$this_provider_name_lower]['webhook'] 	  = $this_provider_class->getWebHookUrl();
					$this->providers[$this_provider_name_lower]['class_full'] = $this_provider_name_full;
					$this->providers[$this_provider_name_lower]['class_name'] = $this_provider_name;
					$this->providers[$this_provider_name_lower]['class']   	  = $this_provider_class;
                }
            }
        }
    }

	public function listProviders()
	{
		return array_keys($this->providers);
	}

	public function getProvider($name)
	{
		$return_data = array();
		if (empty($name))
		{
			$return_data = $this->providers;
		}
		else
		{
			if (array_key_exists($name, $this->providers))
			{
				$return_data = $this->providers[$name];
			}
		}
		return $return_data;
	}

	public function getProviderConfigDefault($name)
	{
		$data_return = array();
		$info = $this->getProvider($name);
		foreach ($info['configs'] as $config => $options)
		{
			$data_return[$config] = isset($options['default']) ? $options['default'] : '';
		}
		return $data_return;
	}

	public function getProviderConfig($name)
	{
		$data_return = array();

		$default = $this->getProviderConfigDefault($name);
		$setting = $this->getConfig($name, 'provider');

		foreach ($default as $option => $value)
		{
			$data_return[$option] = isset($setting[$option]) ? $setting[$option] : $value;
		}
		return $data_return;
	}

	public function setProviderConfig($name, $config)
	{
		$this->setConfig($name, $config, 'provider');
	}

	public function myDialplanHooks()
	{
		return true;
	}

	public function doDialplanHook(&$ext, $engine, $priority)
	{
		foreach ($this->getUsersWithDids() as $uid)
		{
			if ($this->Userman->getModuleSettingByID($uid, 'smsconnector', 'sipsmsenabled', false, false))
			{
				$user = $this->Userman->getUserByID($uid);
				$extension = $user['default_extension'];
				$device = $this->FreePBX->Core->getDevice($extension);
				if ($device['tech'] == 'pjsip')
				{
					$de = $this->kvArrayifyDeviceValues($device);
					$de['message_context']['value'] = 'smsconnector-messages';
					$this->FreePBX->Core->delDevice($extension, true);
					$this->FreePBX->Core->addDevice($extension, 'pjsip', $de, true);
				}
			}
		}
	}

	private function kvArrayifyDeviceValues($values) { // stolen verbatim from Core module
		$response = array();
		$flag = 2;
		$ignoreTheseKeys = array('id', 'tech');
		foreach($values as $key => $value) {
			if (in_array($key, $ignoreTheseKeys)) {
				continue;
			}

			$response[$key] = array(
					'value' => $value,
					'flag' => $flag++
			);
		}
		return $response;
	}
}
