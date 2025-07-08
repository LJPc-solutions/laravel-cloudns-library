<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests;

use LJPc\ClouDNS\ClouDNSServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ClouDNSServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cloudns.auth_id', 'test-auth-id');
        $app['config']->set('cloudns.auth_password', 'test-password');
        $app['config']->set('cloudns.is_sub_user', false);
        $app['config']->set('cloudns.base_url', 'https://api.cloudns.net');
        $app['config']->set('cloudns.response_format', 'json');
        $app['config']->set('cloudns.cache_enabled', false);
        $app['config']->set('cloudns.log_enabled', false);
    }
}