<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use QCod\Gamify\Badge;
use QCod\Gamify\Tests\Fixtures\Badges\FirstContribution;
use QCod\Gamify\Tests\Fixtures\Badges\FirstThousandPoints;
use QCod\Gamify\Tests\Fixtures\Models\Post;
use QCod\Gamify\Tests\Fixtures\Models\User;

abstract class TestCase extends OrchestraTestCase
{
    /** @inheritdoc */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/database/migrations');
    }

    /** @param \Illuminate\Foundation\Application $app */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('gamify.payee_model', '\QCod\Gamify\Tests\Fixtures\Models\User');

        // test badges
        $app->singleton('badges', function () {
            return collect([FirstContribution::class, FirstThousandPoints::class])
                ->map(function (string $badge) {
                    return app($badge);
                });
        });
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['QCod\Gamify\GamifyServiceProvider'];
    }

    /**
     * Create a user
     *
     * @param array $attrs
     * @return User
     */
    public function createUser($attrs = [])
    {
        $user = new User();

        $user->forceFill(array_merge($attrs, [
            'name' => 'Saqueib',
            'email' => 'me@example.com',
            'password' => 'secret',
        ]))->save();

        return $user->fresh();
    }

    /**
     * Create a post
     *
     * @param array $attrs
     * @return Post
     */
    public function createPost($attrs = [])
    {
        $post = new Post();

        $post->forceFill(array_merge($attrs, [
            'title' => 'Dummy post title',
            'body' => 'I am the content on dummy post',
            'user_id' => 1,
        ]))->save();

        return $post->fresh();
    }

    /**
     * Create a badge
     *
     * @param array $attrs
     * @return Badge
     */
    public function createBadge($attrs = [])
    {
        $badge = new Badge();

        $badge->forceFill(array_merge($attrs, [
            'name' => 'New Member',
            'description' => 'Welcome new user',
            'icon' => 'images/new-member-icon.svg',
        ]))->save();

        return $badge->fresh();
    }
}
