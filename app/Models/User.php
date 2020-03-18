<?php

namespace App\Models;

use App\Task;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Stripe\Charge;

/**
 * Class User
 * @property string                                        $name
 * @property string                                        $email
 * @property string                                        $password
 * @property string                                        $status
 * @property string                                        $role
 * @property string                                        $stripe_id
 * @property integer                                       $credits
 * @property \Carbon\Carbon                                $created_at
 * @property \Carbon\Carbon                                $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection $tasks
 *
 * @mixin \Eloquent
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BANNED = 'banned';

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getLastPurchasesAttribute()
    {
        $this->charges(['limit' => 10]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Task
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id', 'id');
    }

    /**
     * @return bool
     */
    public function hasCredits()
    {
        return $this->credits > 0;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role == static::ROLE_ADMIN;
    }

    /**
     * @param array $options
     *
     * @return \Stripe\Collection
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function charges(array $options = [])
    {
        return Charge::all(array_merge([
            'customer' => $this->stripe_id,
        ], $options));
    }

    /**
     * @return bool
     */
    public function ban()
    {
        $this->status = static::STATUS_BANNED;

        return $this->save();
    }

    /**
     * @return bool
     */
    public function approve()
    {
        $this->status = static::STATUS_ACTIVE;

        return $this->save();
    }

}
