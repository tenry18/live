<?php

/**
 * @param string $app
 * @param string $stream_id
 * @return string
 */
function stream_key(string $app,string $stream_id){
    return md5($app.$stream_id);
}

/**解压缩
 * @param $filepath
 * @param $extractTo
 * @return bool
 */
function unZip($filepath,$extractTo) {
    $zip = new ZipArchive();
    $res = $zip->open($filepath);
    if ($res === TRUE) {
        //解压缩到$extractTo指定的文件夹
        $zip->extractTo($extractTo);
        $zip->close();
        return true;
    } else {
        echo 'failed, code:' . $res;
        return false;
    }
}