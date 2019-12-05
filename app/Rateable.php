<?php

namespace App;

/**
 * Trait to enable polymorphic ratings on model
 */



trait Rateable
{
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
