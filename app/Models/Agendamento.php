<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agendamento extends Model
{
    use HasFactory;

    public const PERIODOS = ['manha', 'tarde', 'noite'];

    public const STATUS = ['pendente', 'confirmado', 'concluido', 'cancelado'];

    protected $fillable = [
        'user_id',
        'veiculo_id',
        'servico_id',
        'mecanico_id',
        'data_agendamento',
        'periodo',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_agendamento' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function mecanico(): BelongsTo
    {
        return $this->belongsTo(Mecanico::class);
    }
}
