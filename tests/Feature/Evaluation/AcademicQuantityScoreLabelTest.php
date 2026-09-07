<?php

test('academic reviewer quantity score labels match their A B C D fields', function (string $component) {
    $source = file_get_contents(resource_path("views/components/{$component}.blade.php"));

    expect($source)
        ->toMatch('/ค่าน้ำหนักคะแนน \(A\).*?name="quantity_list\[\{\{ \$subCriteria\[\'id\'\] \}\}\]\[score_A\]"/s')
        ->toMatch('/หน่วยภาระงานมาตรฐาน \(B\).*?name="quantity_list\[\{\{ \$subCriteria\[\'id\'\] \}\}\]\[score_B\]"/s')
        ->toMatch('/หน่วยภาระงานที่ทำได้ \(C\).*?name="quantity_list\[\{\{ \$subCriteria\[\'id\'\] \}\}\]\[score_C\]"/s')
        ->toMatch('/คะแนนที่คำนวณได้ \(D\).*?name="quantity_list\[\{\{ \$subCriteria\[\'id\'\] \}\}\]\[score_D\]"/s');
})->with(['unified-evaluator', 'unified-director']);
