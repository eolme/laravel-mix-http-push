<?php

namespace Eolme\MixPusher\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MixPusherCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mix-pusher:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache current mix';

    /**
     * Determines whether a string ends with the characters of a specified string
     * 
     * @return bool
     */
    protected function endWith($haystack, $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Caching...');

        $manifestPath = public_path('/mix-manifest.json');

        if (!file_exists($manifestPath)) {
            throw new Exception('The Mix manifest does not exist.');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        Cache::tags('mix-pusher')->flush();
        foreach ($manifest as $name => $link) {
            Cache::tags('mix-pusher')->forever($name, $link);
        }
    }
}
