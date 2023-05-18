<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class project extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'user_id', 'app_id'];

    public function services() {
        return $this->hasMany(Service::class, 'project_id');
    }

    public function containers() {
        return $this->hasMany(Container::class, 'project_id');
    }

    public function networks() {
        return $this->hasMany(Network::class, 'project_id');
    }

    public function volumes() {
        return $this->hasMany(Volume::class, 'project_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
