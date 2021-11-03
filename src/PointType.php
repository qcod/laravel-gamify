<?php

namespace JawabApp\Gamify;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use JawabApp\Gamify\Exceptions\PointsNotDefined;
use JawabApp\Gamify\Exceptions\InvalidPayeeModel;
use JawabApp\Gamify\Exceptions\PointSubjectNotSet;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class PointType
{
    /**
     * Subject for reputation
     *
     * @var Model
     */
    protected $subject;

    /**
     * Check qualification to give this point
     *
     * @return bool
     */
    public function qualifier()
    {
        return true;
    }

    /**
     * Payee who will be recieving points
     *
     * @return Model
     */
    public function payee()
    {
        if (property_exists($this, 'payee')) {
            return $this->getSubject()->{$this->payee};
        }

        throw new InvalidPayeeModel();
    }

    /**
     * Subject model for point
     *
     * @return Model
     */
    public function getSubject()
    {
        if (!isset($this->subject)) {
            throw new PointSubjectNotSet();
        }

        return $this->subject;
    }

    /**
     * Get point name
     *
     * @return string
     */
    public function getName()
    {
        return property_exists($this, 'name')
            ? $this->name
            : class_basename($this);
    }

    /**
     * Get points
     *
     * @return int
     * @throws PointsNotDefined
     */
    public function getPoints()
    {
        if (!isset($this->points)) {
            throw new PointsNotDefined();
        }

        return $this->points;
    }

    /**
     * Set a subject
     *
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Check if reputation alredy exists for a point
     *
     * @return bool
     * @throws InvalidPayeeModel
     */
    public function reputationExists()
    {
        return $this->reputationQuery()->exists();
    }

    /**
     * Get first reputation for point
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws InvalidPayeeModel
     */
    public function firstReputation()
    {
        return $this->reputationQuery()->first();
    }

    /**
     * Store a reputation in the database
     *
     * @param array $meta
     * @return mixed
     * @throws InvalidPayeeModel
     */
    public function storeReputation($meta = [])
    {
        return $this->payeeReputations()->create([
            'payee_id' => $this->payee()->id,
            'subject_type' => $this->getSubject()->getMorphClass(),
            'subject_id' => $this->getSubject()->getKey(),
            'name' => $this->getName(),
            'meta' => json_encode($meta),
            'point' => $this->getPoints()
        ]);
    }

    /**
     * Get reputation query for this point
     *
     * @return Builder
     * @throws InvalidPayeeModel
     */
    public function reputationQuery()
    {

        return $this->payeeReputations()->where([
            ['payee_id', $this->payee()->id],
            ['subject_type', $this->getSubject()->getMorphClass()],
            ['subject_id', $this->getSubject()->getKey()],
            ['name', $this->getName()]
        ]);
    }

    /**
     * Return reputations payee relation
     *
     * @return HasMany
     * @throws InvalidPayeeModel
     */
    protected function payeeReputations()
    {
        $model = $this->payee();

        if (!$model) {
            throw new InvalidPayeeModel();
        }

        return $model->reputations();
    }
}
