<?php

namespace LaravelStream\Redis\Commands;

use Illuminate\Console\Command;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\View;
use Symfony\Component\Console\Application;

class MakeStreamChannelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:channel-listener {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create redis channel listener';
    /**
     * @var Application|null
     */
    private $app;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->app = app();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var Factory $view */
        $view = $this->createLocalViewFactory();
        $path = app_path('Channels');
        if (!file_exists($path)) {
            if (!mkdir($path)) {
                $this->warn('no write permission on "'.app_path().'"');
                return 1;
            }
        }

        $name = $this->argument('name');

        $html = $view->make('make_stream_channel')
            ->with('name', $name)
            ->render();

        if (file_exists("{$path}/{$name}.php")) {
            $this->warn('there is a file with same name');
            return 1;
        }

        if (file_put_contents("{$path}/{$name}.php", $html)) {

            $path = str_replace(base_path() . '/', '', $path);

            $this->comment('channel class has created');
            $this->info(base_path());
            $this->info("class {$path}/{$name}.php");
            return 0;
        }


        $this->warn('no write permission on "'.$path.'"');

        return 1;
    }

    private function createLocalViewFactory(): Factory
    {
        $resolver = new EngineResolver();
        $resolver->register('php', function () {
            return new PhpEngine($this->app['files']);
        });
        $finder = new FileViewFinder($this->app['files'], [__DIR__ . '/../../resources/views']);
        $factory = new Factory($resolver, $finder, $this->app['events']);
        $factory->addExtension('php', 'php');

        return $factory;
    }
}
