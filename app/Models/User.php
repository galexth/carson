<?php

namespace App\Models;

use App\Exceptions\ApiException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Stripe\Charge;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 * @property string                                        $name
 * @property string                                        $address
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
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
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
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getLastPurchasesAttribute()
    {
        return $this->charges(['limit' => 10])->data;
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
     * @return bool
     */
    public function isApproved()
    {
        return $this->status == static::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return $this->status == static::STATUS_BANNED;
    }

    /**
     * @param array $options
     *
     * @return \Stripe\Collection
     * @throws \App\Exceptions\ApiException
     */
    public function charges(array $options = [])
    {
        try {
            return Charge::all(array_merge([
                'customer' => $this->stripe_id,
            ], $options));
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
            throw new ApiException($e->getMessage(), 400);
        }
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
        $this->status = static::STATUS_APPROVED;

        return $this->save();
    }

}
