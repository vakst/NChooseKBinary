<?php
class IntegerSequenceGenerator extends BaseSequenceGenerator
{

	/**
	 * Raise number into power
	 * 
	 * @param integer $number 
	 * @param integer $pow 
	 * @return integer
	 */
	protected function getPow($number, $pow)
	{
		return pow($number, $pow);
	}

	/**
	 * Convert number to string
	 * 
	 * @param integer $number 
	 * @return string
	 */
	protected function numberToString($number)
	{
		return decbin($number);
	}
}