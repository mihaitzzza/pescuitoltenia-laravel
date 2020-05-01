<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    /**
     * Get the author of the article.
    */
    public function author()
    {
        return $this->belongsTo('App\User');
    }
}
