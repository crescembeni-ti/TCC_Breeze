<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;

    // Define a tabela, se o nome for diferente do padrão
    protected $table = 'admin_logs';

    // Define os campos que podem ser preenchidos
    protected $fillable = [
        'user_id',
        'action',
        'description',
    ];

    /**
     * Define o relacionamento: Um Log pertence a um Usuário (Admin).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}