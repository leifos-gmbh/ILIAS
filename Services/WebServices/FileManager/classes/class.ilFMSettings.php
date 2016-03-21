<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * File Manager settings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilFMSettings
{
	const MODE_DEFAULT = 0;
	const MODE_EXPLORER = 1;
	
	private static $instance = null;

	private $storage = null;
	private $enabled = false;
	private $localFS = true;
	private $maxFileSize = 64;
	private $mode = self::MODE_EXPLORER;
    
	/**
	 * Singleton constructor
	 */
	private function __construct()
	{
		$this->storage = new ilSetting('fm');
		$this->read();
	}

	/**
	 * Get singleton instance
	 * @return ilFMSettings
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilFMSettings();
	}

	/**
	 * Get storage
	 * @return ilSetting
	 */
	protected function getStorage()
	{
		return $this->storage;
	}

	/**
	 * Enable file manager
	 * @param bool $a_status
	 */
	public function enable($a_status)
	{
		$this->enabled = $a_status;
	}

	/**
	 * check if enabled
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * Enable local file system frame
	 * @param <type> $a_stat
	 */
	public function enableLocalFS($a_stat)
	{
		$this->localFS = $a_stat;
	}

	/**
	 * Check if local file system frame is enabled by default
	 * @return <type>
	 */
	public function isLocalFSEnabled()
	{
		return $this->localFS;
	}
	
	/**
	 * Get file manager mode
	 * @return type 
	 */
	public function getMode()
	{
		return $this->mode;
	}
	
	/**
	 * Set file manager mode
	 * @param type $a_mode 
	 */
	public function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	public function setMaxFileSize($a_size)
	{
		$this->maxFileSize = $a_size;
	}
	
	public function getMaxFileSize()
	{
		return $this->maxFileSize;
	}
	

	/**
	 * Update settings
	 */
	public function update()
	{
		$this->getStorage()->set('enabled',(int) $this->isEnabled());
		$this->getStorage()->set('local',(int) $this->isLocalFSEnabled());
		$this->getStorage()->set('maxFileSize', (int) $this->getMaxFileSize());
		$this->getStorage()->set('mode',(int) $this->getMode());
	}

	/**
	 * Read settings
	 */
	protected function read()
	{
		$this->enable($this->getStorage()->get('enabled', $this->enabled));
		$this->enableLocalFS($this->getStorage()->get('local'), $this->localFS);
		$this->setMaxFileSize($this->getStorage()->get('maxFileSize',$this->maxFileSize));
		$this->setMode($this->getStorage()->get('mode'),$this->mode);
	}
}
?>
