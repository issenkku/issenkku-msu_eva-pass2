<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function normalize_value(mixed $value): mixed
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d H:i:s');
    }

    return $value;
}

function export_table(string $table): array
{
    $rows = DB::table($table)->orderBy('id')->get();
    $out = [];

    foreach ($rows as $row) {
        $arr = (array) $row;
        foreach ($arr as $key => $value) {
            $arr[$key] = normalize_value($value);
        }
        $out[] = $arr;
    }

    return $out;
}

$tables = [
    'criteria_versions',
    'report_datas',
    'categories',
    'evaluation_lists',
    'quantity_main_criterias',
    'quantity_sub_criterias',
    'quality_main_criterias',
    'quality_sub_criterias',
    'formulas',
];

$rowsByTable = [];
foreach ($tables as $table) {
    if (! Schema::hasTable($table)) {
        continue;
    }
    $rows = export_table($table);
    if (count($rows) > 0) {
        $rowsByTable[$table] = $rows;
    }
}

$className = 'ReportStructureFromDbSeeder';
$path = __DIR__ . '/../database/seeders/' . $className . '.php';

$content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\DB;\n\nclass {$className} extends Seeder\n{\n    public function run(): void\n    {\n";

foreach ($rowsByTable as $table => $rows) {
    $export = var_export($rows, true);
    $content .= "        DB::table('{$table}')->insert({$export});\n\n";
}

$content .= "    }\n}\n";

file_put_contents($path, $content);

echo "Wrote: {$path}\n";