<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agendamento extends Model
{
    use HasFactory;

    public const PERIODOS = ['manha', 'tarde', 'noite'];

    public const PENDENTE = 'pendente';

    public const CONFIRMADO = 'confirmado';

    public const EM_ATENDIMENTO = 'em_atendimento';

    public const CONCLUIDO = 'concluido';

    public const CANCELADO = 'cancelado';

    public const STATUS = [self::PENDENTE, self::CONFIRMADO, self::EM_ATENDIMENTO, self::CONCLUIDO, self::CANCELADO];

    /** Status que ainda ocupam a agenda (o serviço não terminou). */
    public const STATUS_EM_ABERTO = [self::PENDENTE, self::CONFIRMADO, self::EM_ATENDIMENTO];

    /** Status em que o agendamento não pode ser excluído. */
    public const STATUS_NAO_EXCLUIVEIS = [self::EM_ATENDIMENTO, self::CONCLUIDO];

    /** Status finais: o agendamento não pode mais ser alterado. */
    public const STATUS_FINAIS = [self::CONCLUIDO, self::CANCELADO];

    /** Para quais status cada status pode mudar. */
    public const TRANSICOES = [
        self::PENDENTE => [self::CONFIRMADO, self::CANCELADO],
        self::CONFIRMADO => [self::EM_ATENDIMENTO, self::CANCELADO],
        self::EM_ATENDIMENTO => [self::CONCLUIDO],
        self::CONCLUIDO => [],
        self::CANCELADO => [],
    ];

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
            'data_agendamento' => 'date:Y-m-d',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
