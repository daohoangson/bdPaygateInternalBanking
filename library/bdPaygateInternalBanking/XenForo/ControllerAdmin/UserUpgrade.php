<?php

class bdPaygateInternalBanking_XenForo_ControllerAdmin_UserUpgrade extends XFCP_bdPaygateInternalBanking_XenForo_ControllerAdmin_UserUpgrade
{
	public function actionIndex()
	{
		$optionModel = $this->getModelFromCache('XenForo_Model_Option');
		$optionModel->bdPaygateInternalBanking_hijackOptions();
		
		return parent::actionIndex();
	}
}