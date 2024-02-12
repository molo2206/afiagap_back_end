<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ActiviteProjetModel extends Model
{
    use HasApiTokens, HasFactory, Notifiable,HasUuids;
    protected $table="t_activite_projets";
    protected $fillable = [
        "id",
        "projetid",
        "orgid",
    ];
    public function projet()
    {
        return $this->belongsTo(ProjetModel::class, 'projetid', 'id');
    }
    public function databeneficecible()
    {
        return $this->belongsTo(BeneficeCibleProjet::class, 'id', 'activiteid');
    }

    public function databeneficeatteint()
    {
        return $this->belongsTo(BeneficeAtteintProjet::class, 'id', 'activiteid');
    }

    public function dataconsultationexterne()
    {
        return $this->belongsTo(ConsultationExterneFosaProjet::class, 'id', 'activiteid');
    }

    public function dataconsultationcliniquemobile()
    {
        return $this->belongsTo(ConsultationCliniqueMobileProjet::class, 'id', 'activiteid');
    }

    public function data_organisation_make_rapport()
    {
        return $this->belongsTo(Organisation::class, 'org_make_repport', 'id');
    }

    public function data_organisation_mise_en_oeuvre()
    {
        return $this->belongsTo(Organisation::class, 'org_make_oeuvre', 'id');
    }

    public function struturesantes()
    {
        return $this->belongsToMany(structureSanteModel::class, 't_rayon_action_projet', 'projetid', 'structureid');
    }
    public function autresinfoprojet()
    {
        return $this->belongsTo(AutreInfoProjets::class, 'id', 'activiteid');
    }
    public function typeimpact()
    {
        return $this->belongsToMany(TypeImpactModel::class, 't_reponse_indicateur_projet', 'activiteid', 'typeimpactid');
    }

    public function indicataire()
    {
        return $this->belongsToMany(indicateur::class, 't_activite_indicateur', 'activiteid','indicateurid')->
        withPivot(['indicateurid'])->as('pci');
    }



    public function datatypeimpact()
    {
        return $this->hasMany(IndicateurProjetModel::class, 'activiteid', 'id');
    }

    public function typeprojet()
    {
        return $this->belongsTo(TypeProjet::class, 'typeprojetid', 'id');
    }
}
