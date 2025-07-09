# PostPilot

Uma plataforma moderna para gerenciamento e agendamento de posts em redes sociais, construída com Laravel, Vue.js e Inertia.js.

## 🚀 Sobre o Projeto

PostPilot é uma aplicação web que permite aos usuários criar, agendar e gerenciar posts para diferentes redes sociais de forma intuitiva e eficiente. A plataforma oferece recursos de IA para geração de conteúdo, agendamento inteligente e análise de performance.

## ✨ Funcionalidades

- **Autenticação e Autorização**: Sistema completo de registro e login com Jetstream
- **Dashboard Intuitivo**: Interface moderna e responsiva
- **Gerenciamento de Posts**: Criação, edição e agendamento de posts
- **Templates de Prompts**: Sistema de templates para geração de conteúdo com IA
- **Sistema de Filas**: Processamento assíncrono de posts agendados
- **Categorização**: Organização de posts por categorias e tags
- **Relatórios**: Análise de performance e métricas
- **Sistema de Assinatura**: Controle de planos e limites
- **Verificação de Força de Senha**: Segurança aprimorada com zxcvbn

## 🛠️ Tecnologias Utilizadas

### Backend
- **Laravel 11**: Framework PHP robusto e elegante
- **Jetstream**: Scaffolding de autenticação
- **Inertia.js**: Aplicações SPA sem complexidade
- **Redis**: Sistema de filas e cache
- **MySQL**: Banco de dados principal

### Frontend
- **Vue.js 3**: Framework JavaScript progressivo
- **Inertia.js**: Bridge entre Laravel e Vue
- **Tailwind CSS**: Framework CSS utilitário

### DevOps
- **Docker**: Containerização da aplicação
- **Docker Compose**: Orquestração de containers

## 📋 Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- Docker e Docker Compose 
- Redis
- MySQL 8.0+

## 🚀 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/postpilot.git
cd postpilot
```

### 2. Instale as dependências PHP
```bash
composer install
```

### 3. Instale as dependências JavaScript
```bash
npm install
```

### 4. Configure o ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure o banco de dados
Edite o arquivo `.env` com suas configurações de banco de dados:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=postpilot
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Execute as migrações
```bash
php artisan migrate
```

### 7. Execute os seeders (opcional)
```bash
php artisan db:seed
```

### 8. Configure o Redis
Certifique-se de que o Redis está rodando e configurado no `.env`:
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 9. Compile os assets
```bash
npm run build
```

### 10. Inicie o servidor
```bash
php artisan serve
```

## 🐳 Usando Docker

### Com Docker Compose
```bash
git clone https://github.com/seu-usuario/postpilot.git
cd postpilot

docker-compose up -d

docker-compose exec app composer install
docker-compose exec app npm install

docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

docker-compose exec app php artisan migrate

docker-compose exec app npm run build
```

## 🔧 Configuração

### Variáveis de Ambiente Importantes

```env
# Configurações do App
APP_NAME=PostPilot
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=postpilot
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail (para notificações)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🎯 Uso

### Comandos Artisan Úteis

```bash
# Verificar posts agendados
php artisan posts:check-scheduled

# Processar posts na fila
php artisan posts:process-queued

# Configurar fila Redis
php artisan queue:setup-redis

# Limpar cache
php artisan cache:clear

# Otimizar aplicação
php artisan optimize
```

### Filas e Jobs

O PostPilot utiliza um sistema de filas para processar posts agendados:

```bash
# Iniciar worker das filas
php artisan queue:work

# Monitorar filas
php artisan queue:monitor
```

## 📁 Estrutura do Projeto

```
postpilot/
├── app/
│   ├── Actions/          # Ações Fortify e Jetstream
│   ├── Console/          # Comandos Artisan
│   ├── Events/           # Eventos da aplicação
│   ├── Http/
│   │   ├── Controllers/  # Controladores
│   │   └── Middleware/   # Middlewares
│   ├── Jobs/             # Jobs para filas
│   ├── Models/           # Modelos Eloquent
│   ├── Policies/         # Políticas de autorização
│   ├── Providers/        # Service Providers
│   └── Services/         # Serviços da aplicação
├── database/
│   ├── migrations/       # Migrações do banco
│   ├── seeders/          # Seeders
│   └── factories/        # Factories para testes
├── resources/
│   ├── js/
│   │   ├── Components/   # Componentes Vue
│   │   ├── Layouts/      # Layouts Vue
│   │   └── Pages/        # Páginas Vue
│   └── views/            # Views Blade
├── routes/               # Rotas da aplicação
└── storage/              # Arquivos de storage
```

## 🧪 Testes

```bash
php artisan test

php artisan test --filter=PostTest

php artisan test --coverage
```

## 📊 Monitoramento

### Logs
Os logs da aplicação estão em `storage/logs/`

### Cache
```bash
php artisan cache:clear

php artisan config:clear

php artisan route:clear
```

## 🔒 Segurança

- Autenticação com Jetstream
- Verificação de força de senha com zxcvbn
- Middleware de autenticação
- Validação CSRF
- Sanitização de inputs
- Controle de acesso baseado em políticas

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

**PostPilot** - Simplificando o gerenciamento de redes sociais 🚀
