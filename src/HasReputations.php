<?php

namespace QCod\Gamify;

use QCod\Gamify\Events\ReputationChanged;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \QCod\Gamify\Reputation> $reputations
 * @property-read int|null $reputations_count
 */
trait HasReputations
{
    /**
     * Give reputation point to payee
     *
     * @param PointType $pointType
     * @return bool
     */
    public function givePoint(PointType $pointType)
    {
        if (!$pointType->qualifier()) {
            return false;
        }

        if ($this->storeReputation($pointType)) {
            return $pointType->payee()->addPoint($pointType->getPoints());
        }
    }

    /**
     * Undo last given point for a subject model
     *
     * @param PointType $pointType
     * @return bool
     */
    public function undoPoint(PointType $pointType)
    {
        $reputation = $pointType->firstReputation();

        if (!$reputation) {
            return false;
        }

        // undo reputation
        $reputation->undo();
    }

    /**
     * Reputations of user relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reputations()
    {
        return $this->hasMany(config('gamify.reputation_model'), 'payee_id');
    }

    /**
     * Store a reputation for point
     *
     * @param PointType $pointType
     * @param array $meta
     * @return mixed
     */
    public function storeReputation(PointType $pointType, array $meta = [])
    {
        if (!$this->isDuplicatePointAllowed($pointType) && $pointType->reputationExists()) {
            return false;
        }

        return $pointType->storeReputation($meta);
    }

    /**
     * Give point to a user
     *
     * @param int $point
     * @return HasReputations
     */
    public function addPoint($point = 1)
    {
        $this->increment($this->getReputationField(), $point);

        ReputationChanged::dispatch($this, $point, true);

        return $this;
    }

    /**
     * Reduce a user point
     *
     * @param int $point
     * @return HasReputations
     */
    public function reducePoint($point = 1)
    {
        $this->decrement($this->getReputationField(), $point);

        ReputationChanged::dispatch($this, $point, false);

        return $this;
    }

    /**
     * Reset a user point to zero
     *
     * @return mixed
     */
    public function resetPoint()
    {
        $this->forceFill([$this->getReputationField() => 0])->save();

        ReputationChanged::dispatch($this, 0, false);

        return $this;
    }

    /**
     * Get user reputation point
     *
     * @param bool $formatted
     * @return int|string
     */
    public function getPoints($formatted = false)
    {
        $point = $this->{$this->getReputationField()};

        if ($formatted) {
            return short_number($point);
        }

        return (int) $point;
    }

    /**
     * Get the reputation column name
     *
     * @return string
     */
    protected function getReputationField()
    {
        return property_exists($this, 'reputationColumn')
            ? $this->reputationColumn
            : 'reputation';
    }

    /**
     * Check for duplicate point allowed
     *
     * @param PointType $pointType
     * @return bool
     */
    protected function isDuplicatePointAllowed(PointType $pointType)
    {
        return property_exists($pointType, 'allowDuplicates')
            ? $pointType->allowDuplicates
            : config('gamify.allow_reputation_duplicate', true);
    }
}
