<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bairro extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'bairros'; // Opcional se o nome for o plural de 'Bairro'

    /**
     * Indica se o model deve ter timestamps (created_at e updated_at).
     * Para uma tabela de bairros, geralmente não precisamos disso.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que podem ser preenchidos em massa (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        // Adicione aqui outras colunas que você queira poder criar/atualizar
        // usando Bairro::create() ou $bairro->update()
    ];

    /**
     * Define a relação: Um Bairro pode ter muitas Solicitações (Contacts).
     */
    public function contacts()
    {
        // 'bairro_id' é a chave estrangeira na tabela 'contacts'
        return $this->hasMany(Contact::class, 'bairro_id');
    }
}