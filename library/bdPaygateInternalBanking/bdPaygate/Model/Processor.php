<?php

class bdPaygateInternalBanking_bdPaygate_Model_Processor extends XFCP_bdPaygateInternalBanking_bdPaygate_Model_Processor
{
	public function getProcessorNames()
	{
		$names = parent::getProcessorNames();
		
		$names['bdbank'] = 'bdPaygateInternalBanking_Processor';
		
		return $names;
	}
}