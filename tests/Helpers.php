<?php

declare(strict_types=1);

use QCod\Gamify\Badge;
use QCod\Gamify\Tests\Fixtures\Models\Post;
use QCod\Gamify\Tests\Fixtures\Models\User;

function createUser(array $attributes = []): User
{
    $user = new User();

    $user->forceFill(array_merge($attributes, [
        'name' => 'Saqueib',
        'email' => 'me@example.com',
        'password' => 'secret',
    ]))->save();

    return $user->fresh();
}

function createPost(array $attributes = []): Post
{
    $post = new Post();

    $post->forceFill(array_merge($attributes, [
        'title' => 'Dummy post title',
        'body' => 'I am the content on dummy post',
        'user_id' => 1,
    ]))->save();

    return $post->fresh();
}

function createBadge(array $attributes = []): Badge
{
    $badge = new Badge();

    $badge->forceFill(array_merge($attributes, [
        'name' => 'New Member',
        'description' => 'Welcome new user',
        'icon' => 'images/new-member-icon.svg',
    ]))->save();

    return $badge->fresh();
}
