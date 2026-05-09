# 🛒 Weale Store Back

API backend para e-commerce construída com **PHP 8.2 + Slim Framework 4**, seguindo os princípios de **DDD (Domain-Driven Design)** e **TDD (Test-Driven Development)**.

---

## 🏗️ Arquitetura — DDD em Camadas

```
src/
├── Domain/                         ← Núcleo do negócio (puro PHP, sem frameworks)
│   ├── Product/
│   │   ├── Product.php             ← Aggregate Root
│   │   ├── ProductRepositoryInterface.php
│   │   ├── Events/
│   │   │   └── ProductCreatedEvent.php
│   │   └── ValueObjects/
│   │       ├── ProductId.php
│   │       └── Money.php
│   └── Shared/
│       ├── Events/DomainEventInterface.php
│       └── Exceptions/NotFoundException.php
│
├── Application/                    ← Casos de uso (orquestram o domínio)
│   └── Product/
│       ├── CreateProductUseCase.php
│       ├── CreateProductCommand.php
│       ├── GetProductUseCase.php
│       ├── ListProductsUseCase.php
│       ├── DeleteProductUseCase.php
│       └── ProductResponse.php
│
└── Infrastructure/                 ← Detalhes técnicos (DB, HTTP, etc)
    ├── Persistence/
    │   ├── Entities/ProductEntity.php
    │   ├── Migrations/Version*.php
    │   └── Repositories/DoctrineProductRepository.php
    └── Http/
        ├── Controllers/ProductController.php
        └── Middleware/
            ├── JsonMiddleware.php
            └── ErrorHandlerMiddleware.php
```

---

## 🚀 Quick Start

### 1. Clonar e configurar

```bash
cp .env.example .env
```

### 2. Subir com Docker

```bash
make build
```

### 3. Instalar dependências

```bash
make composer-install
```

### 4. Rodar migrations

```bash
make migrate
```

### 5. Testar a API

```bash
curl http://localhost:8080/health
```

---

## 🐳 Docker

| Serviço    | Container        | Porta         |
|------------|------------------|---------------|
| PHP-FPM    | weale_app        | interno       |
| Nginx      | weale_nginx      | 8080          |
| PostgreSQL | weale_db         | 5432          |
| PostgreSQL | weale_db_test    | 5433 (testes) |
| Redis      | weale_redis      | 6379          |

---

## 🧪 TDD — Rodando os Testes

```bash
# Todos os testes
make test

# Apenas unitários
make test-unit

# Com relatório de cobertura (HTML em coverage/)
make test-coverage
```

### Estrutura de testes

```
tests/
├── Unit/
│   ├── Domain/Product/
│   │   ├── ProductTest.php
│   │   ├── InMemoryProductRepository.php
│   │   └── ValueObjects/
│   │       ├── ProductIdTest.php
│   │       └── MoneyTest.php
│   └── Application/Product/
│       ├── CreateProductUseCaseTest.php
│       └── GetProductUseCaseTest.php
├── Integration/                    ← Testes contra banco real
└── Feature/                        ← Testes HTTP end-to-end
```

---

## 📡 Endpoints da API

```
GET    /health                    → Status da aplicação
GET    /api/v1/products           → Listar produtos (paginado)
POST   /api/v1/products           → Criar produto
GET    /api/v1/products/{id}      → Buscar produto
PUT    /api/v1/products/{id}      → Atualizar produto
DELETE /api/v1/products/{id}      → Deletar produto
```

### Exemplo — Criar produto

```bash
curl -X POST http://localhost:8080/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "iPhone 15",
    "description": "Smartphone Apple",
    "price": 5999.99,
    "stock": 50,
    "category": "smartphones"
  }'
```

---

## 🛠️ Comandos Úteis (Makefile)

```bash
make up              # Subir containers
make down            # Parar containers
make shell           # Entrar no container PHP
make migrate         # Rodar migrations
make migrate-create  # Criar nova migration
make migrate-status  # Ver status das migrations
make test            # Rodar testes
make lint            # Verificar estilo de código (PSR-12)
make analyse         # Análise estática (PHPStan level 8)
make db-cli          # Abrir CLI do PostgreSQL
make logs            # Ver logs da aplicação
```

---

## 🗄️ Migrations

As migrations usam **Doctrine Migrations 3.x** e ficam em:
`src/Infrastructure/Persistence/Migrations/`

```bash
# Criar nova migration
php vendor/bin/doctrine-migrations generate --configuration=config/migrations.php

# Executar pendentes
php vendor/bin/doctrine-migrations migrate --configuration=config/migrations.php

# Ver status
php vendor/bin/doctrine-migrations status --configuration=config/migrations.php
```

---

## 📦 Stack

- **PHP 8.2** + atributos nativos
- **Slim Framework 4.x** — micro-framework HTTP
- **PHP-DI 7** — injeção de dependência
- **Doctrine ORM 3 + Migrations 3** — persistência
- **PostgreSQL 16** — banco de dados
- **PHPUnit 11** — testes
- **PHPStan level 8** — análise estática
- **Mockery** — mocks para testes
- **Monolog** — logging
- **Ramsey UUID** — identificadores únicos
