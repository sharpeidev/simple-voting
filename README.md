# Simple Voting

Sistema de votação simples desenvolvido em Drupal 11.

## Requisitos

- Docker
- Lando
- Git

## Instalação

Clone o projeto:

    git clone git@github.com:sharpeidev/simple-voting.git
    cd simple-voting

Inicie o ambiente:

    lando start

Instale as dependências:

    lando composer install

Instale o Drupal:

    lando drush site:install standard \
      --db-url=mysql://drupal11:drupal11@database/drupal11 \
      --site-name="Simple Voting" \
      --account-name=admin \
      --account-pass=admin \
      --yes

Limpe o cache:

    lando drush cr

Acesse:

https://simple-voting.lndo.site

## Administração

Perguntas:

https://simple-voting.lndo.site/admin/content/simple-voting/questions

Configurações:

https://simple-voting.lndo.site/admin/config/services/simple-voting

O administrador pode:

- Cadastrar e editar perguntas;
- Cadastrar e editar opções;
- Definir a ordem das opções;
- Ativar ou desativar perguntas e opções;
- Definir se os resultados serão exibidos após a votação;
- Habilitar ou desabilitar globalmente a votação.

## API

### Listar perguntas

    GET /api/questions

### Consultar pergunta

    GET /api/questions/{identifier}

### Registrar voto

    POST /api/questions/{identifier}/vote

Body:

    {
      "option_id": 1
    }

A requisição exige usuário autenticado e token CSRF no header:

    X-CSRF-Token: <token>

### Consultar resultados

    GET /api/questions/{identifier}/results

Os resultados somente são disponibilizados quando configurados para a pergunta e após o usuário ter votado.

## Autenticação

A API utiliza a autenticação de sessão do Drupal.

Para obter uma sessão:

    POST /user/login?_format=json

Body:

    {
      "name": "usuario",
      "pass": "senha"
    }

O usuário autenticado deve manter o cookie de sessão e utilizar o csrf_token retornado no login nas requisições que alteram dados.

## Regras de votação

- Apenas usuários autenticados podem votar;
- Cada usuário pode votar uma única vez em cada pergunta;
- A integridade da votação é garantida por uma restrição UNIQUE no banco de dados;
- A opção precisa pertencer à pergunta informada;
- Perguntas e opções inativas não podem receber votos;
- A votação pode ser desabilitada globalmente;
- Resultados podem ser configurados individualmente por pergunta.

## Postman

A collection está disponível em:

    postman/simple-voting.postman_collection.json

Ela contém todos os endpoints da API disponíveis no sistema.

## Observabilidade

Eventos de votação são registrados no log do Drupal.

Para visualizar:

    lando drush watchdog:show --count=10

São registrados eventos de:

- Votos realizados;
- Votos duplicados;
- tentativas inválidas;
- Votação desabilitada.

## Estrutura

O módulo customizado está localizado em:

    web/modules/custom/simple_voting

A implementação utiliza entidades Drupal e serviços separados para:

- Perguntas;
- Opções;
- Votos;
- Regras de votação;
- Resultados;
- Configuração;
- API;
- Logging.
