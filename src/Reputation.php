<?php

namespace QCod\Gamify;

use Illuminate\Database\Eloquent\Model;

class Reputation extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Payee user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payee()
    {
        return $this->belongsTo(config('gamify.payee_model'), 'payee_id');
    }

    /**
     * Undo last point
     *
     * @throws \Exception
     */
    public function undo()
    {
        if ($this->exists) {
            $this->payee->reducePoint($this->point);
            $this->delete();
        }
    }
}
