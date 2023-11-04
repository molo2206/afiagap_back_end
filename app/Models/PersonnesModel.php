<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PersonnesModel extends Model
{
    use HasFactory,HasUuids,Notifiable;

    protected $table="t_personnes";

    protected $fillable = [
        'id',
        'nom',
        'postnom',
        'prenom',
        'sexe',
        'roleid',
        'typepersonneid',
        'nom_pere',
        'nom_mere',
        'probleme_sante',
        'lieu_naissance',
        'datenaiss',
        'sous_moustiquaire',
        'photo',
        'bar_code',
        'empreinte_digital',
        'manageid',
        'femme_enceinte',
        'femme_allaitante'
    ];
    public function datatype_personne()
    {
        return $this->belongsTo(TypePersonneModel::class, 'typepersonneid','id');
    }

    public function datarole()
    {
        return $this->belongsTo(RoleMenageModel::class, 'roleid','id');
    }
}
