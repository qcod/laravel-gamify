<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

test('a badge can be given to a user', function () {
    $user = createUser();
    $badge = createBadge();

    $badge->awardTo($user);

    assertCount(1, $user->badges);
    assertTrue($user->badges->first()->is($badge));
});

test('a badge can be remove from a user', function () {
    $user = createUser();
    $badge = createBadge();
    $badge->awardTo($user);
    assertCount(1, $user->badges);

    $badge->removeFrom($user);

    assertCount(0, $user->fresh()->badges);
});

test(
    'a badge is awarded if user point reached 1000',
    function () {
        $user = createUser();
        assertCount(0, $user->badges);

        $user->addPoint(1001);

        assertCount(1, $user->fresh()->badges);

        $user->reducePoint(10);

        assertCount(0, $user->fresh()->badges);
    }
);

test('a badge is given when user first creat a post', function () {
    $user = createUser();
    assertCount(0, $user->badges);

    createPost(['user_id' => $user->id]);
    assertCount(0, $user->fresh()->badges);

    $user->addPoint(20);
    assertCount(1, $user->fresh()->badges);

    assertEquals('First Contribution', $user->fresh()->badges->first()->name);
});
