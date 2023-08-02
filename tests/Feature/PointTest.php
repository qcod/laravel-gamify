<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Feature;

use QCod\Gamify\Exceptions\InvalidPayeeModel;
use QCod\Gamify\Exceptions\PointsNotDefined;
use QCod\Gamify\Exceptions\PointSubjectNotSet;
use QCod\Gamify\PointType;
use QCod\Gamify\Tests\Fixtures\Models\User;
use QCod\Gamify\Tests\TestCase;

class PointTest extends TestCase
{
    /**
     * it_sets_point_type_name_from_class_name
     *
     * @test
     */
    public function it_sets_point_type_name_from_class_name()
    {
        $point = new FakeCreatePostPoint(1);

        $this->assertEquals('FakeCreatePostPoint', $point->getName());
    }

    /**
     * it uses name property for point name if provided
     *
     * @test
     */
    public function it_uses_name_property_for_point_name_if_provided()
    {
        $point = new FakeWelcomeUserWithNamePoint(1);

        $this->assertEquals('FakeName', $point->getName());
    }

    /**
     * it can get points for a point type
     *
     * @test
     */
    public function it_can_get_points_for_a_point_type()
    {
        $point = new FakeCreatePostPoint(1);

        $this->assertEquals(10, $point->getPoints());
    }

    /**
     * it gives point to a user
     *
     * @test
     */
    public function it_gives_point_to_a_user()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        $user->givePoint(new FakeCreatePostPoint($post));

        $this->assertEquals(10, $user->fresh()->getPoints());
        $this->assertCount(1, $user->reputations);
        $this->assertDatabaseHas('reputations', [
            'payee_id' => $user->id,
            'subject_type' => $post->getMorphClass(),
            'subject_id' => $post->id,
            'point' => 10,
            'name' => 'FakeCreatePostPoint',
        ]);
    }

    /**
     * it can access a reputation payee and subject
     *
     * @test
     */
    public function it_can_access_a_reputation_payee_and_subject()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        $user->givePoint(new FakeCreatePostPoint($post));

        $point = $user->reputations()->first();

        $this->assertEquals($user->id, $point->payee->id);
        $this->assertEquals($post->id, $point->subject->id);

        $this->assertEquals('FakeCreatePostPoint', $post->reputations->first()->name);
    }

    /**
     * it only adds unique point reward if property is set on point type
     *
     * @test
     */
    public function it_only_adds_unique_point_reward_if_property_is_set_on_point_type()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        $user->givePoint(new FakeCreatePostPoint($post));
        $user->givePoint(new FakeCreatePostPoint($post));

        $this->assertEquals(10, $user->fresh()->getPoints());
        $this->assertCount(1, $user->reputations);
    }

    /**
     * it can store duplicate reputations if no property set
     *
     * @test
     */
    public function it_can_store_duplicate_reputations_if_no_property_set()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        $user->givePoint(new FakeWelcomeUserWithNamePoint($post));
        $user->givePoint(new FakeWelcomeUserWithNamePoint($post));

        $this->assertEquals(60, $user->fresh()->getPoints());
        $this->assertCount(2, $user->reputations);
    }

    /**
     * it do not give point if qualifier returns false
     *
     * @test
     */
    public function it_do_not_give_point_if_qualifier_returns_false()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        $user->givePoint(new FakeWelcomeUserWithFalseQualifier($post));

        $this->assertEquals(0, $user->fresh()->getPoints());
        $this->assertCount(0, $user->reputations);
    }

    /**
     * it uses payee field on point as relation if no payee method override
     *
     * @test
     */
    public function it_uses_payee_field_on_point_as_relation_if_no_payee_method_override()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        $user->givePoint(new FakePayeeFieldPoint($post));

        $this->assertEquals(10, $user->fresh()->getPoints());
        $this->assertCount(1, $user->reputations);
    }

    /**
     * it can undo a reward by given model
     *
     * @test
     */
    public function it_can_undo_a_reward_by_given_model()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);
        $user->givePoint(new FakeWelcomeUserWithNamePoint($post));
        $user->givePoint(new FakeWelcomeUserWithNamePoint($post));
        $this->assertEquals(60, $user->fresh()->getPoints());
        $this->assertCount(2, $user->reputations);

        $user->undoPoint(new FakeWelcomeUserWithNamePoint($post));

        $this->assertEquals(30, $user->fresh()->getPoints());
        $this->assertCount(1, $user->fresh()->reputations);

        $user->undoPoint(new FakeWelcomeUserWithNamePoint($post));

        $this->assertEquals(0, $user->fresh()->getPoints());
        $this->assertCount(0, $user->fresh()->reputations);
    }

    /**
     * it throws exception if no payee is returned
     *
     * @test
     */
    public function it_throws_exception_if_no_payee_is_returned()
    {
        $user = $this->createUser();
        $this->expectException(InvalidPayeeModel::class);

        $user->givePoint(new FakePointTypeWithoutPayee());

        $this->assertEquals(0, $user->fresh()->getPoints());
        $this->assertCount(0, $user->reputations);
    }

    /**
     * it throws exception if no subject is set
     *
     * @test
     */
    public function it_throws_exception_if_no_subject_is_set()
    {
        $user = $this->createUser();
        $this->expectException(PointSubjectNotSet::class);

        $user->givePoint(new FakePointTypeWithoutSubject());

        $this->assertEquals(0, $user->fresh()->getPoints());
        $this->assertCount(0, $user->reputations);
    }

    /**
     * it throws exception if no points field or method is defined
     *
     * @test
     */
    public function it_throws_exception_if_no_points_field_or_method_is_defined()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);
        $this->expectException(PointsNotDefined::class);

        $user->givePoint(new FakePointWithoutPoint($post));

        $this->assertEquals(0, $user->fresh()->getPoints());
        $this->assertCount(0, $user->reputations);
    }

    /**
     * it gives and undo point via helper functions
     *
     * @test
     */
    public function it_gives_and_undo_point_via_helper_functions()
    {
        $user = $this->createUser();
        $post = $this->createPost(['user_id' => $user->id]);

        givePoint(new FakePayeeFieldPoint($post), $user);

        $this->assertEquals(10, $user->fresh()->getPoints());
        $this->assertCount(1, $user->reputations);

        undoPoint(new FakePayeeFieldPoint($post), $user);

        $user = $user->fresh();
        $this->assertEquals(0, $user->getPoints());
        $this->assertCount(0, $user->reputations);
    }
}

class FakeCreatePostPoint extends PointType
{
    protected $points = 10;

    public $allowDuplicates = false;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function payee()
    {
        return $this->getSubject()->user;
    }
}

class FakeWelcomeUserWithNamePoint extends PointType
{
    protected $name = 'FakeName';

    protected $points = 30;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function payee()
    {
        return $this->getSubject()->user;
    }
}

class FakeWelcomeUserWithFalseQualifier extends PointType
{
    protected $points = 10;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function qualifier()
    {
        return false;
    }

    public function payee()
    {
        return $this->getSubject()->user;
    }
}

class FakePointTypeWithoutPayee extends PointType
{
    protected $point = 24;

    public function payee()
    {
    }
}

class FakePointTypeWithoutSubject extends PointType
{
    protected $point = 12;

    public function payee()
    {
        return new User();
    }
}

class FakePointWithoutPoint extends PointType
{
    protected $payee = 'user';

    public function __construct($subject)
    {
        $this->subject = $subject;
    }
}

class FakePayeeFieldPoint extends PointType
{
    protected $points = 10;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /** @var string payee model relation on subject */
    protected $payee = 'user';
}
