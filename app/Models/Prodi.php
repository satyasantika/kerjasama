<?php

namespace App\Models;

use App\Enums\JenjangProdi;
use Database\Factories\ProdiFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    /** @use HasFactory<ProdiFactory> */
    use HasFactory, HasUuids;

    protected $table = 'prodi';

    protected $fillable = ['kode', 'nama', 'jenjang', 'aktif'];

    protected function casts(): array
    {
        return [
            'jenjang' => JenjangProdi::class,
            'aktif' => 'boolean',
        ];
    }

    public function ndtps(): HasMany
    {
        return $this->hasMany(ProdiNdtps::class);
    }
}
