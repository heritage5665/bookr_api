<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use Rateable;
    /**
     * The attribute that are mass assignable
     *
     * @var array
     */
    protected $fillable = ['name', 'biography', 'gender'];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
