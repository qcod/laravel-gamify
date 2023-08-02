<?php

declare(strict_types=1);

use QCod\Gamify\Exceptions\InvalidPayeeModel;
use QCod\Gamify\Exceptions\PointsNotDefined;
use QCod\Gamify\Exceptions\PointSubjectNotSet;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakeCreatePostPoint;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakePayeeFieldPoint;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakePointTypeWithoutPayee;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakePointTypeWithoutSubject;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakePointWithoutPoint;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakeWelcomeUserWithFalseQualifier;
use QCod\Gamify\Tests\Fixtures\Fake\PointTypes\FakeWelcomeUserWithNamePoint;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

it('sets point type name from class name', function () {
    $point = new FakeCreatePostPoint(1);

    assertEquals('FakeCreatePostPoint', $point->getName());
});

it('uses name property for point name if provided', function () {
    $point = new FakeWelcomeUserWithNamePoint(1);

    assertEquals('FakeName', $point->getName());
});

it('can get points for a point type', function () {
    $point = new FakeCreatePostPoint(1);

    assertEquals(10, $point->getPoints());
});

it('gives point to a user', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakeCreatePostPoint($post));

    assertEquals(10, $user->fresh()->getPoints());
    assertCount(1, $user->reputations);
    assertDatabaseHas('reputations', [
        'payee_id' => $user->id,
        'subject_type' => $post->getMorphClass(),
        'subject_id' => $post->id,
        'point' => 10,
        'name' => 'FakeCreatePostPoint',
    ]);
});

it('can access a reputation payee and subject', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakeCreatePostPoint($post));

    $point = $user->reputations()->first();

    assertEquals($user->id, $point->payee->id);
    assertEquals($post->id, $point->subject->id);

    assertEquals('FakeCreatePostPoint', $post->reputations->first()->name);
});

it('only adds unique point reward if property is set on point type', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakeCreatePostPoint($post));
    $user->givePoint(new FakeCreatePostPoint($post));

    assertEquals(10, $user->fresh()->getPoints());
    assertCount(1, $user->reputations);
});

it('can store duplicate reputations if no property set', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakeWelcomeUserWithNamePoint($post));
    $user->givePoint(new FakeWelcomeUserWithNamePoint($post));

    assertEquals(60, $user->fresh()->getPoints());
    assertCount(2, $user->reputations);
});

it('do not give point if qualifier returns false', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakeWelcomeUserWithFalseQualifier($post));

    assertEquals(0, $user->fresh()->getPoints());
    assertCount(0, $user->reputations);
});

it('uses payee field on point as relation if no payee method override', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakePayeeFieldPoint($post));

    assertEquals(10, $user->fresh()->getPoints());
    assertCount(1, $user->reputations);
});

it('can undo a reward by given model', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);
    $user->givePoint(new FakeWelcomeUserWithNamePoint($post));
    $user->givePoint(new FakeWelcomeUserWithNamePoint($post));
    assertEquals(60, $user->fresh()->getPoints());
    assertCount(2, $user->reputations);

    $user->undoPoint(new FakeWelcomeUserWithNamePoint($post));

    assertEquals(30, $user->fresh()->getPoints());
    assertCount(1, $user->fresh()->reputations);

    $user->undoPoint(new FakeWelcomeUserWithNamePoint($post));

    assertEquals(0, $user->fresh()->getPoints());
    assertCount(0, $user->fresh()->reputations);
});

it('throws exception if no payee is returned', function () {
    $user = createUser();
    $user->givePoint(new FakePointTypeWithoutPayee());

    assertEquals(0, $user->fresh()->getPoints());
    assertCount(0, $user->reputations);
})
    ->throws(InvalidPayeeModel::class);

it('throws exception if no subject is set', function () {
    $user = createUser();

    $user->givePoint(new FakePointTypeWithoutSubject());

    assertEquals(0, $user->fresh()->getPoints());
    assertCount(0, $user->reputations);
})
    ->throws(PointSubjectNotSet::class);

it('throws exception if no points field or method is defined', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    $user->givePoint(new FakePointWithoutPoint($post));

    assertEquals(0, $user->fresh()->getPoints());
    assertCount(0, $user->reputations);
})
    ->throws(PointsNotDefined::class);

it('gives and undo point via helper functions', function () {
    $user = createUser();
    $post = createPost(['user_id' => $user->id]);

    givePoint(new FakePayeeFieldPoint($post), $user);

    assertEquals(10, $user->fresh()->getPoints());
    assertCount(1, $user->reputations);

    undoPoint(new FakePayeeFieldPoint($post), $user);

    $user = $user->fresh();
    assertEquals(0, $user->getPoints());
    assertCount(0, $user->reputations);
});
