<?php

$limit = 838860800;
exec('git status ' . __DIR__, $lines);
$lineCount = $currentFileSize = $pushCount = 0;
$pushCount = 78;
$sw = false;
foreach ($lines AS $line) {
    if ($sw) {
        ++$lineCount;
        if ($lineCount > 2) {
            if (!empty($line)) {
                $line = trim($line);
                $path = __DIR__ . '/' . $line;

                if (is_file($path)) {
                    addFile($path);
                } else {
                    foreach (glob($path . '/*') AS $file) {
                        addFile($file);
                    }
                }
            } else {
                $sw = false;
            }
        }
    } elseif ($line === 'Untracked files:') {
        $sw = true;
    }
}

if ($currentFileSize > 0) {
    ++$pushCount;
    exec("git commit -m 'batch push files part {$pushCount}'");
    exec("git push");
}

function addFile($file) {
    global $limit, $currentFileSize, $pushCount;
    $currentFileSize += filesize($file);
    echo "size: {$currentFileSize} / {$limit}\n";
    if ($currentFileSize < $limit) {
        exec("git add {$file}");
    } else {
        exec("git add {$file}");
        ++$pushCount;
        exec("git commit -m 'batch push files part {$pushCount}'");
        exec("git push");
        $currentFileSize = 0;
    }
}
