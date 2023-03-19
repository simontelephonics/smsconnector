<?php
namespace FreePBX\modules\Smsconnector;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$this->addDependency('sms');
		$configs = $this->dumpTables();
		$this->addConfigs($configs);
	}
}