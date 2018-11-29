<?php

namespace QCod\Gamify\Tests;

use QCod\Gamify\PointType;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use QCod\Gamify\Events\ReputationChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReputationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * it gets user points
     *
     * @test
     */
    public function it_gets_user_points()
    {
        $user = $this->createUser(['reputation' => 10]);

        $this->assertEquals(10, $user->getPoints());
    }

    /**
     * it gives reputation point to a user
     *
     * @test
     */
    public function it_gives_reputation_point_to_a_user()
    {
        $user = $this->createUser();
        $this->assertEquals(0, $user->getPoints());

        $user->addPoint(10);

        $this->assertEquals(10, $user->fresh()->getPoints());
    }

    /**
     * it reduces reputation point for a user
     *
     * @test
     */
    public function it_reduces_reputation_point_for_a_user()
    {
        $user = $this->createUser(['reputation' => 20]);
        $this->assertEquals(20, $user->reputation);

        $user->reducePoint(5);

        $this->assertEquals(15, $user->fresh()->getPoints());
    }

    /**
     * it zeros reputation point of a user
     *
     * @test
     */
    public function it_zeros_reputation_point_of_a_user()
    {
        $user = $this->createUser(['reputation' => 50]);
        $this->assertEquals(50, $user->getPoints());

        $user->resetPoint();

        $this->assertEquals(0, $user->fresh()->getPoints());
    }

    /**
     * it fires event on reputation change
     *
     * @test
     */
    public function it_fires_event_on_reputation_change()
    {
        Event::fake();

        $user = $this->createUser();
        $this->assertEquals(0, $user->getPoints());

        $user->addPoint(10);

        Event::assertDispatched(ReputationChanged::class, function ($event) use ($user) {
            return ($event->point === 10 && $user->id == $event->user->id && $event->increment);
        });

        $this->assertEquals(10, $user->fresh()->getPoints());
    }

    /**
     * it fires event on reputation reduced
     *
     * @test
     */
    public function it_fires_event_on_reputation_reduced()
    {
        Event::fake();

        $user = $this->createUser(['reputation' => 10]);

        $user->reducePoint(3);

        Event::assertDispatched(ReputationChanged::class, function ($event) use ($user) {
            return ($event->point === 3 && $user->id == $event->user->id && !$event->increment);
        });

        $this->assertEquals(7, $user->fresh()->getPoints());
    }
}

class FakePostCreated extends PointType
{
    protected $points = 10;

    public function __construct($model)
    {
        $this->setSubject($model);
    }

    /**
     * Check qualification for this point
     *
     * @return bool
     */
    public function qualifier()
    {
        return true;
    }

    /**
     * User who will be recieving point
     *
     * @return Model
     */
    public function payee()
    {
        return $this->getSubject()->user;
    }
}

class FakeBestReply extends PointType
{
    protected $points = 50;

    public function __construct($model)
    {
        $this->setSubject($model);
    }

    /**
     * Check qualification for this point
     *
     * @return bool
     */
    public function qualifier()
    {
        return !is_null($this->getSubject()->best_reply_id);
    }

    /**
     * User who will be recieving point
     *
     * @return Model
     */
    public function payee()
    {
        return $this->getSubject()->bestReply->user;
    }
}
