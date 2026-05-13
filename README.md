# Sistema de avaliação de vendedores

Sistema completo para avaliação via QR Code com HTML, CSS, JavaScript, PHP, MySQL e Docker, preparado para rodar localmente e migrar para Railway, Render ou Fly.io.

## Estrutura

```text
.
├── app/                    # Código PHP sensível fora da raiz pública
├── public/                 # DocumentRoot do Apache
│   ├── admin/              # Login e dashboard administrativo
│   ├── actions/            # Endpoints POST
│   ├── css/
│   ├── js/
│   └── images/
├── database/
│   ├── migrations/         # Migrações versionadas
│   ├── seeds/              # Dados de exemplo
│   └── init.sql            # Script SQL completo/manual
├── docker/
│   ├── apache/
│   └── entrypoint.sh
├── Dockerfile
├── docker-compose.yml
├── render.yaml
├── railway.json
├── fly.toml
├── .env                    # Local, não subir para o GitHub
└── .env.example            # Modelo de variáveis para deploy
```

## Rodar localmente

Abra o Docker Desktop e rode:

```bash
docker compose up --build -d
```

Acesse:

```text
Site:  http://localhost:8080
Admin: http://localhost:8080/admin/login.php
```

Login local inicial:

```text
Usuário: admin
Senha: password
```

O banco local fica em:

```text
Host: localhost
Porta: 3307
Banco: seller_reviews
Usuário: seller_user
Senha: seller_pass
```

## Variáveis de ambiente

Use `.env` apenas localmente. No GitHub/cloud, configure as variáveis no painel da plataforma.

Principais variáveis:

```text
APP_ENV=production
APP_URL=https://seu-dominio.com
APP_KEY=uma-chave-longa-e-secreta
APP_TRUST_PROXY=true

DATABASE_URL=mysql://usuario:senha@host:3306/banco
RUN_MIGRATIONS=true
RUN_SEEDS=true
SEED_SAMPLE_REVIEWS=false

ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=hash_bcrypt_da_senha
```

Também é possível trocar `DATABASE_URL` por:

```text
DB_HOST=
DB_PORT=3306
DB_NAME=
DB_USER=
DB_PASS=
```

O sistema também reconhece variáveis comuns do Railway MySQL, como `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` e `MYSQL_URL`.

## Gerar senha admin criptografada

Com o projeto local rodando:

```bash
docker compose exec app php -r "echo password_hash('sua_senha_segura', PASSWORD_DEFAULT), PHP_EOL;"
```

Copie o hash gerado para `ADMIN_PASSWORD_HASH` na plataforma de deploy.

Em produção, se `RUN_SEEDS=true`, o sistema exige `ADMIN_PASSWORD_HASH`. Isso evita subir um admin com senha padrão.

## Banco e migrations

Na inicialização do container, `docker/entrypoint.sh` executa:

```bash
php /var/www/html/app/migrate.php
```

Isso aplica automaticamente os arquivos em:

```text
database/migrations/
```

Se `RUN_SEEDS=true`, ele também cria:

- vendedor Nicolas
- vendedor Gabriel
- admin inicial
- dados de exemplo somente se `SEED_SAMPLE_REVIEWS=true`

Para trocar de banco local para online, basta alterar `DATABASE_URL` ou as variáveis `DB_*`.

## Deploy via GitHub

1. Crie um repositório no GitHub.
2. Suba o projeto:

```bash
git add .
git commit -m "Sistema de avaliacao de vendedores"
git branch -M main
git remote add origin https://github.com/seu-usuario/seu-repositorio.git
git push -u origin main
```

3. Conecte esse repositório no Railway, Render ou Fly.io.
4. Configure as variáveis de ambiente no painel da plataforma.
5. Ative auto deploy pelo branch `main`.

O workflow `.github/workflows/docker-build.yml` valida o build Docker em pushes e pull requests.

## Publicar no Railway

1. No Railway, clique em **New Project**.
2. Escolha **Deploy from GitHub repo**.
3. Selecione este repositório.
4. Adicione um banco MySQL pelo Railway ou use um MySQL externo.
5. Configure variáveis:

