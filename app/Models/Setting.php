<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name',
        'app_logo',
        'theme',
        'registration_enabled',
        'google_client_id',
        'google_client_secret',
        'google_redirect_url',
        'google_login_enabled',
        'facebook_client_id',
        'facebook_client_secret',
        'facebook_redirect_url',
        'facebook_login_enabled',
    ];
}
