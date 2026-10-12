<?php

namespace App\Models;

use App\Support\Duree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposition extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'tout_public' => 'boolean',
            'traitee_le' => 'datetime',
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function scopeEnAttente($q)
    {
        return $q->where('statut', 'en_attente');
    }

    public function vignette(): string
    {
        return $this->vignette_url ?: "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    public function dureeLabel(): string
    {
        return $this->duree_secondes ? Duree::court($this->duree_secondes) : 'durée à saisir';
    }
}
