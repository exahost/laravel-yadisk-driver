<?php

namespace ITPolice\YandexDisk\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ITPolice\YandexDisk\Helper;

class YandexDiskClearTmpFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ya-disk:clear-tmp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move files from local disk to yandex disk';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = realpath(__DIR__.'/../../');
        $cmd = '(find '.$path.' -mount -type f -not -name "*.php" -mtime +1  -exec rm -rf {} \;) > /dev/null 2>&1 &';
        shell_exec($cmd);
    }
}
