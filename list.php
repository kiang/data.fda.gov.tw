<?php

$tmpPath = __DIR__ . '/tmp';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}

$result = array();
$pageTotal = 1;
$recordCount = 1;
$dataset = array();
for ($i = 1; $i <= $pageTotal; $i ++) {

    if ($i === 1) {
        $listUrl = 'http://data.fda.gov.tw/frontsite/data/DataAction.do?method=doList';
        $listFile = $tmpPath . '/list_' . md5($listUrl);
        if (!file_exists($listFile)) {
            file_put_contents($listFile, file_get_contents($listUrl));
        }
        $listContent = file_get_contents($listFile);
        $pos = strpos($listContent, 'var pageCount = ');
        if (false === $pos) {
            echo 'pageCount not found';
            exit();
        }
        $pos += 16;
        $pageTotal = intval(substr($listContent, $pos, strpos($listContent, ';', $pos) - $pos));
        $pos = strpos($listContent, 'var c = ');
        if (false === $pos) {
            echo 'c not found';
            exit();
        }
        $pos += 8;
        $recordCount = intval(substr($listContent, $pos, strpos($listContent, ';', $pos) - $pos));
    } else {
        $listUrl = "http://data.fda.gov.tw/frontsite/data/DataAction.do?recordCount={$recordCount}&maxRecord=10&method=doList&currentPage={$i}";
        $listFile = $tmpPath . '/list_' . md5($listUrl);
        if (!file_exists($listFile)) {
            file_put_contents($listFile, file_get_contents($listUrl));
        }
        $listContent = file_get_contents($listFile);
    }

    $pos = strpos($listContent, '<div class="dataset">');
    while (false !== $pos) {
        $posEnd = strpos($listContent, '<div class="dataset">', $pos + 1);
        if (false === $posEnd) {
            $posEnd = strpos($listContent, '<div align="center">', $pos + 1);
        }
        $item = substr($listContent, $pos, $posEnd - $pos);
        $item = preg_split('/\\<\\/h3\\>|\\<\\/div\\>/', $item);
        if (count($item) === 6) {
            $idPos = strpos($item[1], '&infoId=') + 8;
            $item[0] = substr($item[1], $idPos, strpos($item[1], '"', $idPos) - $idPos);
            $dataset[] = array(
                'id' => $item[0],
                'title' => trim(strip_tags($item[1])),
                'description' => trim(strip_tags($item[2])),
                'url' => 'http://data.fda.gov.tw/frontsite/data/DataAction.do?method=doDetail&infoId=' . $item[0],
            );
        }
        $pos = strpos($listContent, '<div class="dataset">', $posEnd);
    }
}
usort($dataset, "cmp");
$listFh = fopen(__DIR__ . '/list.csv', 'w');
$headersWritten = false;
foreach ($dataset AS $data) {
    $infoFile = $tmpPath . '/info_' . md5($data['url']);
    if (!file_exists($infoFile)) {
        file_put_contents($infoFile, file_get_contents($data['url']));
    }
    $info = file_get_contents($infoFile);
    $pos = strpos($info, '<div class="data_class">');
    $pos = strpos($info, '<span>', $pos) + 6;
    $data['class'] = substr($info, $pos, strpos($info, '</span>', $pos) - $pos);
    $pos = strpos($info, '<div class="data_prov">');
    $pos = strpos($info, '<span>', $pos) + 6;
    $data['provider'] = substr($info, $pos, strpos($info, '</span>', $pos) - $pos);
    $pos = strpos($info, '<table border="0" cellpadding="0" cellspacing="0" class="table_Info">');
    $table = substr($info, $pos, strpos($info, '</table>', $pos) - $pos);
    $rows = explode('</tr>', $table);
    $rows[0] = explode('<td>', $rows[0]);
    $data['columns'] = trim(strip_tags($rows[0][1]));
    $rows[1] = explode('<td>', $rows[1]);
    $data['frequency'] = trim(strip_tags($rows[1][1]));
    $rows[2] = explode('<td>', $rows[2]);
    $data['updated'] = trim(strip_tags($rows[2][1]));
    $rows[4] = explode('<td>', $rows[4]);
    $data['contact'] = trim(strip_tags($rows[4][1]));
    $rows[5] = explode('<td>', $rows[5]);
    $data['contact_phone'] = trim(strip_tags($rows[5][1]));
    if (false === $headersWritten) {
        fputcsv($listFh, array_keys($data));
        $headersWritten = true;
    }
    fputcsv($listFh, $data);
}

function cmp($a, $b) {
    if ($a['id'] == $b['id']) {
        return 0;
    }
    return ($a['id'] < $b['id']) ? -1 : 1;
}
