<?php

namespace Tests\Unit;

use App\Services\YouTube;
use App\Support\Duree;
use PHPUnit\Framework\TestCase;

class DureeTest extends TestCase
{
    public function test_saisie_de_duree(): void
    {
        $this->assertSame(5530, Duree::versSecondes('1:32:10'));
        $this->assertSame(3130, Duree::versSecondes('52:10'));
        $this->assertSame(5700, Duree::versSecondes('95'));
        $this->assertSame(5700, Duree::versSecondes('1h35'));
        $this->assertNull(Duree::versSecondes(''));
        $this->assertNull(Duree::versSecondes('bientôt'));
    }

    public function test_affichage_de_duree(): void
    {
        $this->assertSame('26 min', Duree::court(1560));
        $this->assertSame('1 h 30', Duree::court(5400));
        $this->assertSame('2 h', Duree::court(7200));
    }

    public function test_identifiant_youtube(): void
    {
        $this->assertSame('dQw4w9WgXcQ', YouTube::idDepuisUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=12'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::idDepuisUrl('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::idDepuisUrl('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::idDepuisUrl('dQw4w9WgXcQ'));
        $this->assertNull(YouTube::idDepuisUrl('https://example.com'));
    }
}
