<?php

class bdPaygateInternalBanking_Listener
{
public static function load_class($class, array &$extend)
	{
		static $classes = array(
			'bdBank_ControllerPublic_Bank',
			'bdPaygate_Model_Processor',
		
			'XenForo_ControllerAdmin_UserUpgrade',
			'XenForo_Model_Option',
		);
		
		if (in_array($class, $classes))
		{
			$extend[] = 'bdPaygateInternalBanking_' . $class;
		}
	}
	
	public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
	{
		$hashes += bdPaygateInternalBanking_FileSums::getHashes();
	}
}