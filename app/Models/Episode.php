<?php

namespace App\Models;

use App\Support\Duree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'masque' => 'boolean',
            'disponible_jusqu_au' => 'date',
            'verifie_le' => 'datetime',
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function vignetteUrl(): string
    {
        return $this->vignette ? asset($this->vignette) : "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    public function dureeLabel(): string
    {
        return Duree::court($this->duree_secondes);
    }

    public function etiquette(): string
    {
        return 'S'.$this->saison.' · É'.$this->numero;
    }
}
