<?php
spl_autoload_register(function ($class_name) {
    include __DIR__.'/Classes/'.$class_name . '.php';
});

if (count($argv) <> 3) {
	echo "Wrong launch arguments. Use php <scriptName> <N> <M>\n";
	exit();
}
$placeAmount = $argv[1];
$lengthOfSet = $argv[2];


try {
	$sequenceGenerator = SequenceGeneratorFactory::create($placeAmount);
	$sequenceGenerator->setExportManager(new FileExportManager('sequence.txt'));
	$sequenceGenerator->process($lengthOfSet);	
} catch (WrongArgumentException $e) {
	echo "Error: ".$e->getMessage()."\n";
}