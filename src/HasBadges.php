<?php

namespace QCod\Gamify;

trait HasBadges
{
    /**
     * Badges user relation
     *
     * @return mixed
     */
    public function badges()
    {
        return $this->belongsToMany(config('gamify.badge_model'), 'user_badges')
            ->withTimestamps();
    }

    /**
      * Give badge to user
      *
      * @param BadgeType $badge
      * @param $preDefinedBadgeLevel int
      * @return bool
      */
    public function giveBadge(BadgeType $badge, $forceLevel = null)
    {
        if (!is_null($forceLevel)) {
            return $badge->getModel($forceLevel)->awardTo($this);
        }


        if (!($level = $badge->islevelArchived($this, $badge->_extraVariablesForQualify))) {
            return false;
        }
        if (!is_numeric($level)) {
            $level = config('gamify.badge_default_level', 1);
        }
        
        return $badge->getModel($level)->awardTo($this);
    }

    public function removeBadge(BadgeType $badge)
    {
        if (!($level = $badge->islevelArchived($this, $badge->_extraVariablesForQualify))) {
            return false;
        }
        
        return $this->model[$level]->removeBadge($this);
    }
}
