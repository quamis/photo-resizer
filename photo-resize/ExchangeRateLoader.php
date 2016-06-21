<?php
class ExchangeRateLoader 
{
	private $groupedData = Array();
	private $definedCurrencies = Array();
	private $definedBanks = Array();
	
	public function loadJson($fullXchData )
	{
		$groupedData = Array();
		$definedCurrencies = Array();
		$definedBanks = Array();
		foreach($fullXchData as $k=>$xch) {
			#$dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $xch->time);	// this seems to be really slow
			#$ymd = $dt->format('Y-m-d');
			#$ymdhis = $dt->format('Y-m-d H:i:s');
			
			$ymd = substr($xch->time, 0, 10);
			$ymdhis = substr($xch->time, 0, 10) . " " . substr($xch->time, 11);

			if (!isset($definedCurrencies[$xch->currency])) {
				$definedCurrencies[$xch->currency] = 0;
			}
			$definedCurrencies[$xch->currency]++;
			
			if (!isset($definedBanks[$xch->bank])) {
				$definedBanks[$xch->bank] = 0;
			}
			$definedBanks[$xch->bank]++;
			
			if (!isset($groupedData[$ymdhis])) {
				$groupedData[$ymdhis] = Array();
			}
			if (!isset($groupedData[$ymdhis][$xch->type])) {
				$groupedData[$ymdhis][$xch->type] = Array();
			}
			if (!isset($groupedData[$ymdhis][$xch->type][$xch->currency])) {
				$groupedData[$ymdhis][$xch->type][$xch->currency] = Array();
			}
			if (!isset($groupedData[$ymdhis][$xch->type][$xch->currency][$xch->bank])) {
				$groupedData[$ymdhis][$xch->type][$xch->currency][$xch->bank] = Array();
			}
			
			$groupedData[$ymdhis][$xch->type][$xch->currency][$xch->bank] = $xch->value;
		}
		
		$this->groupedData = $groupedData;
		$this->definedCurrencies = array_keys($definedCurrencies);
		$this->definedBanks = array_keys($definedBanks);
		
		sort($this->definedCurrencies);
		sort($this->definedBanks);
	}
	
	public function getRawGroupedData() 
	{
		return $this->groupedData;
	}
	
	public function getKeys() 
	{
		return array_keys($this->groupedData);
	}
	
	public function getDates() 
	{
		$fcn = function($value) { return \DateTime::createFromFormat("Y-m-d H:i:s", $value)->format("Y-m-d"); };
		return array_unique(array_map($fcn, array_keys($this->groupedData)));
	}
	
	public function getUpdatesOnDate($date)
	{
		return array_filter( array_keys($this->groupedData), create_function('$value', "return \DateTime::createFromFormat('Y-m-d H:i:s', \$value)->format('Y-m-d')=='{$date}';"));
	}
	
	public function getUpdatesOn($datetime)
	{
		return $this->groupedData[$datetime];
	}
	
	public function getCompleteUpdatesOn($datetime)
	{
		$root = $this->groupedData[$datetime];
		
		$return = Array();
		
		$gatheredCurrencies = array();
		$gatheredBanks = array();
		
		foreach($root as $operation=>$currencies) {
			foreach($currencies as $currency=>$banks) {
				foreach($banks as $bank=>$value) {
					if(!isset($return[$operation])) {
						$return[$operation] = Array();
					}
					if(!isset($return[$operation][$currency])) {
						$return[$operation][$currency] = Array();
					}
					if(!isset($return[$operation][$currency][$bank])) {
						$return[$operation][$currency][$bank] = Array();
					}
					
					if(!isset($gatheredCurrencies[$currency])) {
						$gatheredCurrencies[$currency] = 0;
					}
					if(!isset($gatheredBanks[$bank])) {
						$gatheredBanks[$bank] = Array();
					}
					if(!isset($gatheredBanks[$bank][$currency])) {
						$gatheredBanks[$bank][$currency] = 0;
					}
					$gatheredCurrencies[$currency]++;
					$gatheredBanks[$bank][$currency]++;
					
					$return[$operation][$currency][$bank] = Array(
						'value'=>$value,
						'key'=>$datetime,
					);
				}
			}
		}
		
		$keys = array_keys($this->groupedData);
		$keyIdx = array_search($datetime, $keys);
		
		$break = false;
		for ($idx=$keyIdx; $idx>=0; $idx--) {
			$key = $keys[$idx];
			$data = $this->groupedData[$key];
			
			foreach($data as $operation=>$currencies) {
				foreach($currencies as $currency=>$banks) {
					foreach($banks as $bank=>$value) {
						if(!isset($return[$operation])) {
							$return[$operation] = Array();
						}
						if(!isset($return[$operation][$currency])) {
							$return[$operation][$currency] = Array();
						}
						if(!isset($return[$operation][$currency][$bank])) {
							$return[$operation][$currency][$bank] = Array();
						}
					
						if(!isset($gatheredCurrencies[$currency])) {
							$gatheredCurrencies[$currency] = 0;
						}
						if(!isset($gatheredBanks[$bank])) {
							$gatheredBanks[$bank] = Array();
						}
						if(!isset($gatheredBanks[$bank][$currency])) {
							$gatheredBanks[$bank][$currency] = 0;
						}
						$gatheredCurrencies[$currency]++;
						$gatheredBanks[$bank][$currency]++;
						
						if(!$return[$operation][$currency][$bank]) {
							$return[$operation][$currency][$bank] = Array(
								'value'=>$value,
								'key'=>$key,
							);
						}
					}
				}
			}
			
			if((count($this->definedCurrencies) == count($gatheredCurrencies)) && (count($this->definedBanks) == count(array_keys($gatheredBanks)))) {
				$allFound = true;
				foreach($gatheredBanks as $b=>$c) {
					if(count($this->definedCurrencies)!=count($c)) {
						$allFound = false;
						break;
					}
				}
				
				if ($allFound) {
					$break = true;
					break;
				}
			}
		}
		
		return $return;
	}
}
