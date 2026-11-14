<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Violação de uma regra de negócio que não é erro de validação de campo
 * (ex.: excluir um registro que ainda está em uso).
 *
 * Lançada pelos Services; o Laravel chama render() e devolve o JSON.
 */
class RegraDeNegocioException extends Exception
{
    public function __construct(string $message, private readonly int $status = 409)
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->status);
    }
}
