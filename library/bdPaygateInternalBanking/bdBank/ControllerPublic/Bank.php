<?php

class bdPaygateInternalBanking_bdBank_ControllerPublic_Bank extends XFCP_bdPaygateInternalBanking_bdBank_ControllerPublic_Bank
{
	public function actionBdpaygate()
	{
		$formData = $this->_input->filter(array(
			'amount' => XenForo_Input::UINT,
			'comment' => XenForo_Input::STRING,
			'_bdPaygate_itemId' => XenForo_Input::STRING,
		));
		
		$visitor = XenForo_Visitor::getInstance();
		$personal = bdBank_Model_Bank::getInstance()->personal();
		$transfered = false;
		$transactionId = '';
		$exception = false;
		
		try
		{
			$transfered = $personal->transfer(
				$visitor['user_id'], 0,
				$formData['amount'], $formData['comment']
			);
			
			if (!empty($transfered['transaction_id']))
			{
				// old version of [bd] Banking doesn't emit transaction id
				// so we have to do a quick check to make sure everything 
				// works together
				$transactionId = 'bdbank_' . $transfered['transaction_id'];
			}
		}
		catch (bdBank_Exception $be)
		{
			$exception = $be;
		}
		
		if (empty($exception))
		{
			$processor = bdPaygate_Processor_Abstract::create('bdPaygateInternalBanking_Processor');
			$logType = bdPaygate_Processor_Abstract::PAYMENT_STATUS_ACCEPTED;
			$logMessage = $processor->processTransaction($logType, $formData['_bdPaygate_itemId']);
			$logDetails = $formData;
		}
		else 
		{
			$logType = 'error';
			$logMessage = $exception->getMessage();
			$logDetails = $formData;
		}
		
		// do proper logging for later analysis
		$this->getModelFromCache('bdPaygate_Model_Processor')->log('bdbanking', $transactionId, $logType, $logMessage, $logDetails);
		
		if (empty($exception))
		{
			$fallbackUrl = XenForo_Link::buildPublicLink("full:" . bdBank_Model_Bank::routePrefix() . "/history");
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect($fallbackUrl),
				new XenForo_Phrase('bdpaygateinternalbanking_payment_x_accepted', array(
					'amount' => XenForo_Template_Helper_Core::callHelper('bdbank_balanceformat', array($formData['amount']))
				))
			);
		}
		else 
		{
			return $this->responseError(
				new XenForo_Phrase('bdpaygateinternalbanking_error_x', array('error' => $exception->getMessage()))
			);
		}
	}
}