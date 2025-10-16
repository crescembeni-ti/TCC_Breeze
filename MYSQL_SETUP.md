# Guia de Configuração MySQL para o Mapa de Árvores de Paracambi-RJ

Este guia explica como configurar o projeto Laravel para usar MySQL em vez de SQLite, permitindo que você gerencie o banco de dados através do MySQL Workbench.

## Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- **MySQL Server** (versão 8.0 ou superior recomendada)
- **MySQL Workbench** (para gerenciamento visual do banco de dados)
- **PHP com extensão MySQL** (pdo_mysql)

## Passo 1: Instalar o MySQL Server

Se você ainda não tem o MySQL instalado, siga as instruções abaixo de acordo com seu sistema operacional:

### Windows

1. Baixe o MySQL Installer em: https://dev.mysql.com/downloads/installer/
2. Execute o instalador e escolha "Developer Default"
3. Durante a instalação, defina uma senha para o usuário `root`
4. Anote a senha, pois você precisará dela posteriormente

### Linux (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

### macOS

```bash
brew install mysql
brew services start mysql
mysql_secure_installation
```

## Passo 2: Criar o Banco de Dados

Você pode criar o banco de dados de duas formas:

### Opção A: Usando MySQL Workbench (Recomendado)

1. Abra o **MySQL Workbench**
2. Conecte-se ao servidor MySQL local (geralmente `localhost:3306`)
3. Use o usuário `root` e a senha que você definiu durante a instalação
4. Abra o arquivo `setup_mysql.sql` que está na raiz do projeto
5. Execute o script clicando no ícone de raio (⚡) ou pressionando `Ctrl+Shift+Enter`
6. O banco de dados `tree_map_paracambi` será criado automaticamente

### Opção B: Usando Linha de Comando

```bash
mysql -u root -p < setup_mysql.sql
```

Digite a senha do MySQL quando solicitado.

## Passo 3: Configurar o Arquivo .env

O arquivo `.env` do projeto já está configurado para usar MySQL. Verifique se as seguintes linhas estão corretas:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tree_map_paracambi
DB_USERNAME=root
DB_PASSWORD=
```

**Importante:** Se você definiu uma senha para o usuário `root` do MySQL, adicione-a na linha `DB_PASSWORD=`:

```env
DB_PASSWORD=sua_senha_aqui
```

## Passo 4: Instalar Dependências PHP

Certifique-se de que a extensão MySQL do PHP está instalada:

### Linux (Ubuntu/Debian)

```bash
sudo apt install php-mysql
```

### Windows

A extensão geralmente já vem habilitada. Caso contrário, edite o arquivo `php.ini` e descomente a linha:

```ini
extension=pdo_mysql
```

## Passo 5: Executar as Migrações

Com o banco de dados criado e configurado, execute as migrações do Laravel para criar as tabelas:

```bash
cd /caminho/do/projeto
php artisan migrate
```

Se quiser popular o banco com dados de exemplo:

```bash
php artisan db:seed
```

Ou execute ambos de uma vez:

```bash
php artisan migrate --seed
```

## Passo 6: Verificar no MySQL Workbench

Após executar as migrações, você pode verificar as tabelas criadas no MySQL Workbench:

1. Abra o MySQL Workbench
2. Conecte-se ao servidor
3. No painel esquerdo, expanda "Schemas"
4. Você verá o banco `tree_map_paracambi` com as seguintes tabelas:
   - `users` - Usuários do sistema
   - `species` - Espécies de árvores
   - `trees` - Árvores cadastradas
   - `activities` - Atividades de manutenção
   - `reports` - Denúncias enviadas
   - `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `sessions` - Tabelas do sistema Laravel

## Estrutura das Tabelas Principais

### Tabela `species` (Espécies)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | BIGINT | Identificador único |
| name | VARCHAR(255) | Nome comum da espécie |
| scientific_name | VARCHAR(255) | Nome científico |
| description | TEXT | Descrição da espécie |
| color_code | VARCHAR(7) | Código de cor hexadecimal para o mapa |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

### Tabela `trees` (Árvores)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | BIGINT | Identificador único |
| species_id | BIGINT | Referência à espécie |
| user_id | BIGINT | Usuário que cadastrou |
| latitude | DECIMAL(10,7) | Latitude da localização |
| longitude | DECIMAL(10,7) | Longitude da localização |
| trunk_diameter | DECIMAL(5,2) | Diâmetro do tronco em cm |
| health_status | VARCHAR(50) | Status de saúde (good, fair, poor) |
| planted_at | TIMESTAMP | Data de plantio |
| address | VARCHAR(255) | Endereço |
| photo | VARCHAR(255) | URL ou caminho da foto |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

