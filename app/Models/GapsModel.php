<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class GapsModel extends Model
{
    use HasApiTokens, HasFactory, Notifiable,HasUuids;
    protected $table="t_gaps_bloc1";
    protected $fillable = [
        'id',
        'title',
        'provinceid',
        'territoirid',
        'zoneid',
        'airid',
        'orgid',
        'children',
        'population',
        'pop_deplace',
        'pop_retourne',
        'pop_site',
        'criseid',
        'userid',
        'orguserid',
        'semaine_epid',
        'annee_epid',
        'etat_top',
        'status',
        'deleted',
        'dateadd'
    ];

    public function dataprovince()
    {
        return $this->belongsTo(province::class, 'provinceid','id');
    }

    public function dataterritoir()
    {
        return $this->belongsTo(territoir::class, 'territoirid','id');
    }
    public function datazone()
    {
        return $this->belongsTo(zonesante::class, 'zoneid','id');
    }

    public function dataaire()
    {
        return $this->belongsTo(airesante::class, 'airid','id');
    }

    public function datamaladie()
    {
        return $this->hasMany(MaladiedGap::class, 'gapid','id');
    }

    public function datamedicament()
    {
        return $this->hasMany(MedicamentRupture::class, 'gapid','id');
    }

    public function datapartenaire()
    {
        return $this->hasMany(PartenairePresntModel::class, 'gapid','id');
    }

    public function datatypepersonnel()
    {
        return $this->hasMany(PersonnelGap::class, 'gapid','id');
    }
     public function datapopulationEloigne()
    {
        return $this->hasMany(PopulationEloigne::class, 'gapid','id');
    }
     public function datastructure()
    {
        return $this->belongsTo(structureSanteModel::class, 'orgid','id');
    }
     public function datascorecard(){
           return $this->hasMany(ReponseModel::class, 'gapid','id');
    }

    public function suite1(){
        return $this->belongsTo(Bloc2Model::class, 'id','bloc1id');
    }

    public function allcrise()
    {
        return $this->hasMany(Crise_Gap::class, 'gapid','id');
    }

}
