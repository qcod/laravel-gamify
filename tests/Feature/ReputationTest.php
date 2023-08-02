<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use QCod\Gamify\Events\ReputationChanged;

use function PHPUnit\Framework\assertEquals;

uses(RefreshDatabase::class);

it('gets user points', function () {
    $user = createUser(['reputation' => 10]);

    assertEquals(10, $user->getPoints());
});

it('gives reputation point to a user', function () {
    $user = createUser();
    assertEquals(0, $user->getPoints());

    $user->addPoint(10);

    assertEquals(10, $user->fresh()->getPoints());
});

it('reduces reputation point for a user', function () {
    $user = createUser(['reputation' => 20]);
    assertEquals(20, $user->reputation);

    $user->reducePoint(5);

    assertEquals(15, $user->fresh()->getPoints());
});

it('zeros reputation point of a user', function () {
    $user = createUser(['reputation' => 50]);
    assertEquals(50, $user->getPoints());

    $user->resetPoint();

    assertEquals(0, $user->fresh()->getPoints());
});

it('fires event on reputation change', function () {
    Event::fake();

    $user = createUser();
    assertEquals(0, $user->getPoints());

    $user->addPoint(10);

    Event::assertDispatched(ReputationChanged::class, function ($event) use ($user) {
        return ($event->point === 10 && $user->id == $event->user->id && $event->increment);
    });

    assertEquals(10, $user->fresh()->getPoints());
});

it('fires event on reputation reduced', function () {
    Event::fake();

    $user = createUser(['reputation' => 10]);

    $user->reducePoint(3);

    Event::assertDispatched(ReputationChanged::class, function ($event) use ($user) {
        return ($event->point === 3 && $user->id == $event->user->id && ! $event->increment);
    });

    assertEquals(7, $user->fresh()->getPoints());
});
