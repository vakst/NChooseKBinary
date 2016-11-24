<?php

interface ExportManagerInterface {
	public function exportString($data);
	public function flushCache();
}