<?php
require 'C:/Users/ahmed/shadi/vendor/autoload.php';
$r = new ReflectionClass('Filament\Resources\Pages\CreateRecord');
foreach ($r->getMethods() as $m) {
    echo $m->getName() . PHP_EOL;
}
echo "---\n";
$r2 = new ReflectionClass('Filament\Resources\Pages\EditRecord');
foreach ($r2->getMethods() as $m) {
    echo $m->getName() . PHP_EOL;
}
