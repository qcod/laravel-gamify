<?php

namespace QCod\Gamify\Tests;

use QCod\Gamify\Badge;
use QCod\Gamify\Tests\Models\Post;
use QCod\Gamify\Tests\Models\User;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('gamify.payee_model', '\QCod\Gamify\Tests\Models\User');

        // test badges
        $app->singleton('badges', function () {
            return collect(['FirstContribution', 'FirstThousandPoints'])
                ->map(function ($badge) {
                    return app("QCod\\Gamify\\Tests\Badges\\".$badge);
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
            'password' => 'secret'
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
            'user_id' => 1
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
