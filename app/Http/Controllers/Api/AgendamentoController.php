<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgendamentoRequest;
use App\Models\Agendamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AgendamentoController extends Controller
{
    private const LIMITE_POR_PERIODO = 5;

    private const TRANSICOES = [
        'pendente' => ['confirmado', 'cancelado'],
        'confirmado' => ['concluido', 'cancelado'],
        'concluido' => [],
        'cancelado' => [],
    ];

    private const RELACOES = ['veiculo', 'servico', 'mecanico'];

    public function index(Request $request)
    {
        $query = Agendamento::with(self::RELACOES)->orderBy('data_agendamento');

        if (! $request->user()->is_admin) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->paginate(10));
    }

    public function store(AgendamentoRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->is_admin) {
            unset($data['mecanico_id']);
        }

        $this->validarRegras(
            $data['veiculo_id'],
            $data['data_agendamento'],
            $data['periodo'],
            $data['mecanico_id'] ?? null
        );

        $agendamento = $request->user()->agendamentos()->create($data + ['status' => 'pendente']);

        return response()->json($agendamento->load(self::RELACOES), 201);
    }

    public function show(Agendamento $agendamento)
    {
        Gate::authorize('view', $agendamento);

        return response()->json($agendamento->load(self::RELACOES));
    }

    public function update(AgendamentoRequest $request, Agendamento $agendamento)
    {
        Gate::authorize('update', $agendamento);

        $user = $request->user();
        $data = $request->validated();

        if (in_array($agendamento->status, ['concluido', 'cancelado'], true)) {
            throw ValidationException::withMessages([
                'status' => "Agendamento {$agendamento->status} não pode ser alterado.",
            ]);
        }

        if (! $user->is_admin) {
            unset($data['mecanico_id']);

            $cancelando = ($data['status'] ?? null) === 'cancelado';

            if (isset($data['status']) && ! $cancelando) {
                throw ValidationException::withMessages(['status' => 'Você só pode cancelar o agendamento.']);
            }

            if (! $cancelando && $agendamento->status !== 'pendente') {
                throw ValidationException::withMessages([
                    'status' => 'Só é possível reagendar enquanto o agendamento estiver pendente.',
                ]);
            }
        }

        if (isset($data['status']) && $data['status'] !== $agendamento->status) {
            if (! in_array($data['status'], self::TRANSICOES[$agendamento->status], true)) {
                throw ValidationException::withMessages([
                    'status' => "Não é possível mudar de {$agendamento->status} para {$data['status']}.",
                ]);
            }
        }

        if (($data['status'] ?? $agendamento->status) !== 'cancelado') {
            $this->validarRegras(
                $agendamento->veiculo_id,
                $data['data_agendamento'] ?? $agendamento->data_agendamento->toDateString(),
                $data['periodo'] ?? $agendamento->periodo,
                array_key_exists('mecanico_id', $data) ? $data['mecanico_id'] : $agendamento->mecanico_id,
                $agendamento->id
            );
        }

        $agendamento->update($data);

        return response()->json($agendamento->fresh(self::RELACOES));
    }

    public function destroy(Agendamento $agendamento)
    {
        Gate::authorize('delete', $agendamento);

        if ($agendamento->status === 'concluido') {
            return response()->json(['message' => 'Agendamentos concluídos não podem ser excluídos.'], 409);
        }

        $agendamento->delete();

        return response()->noContent();
    }

    private function validarRegras(int $veiculoId, string $data, string $periodo, ?int $mecanicoId, ?int $ignorarId = null): void
    {
        $dia = Carbon::parse($data)->toDateString();

        $mesmoPeriodo = Agendamento::where('data_agendamento', $dia)
            ->where('periodo', $periodo)
            ->where('status', '!=', 'cancelado')
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId));

        if ((clone $mesmoPeriodo)->where('veiculo_id', $veiculoId)->exists()) {
            throw ValidationException::withMessages([
                'veiculo_id' => 'Este veículo já possui agendamento neste dia e período.',
            ]);
        }

        if ((clone $mesmoPeriodo)->count() >= self::LIMITE_POR_PERIODO) {
            throw ValidationException::withMessages([
                'periodo' => 'Este período está lotado para a data escolhida.',
            ]);
        }

        if ($mecanicoId && (clone $mesmoPeriodo)->where('mecanico_id', $mecanicoId)->exists()) {
            throw ValidationException::withMessages([
                'mecanico_id' => 'O mecânico já possui agendamento neste dia e período.',
            ]);
        }
    }
}
