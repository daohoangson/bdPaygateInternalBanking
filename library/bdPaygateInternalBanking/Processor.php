<?php

class bdPaygateInternalBanking_Processor extends bdPaygate_Processor_Abstract
{
	public function isAvailable()
	{
		return class_exists('bdBank_Model_Bank');
	}
	
	public function getSupportedCurrencies()
	{
		return array(
			bdPaygate_Processor_Abstract::CURRENCY_USD,
			bdPaygate_Processor_Abstract::CURRENCY_CAD,
			bdPaygate_Processor_Abstract::CURRENCY_AUD,
			bdPaygate_Processor_Abstract::CURRENCY_GBP,
			bdPaygate_Processor_Abstract::CURRENCY_EUR,
		);
	}
	
	public function isRecurringSupported()
	{
		return false;
	}
	
	public function validateCallback(Zend_Controller_Request_Http $request, &$transactionId, &$paymentStatus, &$transactionDetails, &$itemId)
	{
		// [bd] Banking doesn't support callbacks
		return false;
	}
	
	public function generateFormData($amount, $currency, $itemName, $itemId, $recurringInterval = false, $recurringUnit = false, array $extraData = array())
	{
		$this->_assertAmount($amount);
		$this->_assertCurrency($currency);
		$this->_assertItem($itemName, $itemId);
		$this->_assertRecurring($recurringInterval, $recurringUnit);
		
		// calculate amount in internal [bd] Banking currency
		$currencyRate = 0;
		$rates = explode("\n", XenForo_Application::getOptions()->get('bdPaygateInternalBanking_rates'));
		foreach ($rates as $rate) {
			$parts = explode('=', trim($rate));
			if (count($parts) == 2) {
				if (utf8_strtolower(utf8_trim($parts[0])) == $currency) {
					$currencyRate = doubleval($parts[1]);
				}
			}
		}
		$amountInternal = $amount * $currencyRate;
		
		$formAction = XenForo_Link::buildPublicLink("full:" . bdBank_Model_Bank::routePrefix() . "/bdpaygate");
		$callToAction = new XenForo_Phrase('bdpaygateinternalbanking_call_to_action',
			array(
				'money' => new XenForo_Phrase('bdbank_money'),
				'amount' => XenForo_Template_Helper_Core::callHelper('bdbank_balanceformat', array($amountInternal))
			)
		);
		$csrfToken = XenForo_Visitor::getInstance()->get('csrf_token_page');
		
		$form = <<<EOF
<form action="{$formAction}" method="POST" class="AutoValidator" data-redirect="yes">
	<input type="hidden" name="amount" value="{$amountInternal}" />
	<input type="hidden" name="comment" value="{$itemName}" />
	<input type="hidden" name="_bdPaygate_itemId" value="{$itemId}" />
	<input type="submit" value="{$callToAction}" class="button" />
	
	<input type="hidden" name="_xfToken" value="{$csrfToken}">
</form>
EOF;
		
		return $form;
	}
}