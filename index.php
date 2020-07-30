<?php

require 'vendor/autoload.php';

use Therealsmat\data\Months;

$constantsSourceToDestination = [
    Months::class => 'js/'
];

$constantsExporter = new \Therealsmat\ConstantsExporter();

$constantsExporter->setConstantsToExport(
    $constantsSourceToDestination
);

$constantsExporter->perform();