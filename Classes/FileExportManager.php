<?php
class FileExportManager implements ExportManagerInterface {
	const INMEMORY_CHACHED_STRING_COUNT = 10000;
	//protected $fp;
	protected $filename;
	protected $cachedStringCount = 0;
	protected $data = '';
	protected $fpList = array();

	public function __construct($filename)
	{
		//unlink file before processes started
		if (file_exists($filename)) {
			unlink($filename);
		}

		$this->filename = $filename;
		$this->stringCount = 0;
		$this->data = '';
	}

	public function __destruct()
	{
		$this->flushCache();
	}

	/**
	 * Save data in cache
	 * 
	 * @param string $data 
	 * @return void
	 */
	public function exportString($data)
	{
		$this->data .= $data."\n";
		if (++$this->cachedStringCount == self::INMEMORY_CHACHED_STRING_COUNT) {
			$this->flushCache();
		}		
	}

	/**
	 * Flush data to file
	 * 
	 * @return void
	 */
	public function flushCache()
	{
		fwrite($this->getCurrentFp(), $this->data);
		$this->cachedStringCount = 0;
		$this->data = '';
    	fflush($this->getCurrentFp());
	}

	/**
	 * Return current opened pointer on file
	 * @return resource
	 */
	public function getCurrentFp()
	{
		if (empty($this->fpList[getmypid()])) {
			$this->fpList[getmypid()] = fopen($this->filename, "a");
		}
		return $this->fpList[getmypid()];
	}
}