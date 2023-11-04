<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Organisation extends Model
{
    use HasApiTokens, HasFactory, Notifiable,HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
     protected $table="t_organisation";

     protected $fillable = [
        'name',
        'email',
        'phone',
        'description',
        'logo',
        'sigle',
        'adresse',
        'pointfocal',
        'typeorgid',
        'category',
        'status',
        'delete'
    ];

    public function allindicateur()
    {
        return $this->hasMany(org_indicateur::class, 'orgid','id');
    }



}
