<?php

abstract class BaseSequenceGenerator
{
	//number of forks. F(n)=a(n-1) + n^2
	const MAX_FORK_AMOUNT = 8;
	protected $fpids = array();
	protected $masterPid = NULL;
	protected $placeAmount = NULL;
	protected $lengthOfSet = NULL;
	protected $exportManager = NULL;
	protected $maxValue = 0;
	protected $lengthOfReverseSet = NULL;

	/**
	*  Raise number into power
	**/
	abstract protected function getPow($number, $pow);

	abstract protected function numberToString($number);

	public function __construct($placeAmount)
	{
		$this->placeAmount = $placeAmount;
	}

	/**
	 * Set object to export result
	 * 
	 * @param ExportManagerInterface $exportManager 
	 * @return void
	 */
	public function setExportManager(ExportManagerInterface $exportManager)
	{
		$this->exportManager = $exportManager;
	}

	/**
	 * Calculate combinations n choose k
	 * 
	 * @return type
	 */
	public function getCombinationCount()
	{
		return gmp_fact($this->placeAmount)/((gmp_fact($this->placeAmount-$this->lengthOfSet) * gmp_fact($this->lengthOfSet)));
	}

	/**
	 * Initialize variables for generation
	 * @param integer $lengthOfSet 
	 * @return boolean
	 */
	protected function init($lengthOfSet)
	{
		$this->lengthOfSet = $lengthOfSet;
		
		if ($this->placeAmount <= $lengthOfSet) {
			throw new WrongArgumentException("It's n choose k combination algorithm. k should be less then n.");
		}

		if ($this->exportManager === NULL) {
			throw new WrongArgumentException("Export manager is not initialized");
		}

		$combinations = $this->getCombinationCount();
		if ($combinations < 10) {
			$this->exportManager->exportString('Less then 10 combinations');
			return false;
		}

		//Write to export manager count of combinations
		$this->exportManager->exportString($combinations);
		//Need it due multithreading
		$this->exportManager->flushCache();

		$this->lengthOfReverseSet = $this->placeAmount - $this->lengthOfSet;

		//calculate maximum value
		$this->maxValue = $this->getPow("2", $this->placeAmount) - 0b00000001;

		$this->masterPid = getmypid();

		return true;		
	}

	/**
	 * Process generation
	 * 
	 * @param integer $lengthOfSet 
	 * @return void
	 */
	public function process($lengthOfSet)
	{
		if ($this->init($lengthOfSet)) {
			//Launch recursive sequence generation
			$this->generateSequence($this->getInitValue(), 0, 1);

			foreach ($this->fpids as $pid) {
				pcntl_waitpid($pid, $status);
				unset($this->fpids[$pid]);
			}

			if ($this->masterPid <> getmypid()) {
				exit(0);
			}
		}
	}

	/**
	 * Recursive sequence generation
	 * 
	 * @param $number 
	 * @param integer $setBitsCount 
	 * @param integer $currentBit 
	 * @return boolean
	 */
	protected function generateSequence($number, $setBitsCount, $currentBit)
	{
		//if we don't get maximum position
		if ($number < $this->maxValue) {
			//There is no chance to get some result in this case
			if ($this->lengthOfReverseSet - $currentBit + $setBitsCount + 1 < 0) {
				return false;
			}

			$nextNumber = $number << 1;
			
			//fork process
			if ($currentBit <= self::MAX_FORK_AMOUNT/2) {
				$pid = pcntl_fork();
				if ($pid > 0) {
					$this->fpids[] = $pid;
				}
			}

			//if process was forked, branch a logic 
			if ($currentBit <= self::MAX_FORK_AMOUNT/2 && $pid == 0 || $currentBit > self::MAX_FORK_AMOUNT/2) {
				$this->generateSequence($nextNumber, $setBitsCount, $currentBit + 1);
			}

			//if process was forked, branch a logic
			if ($currentBit <= self::MAX_FORK_AMOUNT/2 && $pid > 0 || $currentBit > self::MAX_FORK_AMOUNT/2) {
				//If on next iteration we will get requered lenght, then stop it
				if ($setBitsCount + 1 == $this->lengthOfSet) {
					$this->processResult($nextNumber | 0b00000001, $currentBit + 1);
					return false;
				} else {					
					$this->generateSequence($nextNumber | 0b00000001, $setBitsCount + 1, $currentBit + 1);
				}
			}
		}

		


		return false;
	}

	/**
	 * Prepare data to export
	 * @param $number 
	 * @param integer $currentBit 
	 * @return void
	 */
	protected function processResult($number, $currentBit)
	{
		if ($this->placeAmount - $currentBit <> -1) {
			$number = $number << ($this->placeAmount - $currentBit + 1);
		}
		
		$this->exportString($number);
	}

	/**
	 * Export result to export manager
	 * 
	 * @param $data 
	 * @return void
	 */
	protected function exportString($data)
	{
		$this->exportManager->exportString(
			substr(
				$this->numberToString($data),
				1
			)
		);
	}

	/**
	 * Return initialize value for sequence generation
	 * 
	 * @return integer
	 */
	protected function getInitValue()
	{
		return 1;
	}
}