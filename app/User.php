<?php

namespace App;

use Auth;
use Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname_lat', 'lastname_lat', 'firstname', 'lastname',
        'passport', 'expire', 'issued', 'country', 'province_id', 'city_id',
        'vulnerability', 'membership',
        'place_of_work', 'position'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'photo'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    protected $casts = [
        'complete' => 'boolean',
        'winner' => 'boolean'
    ];

    protected $appends = ['photo_url', 'province_name', 'fio'];

    public function routeNotificationForSms() {
        return $this->phone;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getRoleAttribute() {
        return explode(',', $this->attributes['role']);
    }

    public function setRoleAttribute($roles) {
        $this->attributes['role'] = is_array($roles) ? implode(',', $roles) : $roles;
    }

    public function getPhotoUrlAttribute()
	{
		return $this->photo ? Storage::url($this->photo) : null;
	}

	public function setPhotoAttribute($photo) {
		if($this->photo) {
			Storage::delete($this->photo);
		}

		if($photo) {
			$this->attributes['photo'] = $photo->store('users');
		} else {
			$this->attributes['photo'] = null;
		}
    }

    public function getProvinceNameAttribute() {
        return $this->province ? $this->province->ru : null;
    }

    public function getFioAttribute() {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function questionnaires($level = false)
    {
        return $this->hasMany(Questionnaire::class)
            ->when($level, function($query) use ($level) {
                $query->where('level', $level);
            })
            ->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function firstQuestionnaire()
    {
        return $this->hasOne(Questionnaire::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function questionnaire($level = false)
    {
        return $this->hasOne(Questionnaire::class)
            ->when($level, function($query) use ($level) {
                $query->where('level', $level);
            })
            ->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function questionnaire1()
    {
        return $this->questionnaire(1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function questionnaire2()
    {
        return $this->questionnaire(2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function questionnaire3()
    {
        return $this->questionnaire(3);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function topQuestionnaire($level = false)
    {
        return $this->hasOne(Questionnaire::class)
                    //->today()
                    ->where('level', $level ?: Auth::user()->level)
                    ->orderBy('result', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function topQuestionnaire1()
    {
        return $this->topQuestionnaire(1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function topQuestionnaire2()
    {
        return $this->topQuestionnaire(2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function topQuestionnaire3()
    {
        return $this->topQuestionnaire(3);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function completeQuestionnaires($level = false)
    {
        return $this->hasMany(Questionnaire::class)
                    ->where('level', $level ?: Auth::user()->level)
                    ->where('result', 100)
                    ->where('code', '<>', 0)
                    ->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function completeQuestionnaires1()
    {
        return $this->completeQuestionnaires(1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function completeQuestionnaires2()
    {
        return $this->completeQuestionnaires(2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function completeQuestionnaires3()
    {
        return $this->completeQuestionnaires(3);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function province()
    {
        return $this->belongsTo(Placename::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function city()
    {
        return $this->belongsTo(Placename::class);
    }
    
    public function setVulnerabilityAttribute($value) {
        $this->attributes['vulnerability'] = implode(',', $value);
    }

    public function getVulnerabilityAttribute() {
        return explode(',', $this->attributes['vulnerability']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function trainingApplications()
    {
        return $this->hasMany('App\TrainingApplication');
    }

    public function trainingApplication($trainingId)
    {
        return $this->trainingApplications->where('training_id', $trainingId)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    public function sessions() {
        return $this->belongsToMany(Session::class)->using(UserSession::class)->withPivot('roles');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function usersSessions($user_id) {
        return $this->hasMany(UserSession::class)
            ->whereHas(['session' => function($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    public function contacts() {
        return $this->belongsToMany(User::class, 'contacts', 'contact_id', 'user_id');
    }
}
