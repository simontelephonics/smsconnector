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
		// set up providers
		outn(_("Creating Providers..."));
		$sql = sprintf("INSERT IGNORE INTO %s (name) VALUES ('telnyx'),('flowroute'),('twilio')", $this->tables['providers']);
		$this->Database->query($sql);
		out(_("Done"));

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
				return $this->addNumber($uid, $did, $name);
				break;
			case 'delete':
				return $this->deleteNumber($id);
				break;
			case 'edit':
				return $this->updateNumber($uid, $did, $name);
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
		$sql = sprintf('select rt.didid as id, p.name, u.username, u.id as uid, u.displayname, rt.did from %s as rt ' .
		'inner join userman_users as u on rt.uid = u.id inner join %s as r ' . 
		'on rt.didid = r.didid inner join %s as p on p.id = r.providerid WHERE ' . 
		'rt.adaptor = "%s" AND rt.didid = :id', $this->tablesSms['routing'], $this->tables['relations'], $this->tables['provider'], self::adapterName);
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
	public function getProviderSettings() {
		$sql = sprintf('select name, api_key, api_secret from %s', $this->tables['providers']);
		$data = $this->Database->query($sql)->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);
		return array('providers' => $data);
	}

	/**
	 * getAvailableProviders
	 * @return array list of providers
	 */
	public function getAvailableProviders() {

		return $this->getProvider("");
		// $sql = sprintf("select name from %s where api_key <> ''", $this->tables['providers']);
		// return $this->Database->query($sql)->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * getList gets a list of numbers and their associations
	 * @return array 
	 */
	public function getList()
	{
		$sql = sprintf('select rt.didid as id, p.name, u.username, rt.did from %s as rt ' .
		'inner join userman_users as u on rt.uid = u.id inner join %s as r ' . 
		'on rt.didid = r.didid inner join %s as p on p.id = r.providerid WHERE ' . 
		'rt.adaptor = "%s"', $this->tablesSms['routing'], $this->tables['relations'], $this->tables['providers'], self::adapterName);
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
	public function addNumber($uid, $did, $name)
	{
		if ( ($name == "") || ($did == "") || ($uid == ""))
		{
			return false;
		}

		$this->FreePBX->Sms->addDIDRouting($did, array($uid), self::adapterName);

		$sql  = sprintf('SELECT id FROM %s WHERE name = :name', $this->tables['providers']);
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':name', $name, \PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$providerid = $row['id'];

		$sql = "SELECT id FROM sms_dids WHERE did = :did";
		$sth = $this->Database->prepare($sql);
		$sth->execute(array(':did' => $did));
		$didid = $sth->fetchColumn();

		$sql = sprintf('INSERT INTO %s (didid, providerid) VALUES (:didid, :providerid) ON DUPLICATE KEY UPDATE providerid = :providerid', $this->tables['relations']);
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':didid', $didid, \PDO::PARAM_INT);
		$stmt->bindParam(':providerid', $providerid, \PDO::PARAM_INT);
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

		$this->addNumber($uid, $did, $name);
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
		$sql 	= sprintf("INSERT IGNORE INTO %s (name) VALUES (:provider)", $this->tables['providers']);
		$insert = $this->Database->prepare($sql);

		$sql = sprintf("UPDATE %s SET api_key = :key, api_secret = :secret WHERE name = :provider", $this->tables['providers']);
		$stmt = $this->Database->prepare($sql);
		foreach ($providers as $provider => $creds)
		{
			// create the provider in case it doesn't exist.
			$insert->execute(array(':provider' => $provider));

			// update date.
			$row = array(
				':key' 		=> $creds['api_key'],
				':secret' 	=> $creds['api_secret'],
				':provider'	=> $provider,
			);
			$stmt->execute($row);
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
				$data['settings'] = $this->getProviderSettings();
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
				$this_provider_name      = $matches[1];
                $this_provider_name_full = sprintf("FreePBX\modules\Smsconnector\Provider\%s", $this_provider_name);

                if(class_exists($this_provider_name_full))
                {
                	$this_provider_class = new $this_provider_name_full();					

					$this->providers[$this_provider_name]['name']    = $this_provider_class->getName();
                    $this->providers[$this_provider_name]['nameraw'] = $this_provider_class->getNameRaw();
                    // $this->providers[$this_provider_name]['class']   = $this_provider_name_full;
					$this->providers[$this_provider_name]['class']   = $this_provider_class;
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
}
