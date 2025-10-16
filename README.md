# Mapa de Árvores - Laravel com Breeze

Um sistema de mapeamento de árvores urbanas desenvolvido com **Laravel 12**, **Laravel Breeze** e **Leaflet.js**, inspirado no [NYC Tree Map](https://tree-map.nycgovparks.org/).

## 📋 Características

O projeto apresenta as seguintes funcionalidades principais:

- **Mapa Interativo**: Visualização de árvores em um mapa interativo usando Leaflet.js, com marcadores coloridos por espécie e tamanho baseado no diâmetro do tronco.
- **Autenticação**: Sistema completo de autenticação (login, registro, recuperação de senha) fornecido pelo Laravel Breeze.
- **Gestão de Árvores**: Cadastro e visualização de árvores com informações detalhadas sobre espécie, localização, saúde e histórico.
- **Registro de Atividades**: Acompanhamento de atividades de manutenção realizadas nas árvores (rega, poda, adubação, etc.).
- **Estatísticas**: Painel com estatísticas gerais sobre o número de árvores, atividades e espécies cadastradas.
- **Interface Responsiva**: Design moderno e responsivo usando Tailwind CSS.

## 🛠️ Tecnologias Utilizadas

O projeto foi desenvolvido utilizando as seguintes tecnologias e ferramentas:

- **Backend**: PHP 8.2 com Laravel 12
- **Autenticação**: Laravel Breeze
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **Mapa**: Leaflet.js com tiles do OpenStreetMap
- **Banco de Dados**: MySQL
- **Build Tool**: Vite

## 📦 Banco de Dados

O projeto está configurado para usar **MySQL** por padrão, permitindo gerenciamento através do MySQL Workbench. Para instruções detalhadas de configuração, consulte o arquivo `MYSQL_SETUP.md`.

O sistema utiliza as seguintes tabelas principais:

### Species (Espécies)
Armazena informações sobre as diferentes espécies de árvores, incluindo nome comum, nome científico, descrição e código de cor para o mapa.

### Trees (Árvores)
Registra cada árvore individual com sua localização (latitude/longitude), espécie, diâmetro do tronco, status de saúde, data de plantio e endereço.

### Activities (Atividades)
Mantém um histórico de todas as atividades de manutenção realizadas em cada árvore, incluindo tipo de atividade, descrição, data e usuário responsável.

### Users (Usuários)
Gerencia os usuários do sistema que podem registrar árvores e atividades (fornecido pelo Laravel Breeze).

## 🚀 Instalação

### Pré-requisitos

Certifique-se de ter instalado em seu sistema:

- PHP 8.2 ou superior
- Composer
- Node.js e NPM
- Extensões PHP: sqlite3, mbstring, xml, curl, zip, gd, bcmath, intl

### Passos para Instalação

1. **Clone ou copie o projeto**:
   ```bash
   cd /caminho/do/projeto
   ```

2. **Instale as dependências do Composer**:
   ```bash
   composer install
   ```

3. **Configure o arquivo de ambiente**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure o banco de dados**:
   O projeto está configurado para usar MySQL. Siga as instruções detalhadas no arquivo `MYSQL_SETUP.md` para:
   - Instalar o MySQL Server
   - Criar o banco de dados usando o script `setup_mysql.sql`
   - Configurar as credenciais no arquivo `.env`
   
   **Resumo rápido:**
   ```bash
   # No MySQL Workbench ou linha de comando:
   mysql -u root -p < setup_mysql.sql
   
   # Configure o .env com suas credenciais MySQL
   # DB_PASSWORD=sua_senha_aqui (se você definiu uma senha)
   ```

5. **Execute as migrações**:
   ```bash
   php artisan migrate
   ```

6. **Popule o banco de dados com dados de exemplo**:
   ```bash
   php artisan db:seed
   ```

7. **Instale as dependências do NPM**:
   ```bash
   npm install
   ```

8. **Compile os assets**:
   ```bash
   npm run build
   ```
   
   Para desenvolvimento com hot reload:
   ```bash
   npm run dev
   ```

9. **Inicie o servidor de desenvolvimento**:
    ```bash
    php artisan serve
    ```

10. **Acesse o sistema**:
    Abra seu navegador e acesse `http://localhost:8000`

## 👤 Credenciais de Teste

Após executar o seeder, você pode fazer login com as seguintes credenciais:

- **Email**: test@example.com
- **Senha**: password

## 👑 Criando um Usuário Administrador

Para tornar um usuário administrador, você pode usar o `php artisan tinker`:

1.  Abra o terminal no diretório do projeto.
2.  Execute `php artisan tinker`.
3.  Dentro do `tinker`, execute os seguintes comandos:
    ```php
    $user = App\Models\User::where(\'email\', \'test@example.com\')->first();
    $user->is_admin = true;
    $user->save();
    exit;
    ```
    Isso tornará o usuário `test@example.com` um administrador. Você pode substituir `\'test@example.com\'` pelo e-mail de qualquer outro usuário existente.

4.  Para criar um novo usuário e torná-lo administrador, você pode usar:
    ```php
    $user = App\Models\User::create([
        \'name\' => \'Admin User\',
        \'email\' => \'admin@example.com\',
        \'password\' => bcrypt(\'password\'), // Use uma senha forte em produção
        \'is_admin\' => true,
    ]);
    exit;
    ```

Você pode adicionar verificações `isAdmin()` em suas views ou controladores para restringir o acesso a certas funcionalidades apenas para administradores.

## 📱 Uso do Sistema

### Página Inicial

A página inicial apresenta um mapa interativo com todas as árvores cadastradas, estatísticas gerais e uma lista de atividades recentes. Os marcadores no mapa são coloridos de acordo com a espécie da árvore e seu tamanho representa o diâmetro do tronco.

### Visualização de Detalhes

Ao clicar em um marcador no mapa ou em um link de detalhes, você pode visualizar informações completas sobre uma árvore específica, incluindo seu histórico de atividades e localização precisa.

### Dashboard

Usuários autenticados têm acesso a um dashboard personalizado onde podem gerenciar suas árvores e atividades.

## 🎨 Personalização

### Cores das Espécies

As cores dos marcadores no mapa são definidas no campo `color_code` da tabela `species`. Você pode personalizar essas cores editando os registros no banco de dados ou através do seeder.

### Dados de Exemplo

O arquivo `database/seeders/DatabaseSeeder.php` contém dados de exemplo com coordenadas de São Paulo, Brasil. Você pode modificar este arquivo para incluir dados de sua própria região.

### Tipos de Atividades

Os tipos de atividades disponíveis são: `watered` (regada), `weeded` (capinada), `mulched` (coberta com mulch), `pruned` (podada) e `fertilized` (adubada). Você pode adicionar novos tipos conforme necessário.

## 📂 Estrutura de Arquivos Principais

```
tree-map-project/
├── app/
│   ├── Http/Controllers/
│   │   └── TreeController.php
│   └── Models/
│       ├── Species.php
│       ├── Tree.php
│       └── Activity.php
├── database/
│   ├── migrations/
│   │   ├── 2025_10_14_202901_create_species_table.php
│   │   ├── 2025_10_14_202901_create_trees_table.php
│   │   └── 2025_10_14_202901_create_activities_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── welcome.blade.php
│       └── trees/
│           └── show.blade.php
└── routes/
    └── web.php
```

## 🔧 Desenvolvimento

### Adicionar Novas Funcionalidades

O sistema foi desenvolvido seguindo as convenções do Laravel, facilitando a adição de novas funcionalidades. Algumas sugestões de melhorias:

- Implementar CRUD completo para árvores e espécies
- Adicionar filtros e busca no mapa
- Implementar upload de fotos das árvores
- Criar relatórios e gráficos de estatísticas
- Adicionar notificações para atividades de manutenção
- Implementar API REST para integração com aplicativos móveis

### Testes

O projeto utiliza Pest PHP para testes. Execute os testes com:

```bash
php artisan test
```

## 📄 Licença

Este projeto é open source e está disponível sob a licença MIT.

## 🤝 Contribuições

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou enviar pull requests.

## 📞 Suporte

Para questões e suporte, entre em contato através do repositório do projeto.

---

Desenvolvido com ❤️ usando Laravel e Leaflet.js

