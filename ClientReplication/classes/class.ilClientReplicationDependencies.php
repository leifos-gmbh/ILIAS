<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dependency injection container
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilClientReplicationDependencies implements ArrayAccess
{
	protected $container = [];
	
	public function setDB(ilDBInterface $db)
	{
		$this->container['ilDB'] = $db;
	}
	
	/**
	 * @return ilDBInterface
	 */
	public function database()
	{
		return $this->container['ilDB'];
	}
	
	/**
	 * @return ilLanguage 
	 */
	public function language()
	{
		return ilLanguage::getGlobalInstance();
	}

	public function offsetExists($offset)
	{
		return in_array($offset, $this->container);
	}

	public function offsetGet($offset)
	{
		if($this->offsetExists($offset))
		{
			return $this->container[$offset];
		}
	}

	public function offsetSet($offset, $value)
	{
		$this->container[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		if($this->offsetExists($offset))
		{
			unset($this->container[$offset]);
		}
	}

}
?>