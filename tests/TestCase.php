<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        putenv('APP_ENV=testing');
        putenv('APP_KEY=base64:KDkGdZSmptDZaYqvXzKAmaRDnm4ovhmyFInsTj2JHIM=');
        putenv('APP_URL=http://localhost');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('SESSION_DRIVER=array');
        putenv('CACHE_STORE=array');
        putenv('QUEUE_CONNECTION=sync');
        putenv('MAIL_MAILER=array');

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_KEY'] = 'base64:KDkGdZSmptDZaYqvXzKAmaRDnm4ovhmyFInsTj2JHIM=';
        $_ENV['APP_URL'] = 'http://localhost';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['SESSION_DRIVER'] = 'array';
        $_ENV['CACHE_STORE'] = 'array';
        $_ENV['QUEUE_CONNECTION'] = 'sync';
        $_ENV['MAIL_MAILER'] = 'array';

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['APP_KEY'] = 'base64:KDkGdZSmptDZaYqvXzKAmaRDnm4ovhmyFInsTj2JHIM=';
        $_SERVER['APP_URL'] = 'http://localhost';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
        $_SERVER['SESSION_DRIVER'] = 'array';
        $_SERVER['CACHE_STORE'] = 'array';
        $_SERVER['QUEUE_CONNECTION'] = 'sync';
        $_SERVER['MAIL_MAILER'] = 'array';

        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $app['config']->set('app.env', 'testing');
        $app['config']->set('app.key', 'base64:KDkGdZSmptDZaYqvXzKAmaRDnm4ovhmyFInsTj2JHIM=');
        $app['config']->set('app.url', 'http://localhost');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('mail.default', 'array');

        return $app;
    }

    protected function withCsrfToken(): static
    {
        return $this->withSession(['_token' => 'test-csrf-token']);
    }
}
