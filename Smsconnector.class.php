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
		'providers' => 'smsconnector_providers',
		'relations' => 'smsconnector_relations',
	);
	protected $tablesSms = array(
		'routing' => 'sms_routing'
	);

	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->FreePBX 	= $freepbx;
		$this->Database = $freepbx->Database;
		$this->Userman 	= $freepbx->Userman;

		$this->loadProvieders();
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
		outn(_("Creating Link to Public Folder..."));
		$link_public = sprintf("%s/smsconn", $this->FreePBX->Config->get("AMPWEBROOT"));
		$link_public_module = sprintf("%s/admin/modules/smsconnector/public", $this->FreePBX->Config->get("AMPWEBROOT"));
		if (! file_exists($link_public))
		{
			symlink($link_public_module, $link_public);
			out(_("Done"));
		}
		else
		{
			out(_('Skip: The Path Already Exists!'));
		}
	}

	/**
	 * Uninstaller run on fwconsole ma uninstall
	 *
	 * @return void
	 */
	public function uninstall()
	{
		outn(_("Remove Folder Public..."));
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
					out(_("Error: The Path Still Exists!"));
				}
			}
			else
			{
				out(_("Skip: The Path Is Not a Symbolic Link!"));
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
		$action = $this->getReq('action', '');
		$id = $this->getReq('id', '');
		$uid = $this->getReq('uid');
		$did = $this->getReq('did');
		$name = $this->getReq('name');
		$providers = $this->getReq('providers');

		switch ($action) {
			case 'add':
				try 
				{
					$this->addNumber($uid, $did, $name);
					header("Location: config.php?display=smsconnector");
					exit();
				}
				catch (\Exception $e)
				{
					$_REQUEST['error_add'] = $e->getMessage();
				}
				break;

			case 'delete':
				return $this->deleteNumber($id);
				break;

			case 'edit':
				$this->updateNumber($uid, $did, $name);
				header("Location: config.php?display=smsconnector");
				exit();
				break;

			case 'setproviders':
				return $this->updateProviders($providers);
				break;
		}
	}

	public function getRightNav($request) {

		switch($request['view'])
		{
			case 'settings':
			case 'form':
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
		if ('smsconnector' == $request['display']) {
			if (!isset($_GET['view'])) {
				return [];
			}
			$buttons = [
				'delete' => [
					'name' => 'delete',
					'id' => 'delete',
					'value' => _('Delete')
				],
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
			if (!isset($_GET['id']) || empty($_GET['id'])) {
				unset($buttons['delete']);
			}
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
	public function ajaxRequest($command, &$setting)
	{
		//The ajax request
		if ("getJSON" == $command) {
			return true;
		}
		return false;
	}

	/**
	 * Handle Ajax request
	 * @url ajax.php?module=smsconnector&command=getJSON&jdata=grid
	 *
	 * @return array
	 */
	public function ajaxHandler()
	{
		if ('getJSON' == $_REQUEST['command'] && 'grid' == $_REQUEST['jdata']) {
			return $this->getList();
		}
		return json_encode([
			'status' => false,
			'message' => _("Invalid Request")
		]);
	}

	//Module getters These are all custom methods
	/**
	 * getOne Gets an individual item by ID
	 * @param  int $id Item ID
	 * @return array Returns an associative array with id, subject and body.
	 */
	public function getOne($id)
	{
		$sql = sprintf('SELECT rt.didid as id, r.providerid as name, u.username, u.id as uid, u.displayname, rt.did from %s as rt ' .
		'INNER JOIN userman_users as u ON rt.uid = u.id ' .
		'INNER JOIN %s as r ON rt.didid = r.didid ' .
		'WHERE rt.adaptor = "%s" AND rt.didid = :id', $this->tablesSms['routing'], $this->tables['relations'], self::adapterName);
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetchObject();
		return [
			'id' => $row->id,
			'name' => $row->name,
			'username' => $row->username,
			'displayname' => $row->displayname,
			'uid' => $row->uid,
			'did' => $row->did
		];
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
	 * @return array list of providers
	 */
	public function getAvailableProviders()
	{
		return $this->getProvider("");
	}

	/**
	 * getList gets a list of numbers and their associations
	 * @return array 
	 */
	public function getList()
	{
		$sql = sprintf('SELECT rt.didid as id, r.providerid as name, u.username, rt.did from %s as rt ' .
		'INNER JOIN userman_users as u on rt.uid = u.id ' .
		'INNER JOIN %s as r ON rt.didid = r.didid ' .
		'WHERE rt.adaptor = "%s"', $this->tablesSms['routing'], $this->tables['relations'], self::adapterName);
		$data = $this->Database->query($sql)->fetchAll(\PDO::FETCH_NAMED);
		return $data;
	}
	//Module setters these are all custom methods.

	/**
	 * addNumber Add a number
	 * @param int $uid userman user id
	 * @param string $did DID
	 * @param string $name name of the SMS provider
	 */
	public function addNumber($uid, $did, $name, $checkExists = true)
	{
		if ( ($name == "") || ($did == "") || ($uid == ""))
		{
			throw new \Exception('Necessary data is missing!');
		}

		if ($checkExists == true)
		{
			$sql = 'SELECT COUNT(*) FROM smsconnector_relations as r INNER JOIN sms_dids AS d ON r.didid = d.id WHERE d.did = :did';
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':did', $did, \PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->fetchColumn() > 0)
			{
				throw new \Exception('The DID already exists!');
			}
		}

		$this->FreePBX->Sms->addDIDRouting($did, array($uid), self::adapterName);

		$sql = "SELECT id FROM sms_dids WHERE did = :did";
		$sth = $this->Database->prepare($sql);
		$sth->execute(array(':did' => $did));
		$didid = $sth->fetchColumn();

		$sql = sprintf('INSERT INTO %s (didid, providerid) VALUES (:didid, :provider) ON DUPLICATE KEY UPDATE providerid = :provider', $this->tables['relations']);
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':didid', $didid, \PDO::PARAM_INT);
		$stmt->bindParam(':provider', $name, \PDO::PARAM_STR);
		$stmt->execute();

		return true;
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
		if ( ($name == "") || ($did == "") || ($uid == ""))
		{
			return false;
		}

		$this->addNumber($uid, $did, $name, false);
		return true;
	}
	/**
	 * deleteNumber Deletes the given number by didid
	 * @param  int $id      DID ID
	 * @return bool          Returns true on success or false on failure
	 */
	public function deleteNumber($id)
	{
		if ($id == "") { return false; }

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
	public function getUsersWithDids() {
		$sql = sprintf('SELECT uid FROM %s', $this->tablesSms['routing']);
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
				$data_return = load_view(__DIR__ . '/views/grid.php', $data);
				break;

			case 'form':
				$data['userman'] =& $this->Userman;
				if (!empty($request['id']))
				{
					$data['edit_data'] = $this->getOne($request['id']);
				}
				$data_return = load_view(__DIR__ . '/views/form.php',  $data);
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

	public function usermanShowPage() {
		$request = $_REQUEST;
		if(isset($request['action'])) {
			switch($request['action']) {
				case 'adduser':
				case 'showuser':
					return array(
						array(
							'title' => _('SMS Connector'),
							'rawname' => 'smsconnector',
							'content' => $this->showPage('userman'),
						)
					);
					break;
			}
		}
	}

	public function usermanAddUser($id, $display, $data) {}

	public function usermanUpdateUser($id, $display, $data) {}

	public function usermanDelUser($id, $display, $data) {}

	private function loadProvieders()
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

					$this->providers[$this_provider_name_lower]['name']    	= $this_provider_class->getName();
					$this->providers[$this_provider_name_lower]['nameraw'] 	= $this_provider_class->getNameRaw();
					$this->providers[$this_provider_name_lower]['configs'] 	= $this_provider_class->getConfigInfo();
					$this->providers[$this_provider_name_lower]['class_full'] = $this_provider_name_full;
					$this->providers[$this_provider_name_lower]['class_name'] = $this_provider_name;
					$this->providers[$this_provider_name_lower]['class']   	= $this_provider_class;
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

}
