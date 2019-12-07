<?php


namespace App\Command;


use EasySwoole\EasySwoole\Command\CommandInterface;

class InstallCommand implements CommandInterface
{
    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return  'install_srs';
    }

    public function exec(array $args): ?string
    {
        $setupZip=EASYSWOOLE_ROOT.'/storage/setup/srs2.0.zip';
        if (!file_exists($setupZip)) {throw new \Exception('没有找到压缩包');}
        echo "\e[32m 程序安装中,预计30分钟,请勿退出此界面 \e[0m \r\n";sleep(3);
        if (!unZip($setupZip,dirname($setupZip).'/srs')) {throw new \Exception('解压失败,请检查权限');}
        //安装
        exec('chmod -R 777 '.dirname($setupZip));
        $cmd='cd '.dirname($setupZip).'/srs && ./configure --full --prefix='.dirname($setupZip).'/srs  && make --jobs='.swoole_cpu_num();

        $process = proc_open($cmd, [0=>['pipe','r'], 1=>["pipe", "w"], 2=>["pipe", "w"]], $pipes, '/bin/bash');
        if(is_resource($process)){
            while($ret=fgets($pipes[1])){echo ''.$ret;}
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        //中途会退出 再次执行
        exec('cd '.dirname($setupZip).'/srs && make --jobs='.swoole_cpu_num(),$out);
        echo implode($out,"\n");
        return null;
    }

    public function help(array $args): ?string
    {
        return "安装SRS流媒体服务器";
    }

}