### Tabela `activities` (Atividades)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | BIGINT | Identificador único |
| tree_id | BIGINT | Referência à árvore |
| user_id | BIGINT | Usuário que realizou |
| activity_type | VARCHAR(50) | Tipo de atividade |
| description | TEXT | Descrição da atividade |
| performed_at | TIMESTAMP | Data de realização |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

### Tabela `reports` (Denúncias)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | BIGINT | Identificador único |
| user_id | BIGINT | Usuário que fez a denúncia |
| subject | VARCHAR(255) | Assunto da denúncia |
| message | TEXT | Mensagem da denúncia |
| status | VARCHAR(50) | Status (pending, in_progress, resolved) |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

## Operações Comuns no MySQL Workbench

### Visualizar Dados

```sql
USE tree_map_paracambi;

-- Ver todas as árvores
SELECT * FROM trees;

-- Ver árvores com suas espécies
SELECT t.id, s.name AS species, t.address, t.trunk_diameter, t.health_status
FROM trees t
JOIN species s ON t.species_id = s.id;

-- Ver atividades recentes
SELECT a.*, t.address, u.name AS user_name
FROM activities a
JOIN trees t ON a.tree_id = t.id
JOIN users u ON a.user_id = u.id
ORDER BY a.performed_at DESC
LIMIT 10;

-- Ver denúncias pendentes
SELECT r.*, u.name AS reporter_name
FROM reports r
JOIN users u ON r.user_id = u.id
WHERE r.status = 'pending'
ORDER BY r.created_at DESC;
```

### Fazer Backup do Banco de Dados

No MySQL Workbench:

1. Vá em **Server** > **Data Export**
2. Selecione o schema `tree_map_paracambi`
3. Escolha as tabelas que deseja exportar
4. Selecione "Export to Self-Contained File"
5. Escolha um local para salvar o arquivo `.sql`
6. Clique em **Start Export**

### Restaurar Backup

1. Vá em **Server** > **Data Import**
2. Selecione "Import from Self-Contained File"
3. Escolha o arquivo `.sql` do backup
4. Selecione o schema de destino `tree_map_paracambi`
5. Clique em **Start Import**

## Solução de Problemas

### Erro: "Access denied for user 'root'@'localhost'"

**Solução:** Verifique se a senha no arquivo `.env` está correta. Se você não definiu senha durante a instalação, deixe o campo vazio:

```env
DB_PASSWORD=
```

### Erro: "SQLSTATE[HY000] [2002] Connection refused"

**Solução:** Certifique-se de que o MySQL Server está rodando:

**Linux:**
```bash
sudo systemctl status mysql
sudo systemctl start mysql
```

**Windows:** Verifique no "Gerenciador de Tarefas" > "Serviços" se o MySQL está em execução.

**macOS:**
```bash
brew services list
brew services start mysql
```

### Erro: "Database 'tree_map_paracambi' doesn't exist"

**Solução:** Execute o script `setup_mysql.sql` para criar o banco de dados.

### Erro: "PDO driver for MySQL not found"

**Solução:** Instale a extensão PHP MySQL:

```bash
# Linux
sudo apt install php-mysql
sudo systemctl restart apache2

# Ou se estiver usando PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Diferenças entre SQLite e MySQL

O projeto foi originalmente configurado para usar SQLite, mas agora está totalmente compatível com MySQL. As principais diferenças são:

| Aspecto | SQLite | MySQL |
|---------|--------|-------|
| Arquivo | Único arquivo `.sqlite` | Servidor de banco de dados |
| Gerenciamento | Arquivo local | MySQL Workbench, phpMyAdmin |
| Performance | Adequado para desenvolvimento | Melhor para produção |
| Concorrência | Limitada | Suporta múltiplos usuários |
| Backup | Copiar arquivo | Export/Import via Workbench |

## Recursos Adicionais

- [Documentação Laravel - Database](https://laravel.com/docs/database)
- [MySQL Workbench Manual](https://dev.mysql.com/doc/workbench/en/)
- [MySQL 8.0 Reference Manual](https://dev.mysql.com/doc/refman/8.0/en/)

## Suporte

Se você encontrar problemas durante a configuração, verifique:

1. Se o MySQL Server está rodando
2. Se as credenciais no `.env` estão corretas
3. Se a extensão PHP MySQL está instalada
4. Se o banco de dados foi criado corretamente

Para mais informações, consulte a documentação oficial do Laravel e do MySQL.

