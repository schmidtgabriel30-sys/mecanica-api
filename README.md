# Mecânica API — Aurum Automotive

API REST em **Laravel 13** para a oficina Aurum Automotive. Clientes cadastram seus veículos e agendam serviços. O administrador gerencia o catálogo de serviços e a equipe de mecânicos, e conduz cada agendamento até a conclusão.

Projeto integrado das disciplinas de **Programação Orientada a Objetos para Web**, **Paradigmas de Linguagem de Programação** e **Programação para Dispositivos Móveis** (o app Flutter que consome esta API está em [aurum_lib](https://github.com/schmidtgabriel30-sys/aurum_lib)).

**Aluno:** _[seu nome completo aqui]_

## Recursos

| Recurso | Rotas | Quem pode |
|---|---|---|
| Autenticação | `POST /register`, `POST /login`, `POST /logout`, `GET /user` | público / logado |
| Serviços | `apiResource /servicos` | listar e ver: público · criar, editar e excluir: admin |
| Mecânicos | `apiResource /mecanicos` | listar e ver: logado · criar, editar e excluir: admin |
| Veículos | `apiResource /veiculos` | cada cliente, só os próprios |
| Agendamentos | `apiResource /agendamentos` | cliente: os próprios · admin: todos |

Todas as rotas ficam sob o prefixo `/api`. As rotas protegidas exigem o cabeçalho `Authorization: Bearer {token}` (Laravel Sanctum).

## Regras de negócio

**Agendamentos**
- O status segue o fluxo **pendente → confirmado → concluído**. Pendentes e confirmados também podem ir para **cancelado**.
- Concluídos e cancelados não podem mais ser alterados. Concluídos não podem ser excluídos.
- O **cliente** só pode reagendar (data e período) enquanto o agendamento estiver pendente, ou cancelá-lo. Não escolhe mecânico nem confirma.
- O **admin** atribui o mecânico e muda o status.
- No mesmo dia e período: o veículo só pode ter um agendamento, o mecânico só pode atender um, e há no máximo **5** agendamentos.
- A data não pode estar no passado. O período é `manha`, `tarde` ou `noite`.
- O veículo precisa pertencer ao cliente, e o serviço precisa estar ativo. Ambos não mudam na edição.

**Outros recursos**
- **Veículos:** a placa é única e normalizada (`abc-1d23` → `ABC1D23`). Um veículo com agendamento em aberto não pode ser excluído (409).
- **Serviços:** um serviço já usado em agendamentos não pode ser excluído (409); nesse caso, deve ser desativado com `ativo: false`.
- **Listagens:** serviços e mecânicos inativos não aparecem.

## Arquitetura

A requisição passa por camadas com responsabilidades separadas:

```
Rota → FormRequest → Controller → Service → Repository (interface) → Eloquent Model → Banco
          valida       autoriza     regras      acesso aos dados
                       (Policy)     de negócio
```

```
app/
├── Exceptions/RegraDeNegocioException.php   # erro de regra → JSON com status HTTP (ex.: 409)
├── Http/
│   ├── Controllers/       # só HTTP: recebe, autoriza com Gate/Policy, chama o Service, responde
│   └── Requests/          # validação dos dados de entrada
├── Models/                # entidades Eloquent, relacionamentos e constantes de domínio
├── Policies/              # quem pode ver/alterar cada registro
├── Repositories/
│   ├── Contracts/         # interfaces (o que o Service pode pedir ao banco)
│   └── Eloquent/          # implementações; BaseRepository reúne o CRUD comum
├── Services/              # regras de negócio (AuthService, AgendamentoService, ...)
└── Providers/RepositoryServiceProvider.php  # liga cada interface à sua implementação
```

**Por que assim?**
- **Controllers** finos: não sabem como os dados são salvos nem quais são as regras.
- **Services** concentram as regras e dependem só das **interfaces** dos repositórios (inversão de dependência). Por isso podem ser testados com um repositório falso, como em `tests/Unit/AgendamentoServiceTest.php`.
- **Repositories** isolam as consultas. O `BaseRepository` evita repetir `create`, `update` e `delete`, e o `AgendamentoRepository` reaproveita uma consulta base (`ativosNoPeriodo`) nas três verificações de conflito de agenda.

## Como rodar

Pré-requisitos: PHP 8.4+ e Composer. Docker é opcional, só para usar MySQL.

1. Instale as dependências:

   ```bash
   composer install
   ```

2. Crie o arquivo de ambiente e gere a chave da aplicação:

   ```bash
   cp .env.example .env
   ```

   ```bash
   php artisan key:generate
   ```

3. Escolha o banco:
   - **SQLite** (padrão, sem instalar nada):

     ```bash
     touch database/database.sqlite
     ```

   - **MySQL via Docker:** suba o banco com `docker compose up -d` e, no `.env`, use `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3309`, `DB_DATABASE=mecanica_db`, `DB_USERNAME=mecanica_user` e `DB_PASSWORD=root`. O Adminer fica em http://localhost:8041.

4. Crie as tabelas e os dados de exemplo:

   ```bash
   php artisan migrate --seed
   ```

5. Inicie o servidor (http://localhost:8000):

   ```bash
   php artisan serve
   ```

### Usuários criados pelo seeder

| Perfil | E-mail | Senha |
|---|---|---|
| Admin | `admin@aurum.com` | `12345678` |
| Cliente | `cliente@aurum.com` | `12345678` |

O seeder também cria 4 serviços, 3 mecânicos, 2 veículos do cliente e 2 agendamentos.

## Testando a API

### rest.http

O arquivo [`rest.http`](rest.http) tem 26 requisições, em ordem, cobrindo login, CRUD de todos os recursos, segurança (401/403) e as regras de negócio (409/422). No VS Code, use a extensão **REST Client**: rode o item 01 e o 02 (logins) e depois os demais.

### Testes automatizados

```bash
php artisan test
```

São 38 testes:
- `tests/Feature`: autenticação, CRUD, permissões e regras de negócio, via HTTP;
- `tests/Unit`: regras do `AgendamentoService` com o repositório substituído por um mock.

## Banco de dados

| Tabela | Campos principais | Relações |
|---|---|---|
| `users` | name, email, password, is_admin | 1:N veículos, 1:N agendamentos |
| `veiculos` | marca, modelo, placa (única), ano | N:1 user · 1:N agendamentos |
| `servicos` | nome, descricao, preco, duracao_minutos, ativo | 1:N agendamentos |
| `mecanicos` | nome, especialidade, telefone, ativo | 1:N agendamentos |
| `agendamentos` | data_agendamento, periodo, status, observacoes | N:1 user, veículo, serviço e mecânico (opcional) |

Cada model tem uma **factory**, usada pelo seeder e pelos testes.
