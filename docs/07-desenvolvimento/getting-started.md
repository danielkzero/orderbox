# Ambiente de Desenvolvimento

## Stack

- PHP 8.2 ou superior;
- Laravel 12 com Blade;
- Laravel Sanctum para autenticação Mobile;
- MariaDB;
- Ionic para o aplicativo Mobile.

---

## Aplicação Web

A aplicação Laravel está em:

```text
apps/web
```

Instalação:

```text
cd apps/web
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Configure o MariaDB no `.env` sem versionar credenciais.

Recomenda-se um usuário dedicado com acesso apenas aos bancos:

- `orderbox`;
- `orderbox_testing`.

Não utilize a conta `root` fora do ambiente local.

---

## Banco e Seed

```text
php artisan migrate
php artisan db:seed
```

O seed administrativo só é executado quando `ADMIN_EMAIL` e `ADMIN_PASSWORD` estiverem definidos no `.env`.

---

## Execução

```text
composer run dev
```

O comando inicia o servidor Laravel, o processamento da fila e o Vite.

Em Linux ou macOS, os logs podem ser acompanhados em outro terminal:

```text
composer run dev:logs
```

No Windows, Laravel Pail não funciona porque depende da extensão `pcntl`. Utilize o PowerShell:

```powershell
Get-Content storage/logs/laravel.log -Wait
```

Endpoints operacionais:

- `GET /up`: health check do Laravel;
- `GET /api/v1/health`: processo da API;
- `GET /api/v1/ready`: conexão com o banco.

---

## Verificações

```text
vendor/bin/pint --test
php artisan test
npm run build
```

Os testes usam SQLite em memória. A validação final das migrations também deve ser executada no MariaDB antes de cada entrega.
