<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ProjetModel extends Model
{
    use HasApiTokens, HasFactory, Notifiable,HasUuids;

    protected $table="t_projets";
    protected $fillable = [
        'title_projet',
        'provinceid',
        'zoneid',
        'airid',
        'org_make_repport',
        'org_make_oeuvre',
        'identifiant_project',
        'typeprojetid',
        'type_intervention',
        'axe_strategique',
        'odd',
        'description_activite',
        'statut_activite',
        'modalite',
        'src_financement',
        'cohp_relais',
        'date_debut_projet',
        'date_fin_projet',
        'type_benef',
        'typeid',
        'typereponse'
];

}
