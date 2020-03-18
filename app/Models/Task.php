<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Task
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property string $category
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Eloquent
 */
class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'category'
    ];
}
