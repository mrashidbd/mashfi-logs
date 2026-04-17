<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class FinalImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->count() > 0) {
            echo 'Sheet has '.$rows->count()." rows\n";
            echo "Heading Row Keys:\n";
            print_r(array_keys($rows[0]->toArray()));
            echo "\nFirst row values:\n";
            print_r($rows[0]->toArray());
        }
    }
}

Excel::import(new FinalImport, __DIR__.'/public/Final-Dataset.xlsx');