```text
APP_ENV=production
APP_URL=https://sua-url.railway.app
APP_KEY=gere-uma-chave-longa
APP_TRUST_PROXY=true
RUN_MIGRATIONS=true
RUN_SEEDS=true
SEED_SAMPLE_REVIEWS=false
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=hash_bcrypt
```

Se usar MySQL do Railway, você pode usar as variáveis `MYSQL*` que ele cria automaticamente. Se preferir, crie:

```text
DATABASE_URL=${{ MySQL.MYSQL_URL }}
```

Depois do primeiro deploy e login confirmado, você pode deixar `RUN_SEEDS=false` para não reprocessar seeds.

## Publicar no Render

O projeto inclui `render.yaml`.

1. No Render, clique em **New +**.
2. Escolha **Blueprint** ou **Web Service**.
3. Conecte o repositório GitHub.
4. Use ambiente **Docker**.
5. Configure um MySQL externo, pois Render geralmente não fornece MySQL nativo gerenciado.
6. Configure:

```text
APP_ENV=production
APP_URL=https://seu-app.onrender.com
APP_KEY=gere-uma-chave-longa
APP_TRUST_PROXY=true
DATABASE_URL=mysql://usuario:senha@host:3306/banco
RUN_MIGRATIONS=true
RUN_SEEDS=true
SEED_SAMPLE_REVIEWS=false
ADMIN_PASSWORD_HASH=hash_bcrypt
```

O health check configurado é:

```text
/health.php
```

## Publicar no Fly.io

O projeto inclui `fly.toml`.

1. Instale e faça login no Fly CLI.
2. Ajuste o nome do app em `fly.toml` se quiser.
3. Crie o app:

```bash
fly launch --no-deploy
```

4. Configure secrets:

```bash
fly secrets set APP_KEY="gere-uma-chave-longa"
fly secrets set APP_URL="https://seu-app.fly.dev"
fly secrets set DATABASE_URL="mysql://usuario:senha@host:3306/banco"
fly secrets set ADMIN_PASSWORD_HASH="hash_bcrypt"
fly secrets set RUN_SEEDS="true"
```

5. Faça deploy:

```bash
fly deploy
```

Depois do primeiro deploy:

```bash
fly secrets set RUN_SEEDS="false"
```

## Atualizar domínio

Quando configurar domínio próprio:

1. Aponte o DNS para a plataforma.
2. Ative HTTPS no painel do Railway, Render ou Fly.io.
3. Atualize:

```text
APP_URL=https://seudominio.com
APP_TRUST_PROXY=true
```

4. Atualize o QR Code para o novo domínio.

## Trocar fotos dos vendedores

Coloque as fotos em:

```text
public/images/
```

Exemplo:

```text
public/images/nicolas.jpg
public/images/gabriel.jpg
```

Atualize no banco:

```sql
UPDATE vendors
SET photo_path = 'images/nicolas.jpg'
WHERE slug = 'nicolas';

UPDATE vendors
SET photo_path = 'images/gabriel.jpg'
WHERE slug = 'gabriel';
```

## Adicionar novos vendedores

```sql
INSERT INTO vendors (name, slug, photo_path, is_active, display_order)
VALUES ('Mariana', 'mariana', 'images/mariana.jpg', 1, 3);
```

A página inicial lista automaticamente todos os vendedores ativos.

## Segurança

- Senhas admin com `password_hash` e `password_verify`.
- Variáveis sensíveis fora do código, via `.env` ou painel da hospedagem.
- `.env` ignorado pelo Git.
- Sessões com `HttpOnly`, `SameSite=Lax` e `Secure` quando HTTPS está ativo.
- Suporte a proxy HTTPS com `APP_TRUST_PROXY=true`.
- CSRF em formulários.
- PDO prepared statements.
- Honeypot invisível contra bots.
- Cooldown anti-spam por cookie, sessão, token do cliente e IP com hash.
- Código sensível em `app/`, fora do DocumentRoot público.

## Comandos úteis

Ver logs:

```bash
docker compose logs -f app
```

Recriar banco local:

```bash
docker compose down -v
docker compose up --build -d
```

Acessar MySQL local:

```bash
docker compose exec db mysql -u seller_user -pseller_pass seller_reviews
```

Executar migrations manualmente:

```bash
docker compose exec app php /var/www/html/app/migrate.php
```

