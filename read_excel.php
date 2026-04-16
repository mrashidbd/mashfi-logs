<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class TestImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->count() > 0) {
            echo "Heading Row Keys:\n";
            print_r(array_keys($rows[0]->toArray()));
        }
    }
}

Excel::import(new TestImport, __DIR__.'/public/dataset-new.xlsx');
