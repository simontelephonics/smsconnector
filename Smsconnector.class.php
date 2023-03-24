<?php
namespace FreePBX\modules;
use BMO;
use FreePBX_Helpers;
use PDO;
class Smsconnector extends FreePBX_Helpers implements BMO
{
	public $FreePBX = null;

	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->Database = $freepbx->Database;
		$this->Userman = $freepbx->Userman;
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
		$sql = "INSERT IGNORE INTO smsconnector_providers (name) VALUES ('telnyx'),('flowroute'),('twilio')";
		$this->Database->query($sql);

		if (! file_exists($this->FreePBX->Config->get("AMPWEBROOT") . '/smsconn')) {
			symlink($this->FreePBX->Config->get("AMPWEBROOT") . '/admin/modules/smsconnector/public', 
				$this->FreePBX->Config->get("AMPWEBROOT") . '/smsconn');
		}
	}

	/**
	 * Uninstaller run on fwconsole ma uninstall
	 *
	 * @return void
	 */
	public function uninstall()
	{
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
		$sql = 'select rt.didid as id, p.name, u.username, u.id as uid, u.displayname, rt.did from sms_routing as rt ' .
		'inner join userman_users as u on rt.uid = u.id inner join smsconnector_relations as r ' . 
		'on rt.didid = r.didid inner join smsconnector_providers as p on p.id = r.providerid WHERE ' . 
		'rt.adaptor = "Smsconnector" AND rt.didid = :id';
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
		$sql = 'select name, api_key, api_secret from smsconnector_providers';
		$data = $this->Database->query($sql)->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);
		return array('providers' => $data);
	}

	/**
	 * getAvailableProviders
	 * @return array list of providers
	 */
	public function getAvailableProviders() {
		$sql = "select name from smsconnector_providers where api_key <> ''";
		return $this->Database->query($sql)->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * getList gets a list of numbers and their associations
	 * @return array 
	 */
	public function getList()
	{
		$sql = 'select rt.didid as id, p.name, u.username, rt.did from sms_routing as rt ' .
		'inner join userman_users as u on rt.uid = u.id inner join smsconnector_relations as r ' . 
		'on rt.didid = r.didid inner join smsconnector_providers as p on p.id = r.providerid WHERE ' . 
		'rt.adaptor = "Smsconnector"';
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
		$this->FreePBX->Sms->addDIDRouting($did, array($uid), 'Smsconnector');

		$sql = 'SELECT id FROM smsconnector_providers WHERE name = :name';
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':name', $name, \PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$providerid = $row['id'];

		$sql = "SELECT id FROM sms_dids WHERE did = :did";
		$sth = $this->Database->prepare($sql);
		$sth->execute(array(':did' => $did));
		$didid = $sth->fetchColumn();

		$sql = 'INSERT INTO smsconnector_relations (didid, providerid) VALUES (:didid, :providerid) ' .
				'ON DUPLICATE KEY UPDATE providerid = :providerid';
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':didid', $didid, \PDO::PARAM_INT);
		$stmt->bindParam(':providerid', $providerid, \PDO::PARAM_INT);
		$stmt->execute();

		return $this;

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
		$this->addNumber($uid, $did, $name);
		return $this;
	}
	/**
	 * deleteNumber Deletes the given number by didid
	 * @param  int $id      DID ID
	 * @return bool          Returns true on success or false on failure
	 */
	public function deleteNumber($id)
	{
		$sql = 'DELETE FROM smsconnector_relations WHERE didid = :id';
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();

		$sql = 'DELETE FROM sms_routing WHERE didid = :id';
		$stmt = $this->Database->prepare($sql);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();

		return $this;
	}

	/**
	 * updateProviders
	 * @param array hash of provider settings from form
	 * @return bool success or failure
	 */
	public function updateProviders($providers) {
		foreach ($providers as $provider => $creds) {
			$sql = 'UPDATE smsconnector_providers SET api_key = :key, api_secret = :secret WHERE name = :name';
			$stmt = $this->Database->prepare($sql);
			$stmt->bindParam(':key', $creds['api_key']);
			$stmt->bindParam(':secret', $creds['api_secret']);
			$stmt->bindParam(':name', $provider);
			$stmt->execute();
		}
		return $this;
	}

	/**
	 * getUsersWithDids
	 * @return array of user IDs associated with SMS DIDs
	 */
	public function getUsersWithDids() {
		$sql = 'SELECT uid FROM sms_routing';
		return $this->Database->query($sql)->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * This returns html to the main page
	 *
	 * @return string html
	 */
	public function showPage()
	{
		$subhead = _('Number List');
		$content = load_view(__DIR__ . '/views/grid.php');

		if ('form' == $_REQUEST['view']) {
			$subhead = _('Add Number');
			$content = load_view(__DIR__ . '/views/form.php');
			if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
				$subhead = _('Edit Number');
				$content = load_view(__DIR__ . '/views/form.php', $this->getOne($_REQUEST['id']));
			}
		}
		elseif ('settings' == $_REQUEST['view']) {
			$subhead = _('Provider Settings');
			$content = load_view(__DIR__ . '/views/settings.php', $this->getProviderSettings());
		}
		echo load_view(__DIR__ . '/views/default.php', array(
			'subhead' => $subhead,
			'content' => $content
		));
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
							'content' => sprintf('<p>%s<a href="/admin/config.php?display=smsconnector">%s</a></p>', _('Not yet implemented.'), _('Go to SMS Connector.')),
						)
					);
					break;
			}
		}
	}

	public function usermanAddUser($id, $display, $data) {}

	public function usermanUpdateUser($id, $display, $data) {}

	public function usermanDelUser($id, $display, $data) {}

}
