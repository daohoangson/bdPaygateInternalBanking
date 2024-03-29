<?php

class bdPaygateInternalBanking_XenForo_Model_Option extends XFCP_bdPaygateInternalBanking_XenForo_Model_Option
{
	// this property must be static because XenForo_ControllerAdmin_UserUpgrade::actionIndex
	// for no apparent reason use XenForo_Model::create to create the optionModel
	// (instead of using XenForo_Controller::getModelFromCache)
	private static $_bdPaygateInternalBanking_hijackOptions = false;
	
	public function getOptionsByIds(array $optionIds, array $fetchOptions = array())
	{
		if (self::$_bdPaygateInternalBanking_hijackOptions === true)
		{
			$optionIds[] = 'bdPaygateInternalBanking_rates';
		}
		
		$options = parent::getOptionsByIds($optionIds, $fetchOptions);
		
		self::$_bdPaygateInternalBanking_hijackOptions = false;

		return $options;
	}
	
	public function bdPaygateInternalBanking_hijackOptions()
	{
		self::$_bdPaygateInternalBanking_hijackOptions = true;
	}
}