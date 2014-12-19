<?php

$resultPath = __DIR__ . '/39_file';
$prefix = array(
    '衛署藥製' => '01',
    '衛署藥輸' => '02',
    '衛署成製' => '03',
    '衛署中藥輸' => '04',
    '衛署醫器製' => '05',
    '衛署醫器輸' => '06',
    '衛署粧製' => '07',
    '衛署粧輸' => '08',
    '衛署菌疫製' => '09',
    '衛署菌疫輸' => '10',
    '衛署色輸' => '11',
    '內衛藥製' => '12',
    '內衛藥輸' => '13',
    '內衛成製' => '14',
    '內衛菌疫製' => '15',
    '內衛菌疫輸' => '16',
    '內藥登' => '17',
    '署藥兼食製' => '18',
    '衛署成輸' => '19',
    '衛署罕藥輸' => '20',
    '衛署罕藥製' => '21',
    '罕菌疫輸' => '22',
    '罕菌疫製' => '23',
    '罕醫器輸' => '24',
    '罕醫器製' => '25',
    '衛署色製' => '31',
    '衛署粧陸輸' => '40',
    '衛署藥陸輸' => '41',
    '衛署醫器陸輸' => '42',
    '衛署醫器製壹' => '43',
    '衛署醫器輸壹' => '44',
    '衛署醫器外製' => '45',
    '衛署醫器陸輸壹' => '46',
    '衛署醫器外製壹' => '47',
    '衛部藥製' => '51',
    '衛部藥輸' => '52',
    '衛部成製' => '53',
    '衛部醫器製' => '55',
    '衛部醫器輸' => '56',
    '衛部粧製' => '57',
    '衛部粧輸' => '58',
    '衛部菌疫製' => '59',
    '衛部菌疫輸' => '60',
    '衛部色輸' => '61',
    '部藥兼食製' => '68',
    '衛部成輸' => '69',
    '衛部罕藥輸' => '70',
    '衛部罕藥製' => '71',
    '衛部罕菌疫輸' => '72',
    '衛部罕菌疫製' => '73',
    '衛部罕醫器輸' => '74',
    '衛部色製' => '81',
    '衛部粧陸輸' => '90',
    '衛部藥陸輸' => '91',
    '衛部醫器陸輸' => '92',
    '衛部醫器製壹' => '93',
    '衛部醫器輸壹' => '94',
    '衛部醫器外製' => '95',
    '衛部醫器陸輸壹' => '96',
    '衛部醫器外製壹' => '97',
    '衛署菌製' => '99',
);

/*
 * Array
  (
  [0] => 許可證字號
  [1] => 中文品名
  [2] => 英文品名
  [3] => 仿單圖檔連結
  [4] => 外盒圖檔連結
  [5] => -
  )
 */
$dirLen = strlen(__DIR__) + 1;
$oFh = fopen(__DIR__ . '/39_file.csv', 'w');
fputcsv($oFh, array('id', 'file', 'url', 'mime', 'size'));
$fh = fopen(__DIR__ . '/dataset/39.csv', 'r');
while ($line = fgetcsv($fh, 2000, "\t")) {
    if (!empty($line[11])) {
        echo "processing {$line[0]}\n";
        $parts = explode('字第', $line[0]);
        $parts[1] = substr($parts[1], 0, strpos($parts[1], '號'));
        if (isset($prefix[$parts[0]])) {
            $files = explode(';;', $line[11]);
            foreach ($files AS $k => $v) {
                $files[$k] = trim($v);
                if (empty($files[$k])) {
                    unset($files[$k]);
                }
            }
            $targetFolder = "{$resultPath}/{$prefix[$parts[0]]}/{$parts[1]}";
            if (!file_exists($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }
            if (count($files) > 1) {
                foreach ($files AS $url) {
                    $targetFile = $targetFolder . '/' . md5($url);
                    if (!file_exists($targetFile)) {
                        file_put_contents($targetFile, file_get_contents($url));
                    }
                    fputcsv($oFh, array(
                        $line[0],
                        substr($targetFile, $dirLen),
                        $url,
                        mime_content_type($targetFile),
                        filesize($targetFile),
                    ));
                }
            } elseif (isset($dbKeys[$line[0]]) && file_exists("/home/kiang/public_html/drug_images/{$dbKeys[$line[0]]}")) {
                $url = $files[key($files)];
                $targetFile = $targetFolder . '/' . md5($url);
                if (!file_exists($targetFile)) {
                    exec("mv /home/kiang/public_html/drug_images/{$dbKeys[$line[0]]} {$targetFile}");
                }
                fputcsv($oFh, array(
                    $line[0],
                    substr($targetFile, $dirLen),
                    $url,
                    mime_content_type($targetFile),
                    filesize($targetFile),
                ));
            } else {
                $url = $files[key($files)];
                $targetFile = $targetFolder . '/' . md5($url);
                if (!file_exists($targetFile)) {
                    file_put_contents($targetFile, file_get_contents($url));
                }
                fputcsv($oFh, array(
                    $line[0],
                    substr($targetFile, $dirLen),
                    $url,
                    mime_content_type($targetFile),
                    filesize($targetFile),
                ));
            }
        }
    }
}