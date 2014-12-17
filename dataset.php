<?php

$tmpPath = __DIR__ . '/tmp';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}
$resultPath = __DIR__ . '/dataset';
if (!file_exists($resultPath)) {
    mkdir($resultPath, 0777, true);
}

$listFh = fopen(__DIR__ . '/list.csv', 'r');
fgets($listFh, 512);
$csvBaseUrl = 'http://data.fda.gov.tw/opendata/exportDataList.do?method=ExportData&logType=2&InfoId=';
$zip = new ZipArchive;
while ($line = fgetcsv($listFh, 2048)) {
    /*
     * Array
      (
      [0] => id
      [1] => title
      [2] => description
      [3] => url
      [4] => class
      [5] => provider
      [6] => columns
      [7] => frequency
      [8] => updated
      [9] => contact
      [10] => contact_phone
      )
     */
    $datasetFile = "{$resultPath}/{$line[0]}.csv";
    if (!file_exists($datasetFile)) {
        file_put_contents($datasetFile, file_get_contents($csvBaseUrl . $line[0]));
        exec("/usr/bin/dos2unix {$datasetFile}");
    }
    if (mime_content_type($datasetFile) === 'application/zip') {
        if ($zip->open($datasetFile) === true) {
            if ($zip->numFiles === 1) {
                echo $zip->getNameIndex(0) . "\n";
                copy("zip://" . $datasetFile . "#" . $zip->getNameIndex(0), $tmpPath . '/' . $line[0] . '.csv');
                if (file_exists($tmpPath . '/' . $line[0] . '.csv')) {
                    copy($tmpPath . '/' . $line[0] . '.csv', $datasetFile);
                    exec("/usr/bin/dos2unix {$datasetFile}");
                }
            }
            $zip->close();
        }
    }
}