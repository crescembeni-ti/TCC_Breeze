<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Permite que o Seeder use Status::create(['name' => ...])
    ];

    /**
     * Relação: Um Status pode ter muitos Contatos.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}