<?php

namespace JawabApp\Gamify\Tests;

class BadgeTest extends TestCase
{
    /**
     * a badge can be given to a user
     *
     * @test
     */
    public function a_badge_can_be_given_to_a_user()
    {
        $user = $this->createUser();
        $badge = $this->createBadge();

        $badge->awardTo($user);

        $this->assertCount(1, $user->badges);
        $this->assertTrue($user->badges->first()->is($badge));
    }

    /**
     * a badge can be remove from a user
     *
     * @test
     */
    public function a_badge_can_be_remove_from_a_user()
    {
        $user = $this->createUser();
        $badge = $this->createBadge();
        $badge->awardTo($user);
        $this->assertCount(1, $user->badges);

        $badge->removeFrom($user);

        $this->assertCount(0, $user->fresh()->badges);
    }

    /**
     * a badge is awarded if user point reached 1000
     *
     * @test
     */
    public function a_badge_is_awarded_if_user_point_reached_1000()
    {
        $user = $this->createUser();
        $this->assertCount(0, $user->badges);

        $user->addPoint(1001);

        $this->assertCount(1, $user->fresh()->badges);

        $user->reducePoint(10);

        $this->assertCount(0, $user->fresh()->badges);
    }

    /**
     * a badge is given when user first creats a post
     *
     * @test
     */
    public function a_badge_is_given_when_user_first_creats_a_post()
    {
        $user = $this->createUser();
        $this->assertCount(0, $user->badges);

        $this->createPost(['user_id' => $user->id]);
        $this->assertCount(0, $user->fresh()->badges);

        $user->addPoint(20);
        $this->assertCount(1, $user->fresh()->badges);

        $this->assertEquals('First Contribution', $user->fresh()->badges->first()->name);
    }
}
