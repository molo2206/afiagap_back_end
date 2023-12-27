<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AlertModel extends Model
{
    use HasApiTokens, HasFactory, Notifiable,HasUuids;

     protected $table="t_alert";
     protected $fillable = [
        'name_point_focal',
        "phone",
        "communaute",
        "airid",
        "date_notification",
        "datealert",
        "timealert",
        "nbr_touche",
        "dece_disponible",
        "nbr_dece",
        "animal_malade",
        "animal_mort",
        "evenement",
        "mesure",
        "maladieid"
    ];

}
