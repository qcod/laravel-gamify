<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use QCod\Gamify\GamifyServiceProvider;
use QCod\Gamify\Tests\Fixtures\Badges\FirstContribution;
use QCod\Gamify\Tests\Fixtures\Badges\FirstThousandPoints;
use QCod\Gamify\Tests\Fixtures\Models\User;

abstract class TestCase extends OrchestraTestCase
{
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/database/migrations');
    }

    /** @param \Illuminate\Foundation\Application $app */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('gamify.payee_model', User::class);

        $app->singleton('badges', function () {
            return collect([FirstContribution::class, FirstThousandPoints::class])
                ->map(fn (string $badge) => app($badge));
        });
    }

    protected function getPackageProviders($app): array
    {
        return [GamifyServiceProvider::class];
    }
}
