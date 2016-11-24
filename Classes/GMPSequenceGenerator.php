<?php
class GMPSequenceGenerator extends BaseSequenceGenerator
{
	/**
	 * Raise number into power
	 * 
	 * @param GMP $number 
	 * @param integer $pow 
	 * @return GMP
	 */
	protected function getPow($number, $pow)
	{
		return gmp_pow($number, $pow);
	}

	/**
	 * Convert GMP to string
	 * 
	 * @param GMP $number 
	 * @return string
	 */
	protected function numberToString($number)
	{
		return gmp_strval($number, 2);
	}

	/**
	 * Return initialize value for sequence generation
	 * 
	 * @return GMP
	 */
	protected function getInitValue()
	{
		return gmp_init(parent::getInitValue());
	}
}