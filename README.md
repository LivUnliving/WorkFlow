# Workflow

*Workflow* é uma aplicação web desenvolvida para auxiliar proprietários e gestores de lojas no gerenciamento de turnos de trabalho e equipes de freelancers. O sistema permite cadastrar funcionários, freelancers, lojas, horários e escalas, facilitando a organização da rotina operacional e o controle da disponibilidade dos colaboradores.

A aplicação busca tornar o gerenciamento de equipes mais prático e eficiente, reduzindo conflitos de agenda e ajudando donos de lojas a distribuírem melhor os turnos de trabalho. O projeto é destinado a pequenos e médios comércios que precisam organizar colaboradores fixos e profissionais temporários em um único sistema.

# Tecnologias Utilizadas

* *PHP* — Desenvolvimento da aplicação e processamento das regras de negócio
* *CSS3* — Estilização, layout e responsividade da interface
* *MySQL* — Banco de dados relacional utilizado pelo sistema
* *phpMyAdmin* — Gerenciamento e importação do banco de dados SQL

# Pré-requisitos

Para executar o projeto, você precisa de:

* Servidor local, como XAMPP, WAMP ou Laragon
* PHP instalado
* MySQL
* phpMyAdmin
* Navegador moderno (Chrome, Edge, Firefox ou Safari)
* Editor de código (recomendado: VS Code)

# Como Instalar

1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/workflow.git
````

2. Acesse a pasta do projeto:

```bash
cd workflow
```

3. Mova a pasta do projeto para o diretório do servidor local:

```plaintext
xampp/htdocs/workflow
```

4. Inicie o Apache e o MySQL pelo XAMPP, WAMP ou Laragon.

5. Acesse o phpMyAdmin pelo navegador:

```plaintext
http://localhost/phpmyadmin
```

6. Crie um banco de dados para o projeto.

7. Importe o arquivo `.sql` do projeto no banco de dados criado.

# Como Executar

Após configurar o banco de dados e iniciar o servidor local, acesse no navegador:

```plaintext
http://localhost/workflow
```

# Scripts Disponíveis

Este projeto não utiliza gerenciadores de pacotes, como npm ou Composer. A aplicação é executada diretamente por meio de um servidor local com suporte a PHP e MySQL.

# Estrutura de Pastas

```plaintext
assets/
 ├── css/
 │    └── style.css      → Estilos da aplicação
 │
 └── img/                → Recursos visuais

database/
 └── workflow.sql        → Arquivo SQL para criação/importação do banco de dados

config/
 └── conexao.php         → Conexão com o banco de dados

pages/
 ├── dashboard.php       → Painel principal
 ├── funcionarios.php    → Gerenciamento de funcionários
 ├── freelancers.php     → Gerenciamento de freelancers
 └── escalas.php         → Gerenciamento de turnos e escalas

index.php               → Página inicial da aplicação
```

# Funcionalidades

* Cadastro de lojas
* Cadastro de funcionários
* Cadastro de freelancers
* Gerenciamento de turnos
* Organização de escalas de trabalho
* Controle de disponibilidade dos colaboradores
* Integração com banco de dados SQL
* Interface simples e responsiva

# Como Contribuir

## Estrutura de Branches

* main → versão estável
* dev → desenvolvimento geral
* feature/nome-da-feature → novas funcionalidades
* fix/nome-do-bug → correções

## Padrão de Commit

Utilizar commits semânticos:

```bash
feat: adiciona funcionalidade
fix: corrige erro
style: ajusta layout
refactor: reorganiza estrutura de código
docs: atualiza documentação
```

## Pull Requests

* Criar PR para dev
* Descrever claramente o que foi feito
* Garantir que o código está funcionando
* Evitar conflitos com a branch principal

# Licença

Este projeto está licenciado sob a licença *MIT*.

# Autores

* Lívia Maria Velasco da Silva — [https://github.com/LivUnliving](https://github.com/LivUnliving)
* Mariana Amaral Scolari — [https://github.com/MariFrixta](https://github.com/MariFrixta)
