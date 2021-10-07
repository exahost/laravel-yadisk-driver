<?php

namespace ITPolice\YandexDisk\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ITPolice\YandexDisk\Helper;

class YandexDiskMoveFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ya-disk:move-files';

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
        $files = Storage::disk('public')->allFiles();
        $excludeFiles = ['.gitignore'];
        foreach ($files as $file) {
            if(in_array($file, $excludeFiles)) continue;
            Helper::upload($file);
        }
    }
}
