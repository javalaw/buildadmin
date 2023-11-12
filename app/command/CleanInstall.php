<?php

declare(strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class CleanInstall extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('clean:install')
            ->setDescription('clean the buildadmin install.');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $publicPath = public_path();
        $installLock = $publicPath . 'install.lock';
        $indexHtml = $publicPath . 'index.html';
        if (file_exists($installLock)) {
            unlink($installLock);
        }
        if (file_exists($indexHtml)) {
            unlink($indexHtml);
        }
        $rootDir = root_path();
        $backupDir = root_path('backup');

        $this->copyDirectory($backupDir, $rootDir);

        $this->output->info('Backup restored successfully.');
    }

    protected function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;

                if (is_dir($srcFile)) {
                    $this->copyDirectory($srcFile, $dstFile);
                } else {
                    copy($srcFile, $dstFile);
                }
            }
        }

        closedir($dir);
    }
}
