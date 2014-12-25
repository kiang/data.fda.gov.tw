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
                        if (is_file($file)) {
                            addFile($file);
                        } else {
                            foreach (glob($file . '/*') AS $subFile) {
                                addFile($subFile);
                            }
                        }
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

$fileCount = 0;

function addFile($file) {
    global $limit, $currentFileSize, $pushCount, $fileCount;
    if (!is_file($file)) {
        echo "{$file} is not file!!\n";
        exit();
    }
    ++$fileCount;
    $fileSize = filesize($file);
    $currentFileSize += $fileSize;
    $part = substr($file, -51);
    echo "[{$fileCount}] {$currentFileSize} - {$fileSize} - {$part}\n";
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
