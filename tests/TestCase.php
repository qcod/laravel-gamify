<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use QCod\Gamify\GamifyServiceProvider;
use QCod\Gamify\Tests\Fixtures\Badges\FirstContribution;
use QCod\Gamify\Tests\Fixtures\Badges\FirstThousandPoints;
use QCod\Gamify\Tests\Fixtures\Models\Post;
use QCod\Gamify\Tests\Fixtures\Models\Reply;
use QCod\Gamify\Tests\Fixtures\Models\User;
use AddReputationFieldOnUserTable;
use CreateGamifyTables;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase($app): void
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create((new Post())->getTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('body');
            $table->unsignedInteger('best_reply_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamps();
        });
        $schema->create((new Reply())->getTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->text('body');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('post_id');
            $table->timestamps();
        });
        $schema->create((new User())->getTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        include_once __DIR__.'/../database/migrations/add_reputation_on_user_table.php.stub';
        (new AddReputationFieldOnUserTable())->up();

        include_once __DIR__.'/../database/migrations/create_gamify_tables.php.stub';
        (new CreateGamifyTables())->up();
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
