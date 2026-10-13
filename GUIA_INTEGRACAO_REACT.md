# WorkFlow — Integracao de perfil e equipe ao React

Esta versao usa a estrutura enviada em `WorkFlow_atual.zip` (branch exportada pelo Git).

## O que foi incluido

- Aba **Meu perfil** no React para dono e freelancer (dados de conta).
- **Meus estabelecimentos** e cadastro de nova loja dentro do React.
- Aba **Funcionarios** para o dono: freelancers aprovados e solicitacoes pendentes com botoes Aprovar / Recusar.
- **Empresas com quem trabalho** e solicitacoes de vinculo na tela do freelancer, com formulario por codigo da loja.
- Historico e cadastro de experiencias profissionais no React.
- Atualizacao da API PHP para fornecer os dados dessas telas e receber as acoes com sessao e CSRF.
- Ajustes para horario inicial e final do turno: validacao, insercao e edicao, alem da exibicao dos dois campos.
- Caminhos do React/PWA para a pasta `gestao_freelas` no XAMPP.

## Instalar sem perder suas mudancas

1. Faca BACKUP do projeto em `C:\xampp\htdocs\gestao_freelas` e do banco via phpMyAdmin.
2. Garanta que o VS Code mostra `git status` sem arquivos locais importantes nao salvos. Nao execute `git reset --hard`.
3. Extraia o ZIP de atualizacao e **copie o conteudo de suas pastas** (`frontend`, `api`, `bd`, `visao` e `logica`) para as mesmas pastas do projeto, substituindo apenas arquivos de nomes iguais. **Nao apague as pastas originais inteiras.**
4. **IMPORTANTE**: o arquivo `bd/funcoesBD.php` vem do ZIP enviado e pode conter a conexao local antiga (`root`, senha vazia, porta `3306`). Antes de substituir esse arquivo, anote sua conexao atual; depois de copiar, restaure somente os dados locais da funcao `conectarBD()` e NAO publique senhas no GitHub.
5. Esta atualizacao nao requer importar `gestao_freelas.sql` novamente. **Nao importe** esse script em banco com dados (ele tem `DROP TABLE`).
6. No phpMyAdmin, selecione `gestao_freelas` e execute `SHOW COLUMNS FROM turnos LIKE 'horario_fim';`. Se a coluna aparecer, nao faca nada. Somente se NAO aparecer, use `bd/atualizacao_horario_fim_SE_NECESSARIO.sql` na aba SQL.
7. Abra um terminal na pasta `C:\xampp\htdocs\gestao_freelas\frontend` e execute `npm install` e `npm run build`.
8. Com Apache e MySQL ligados no XAMPP, abra `http://localhost/gestao_freelas/app/` (em caso de cache, experimente janela anonima / limpar o service worker antigo).

## Testes recomendados

**Gerente:** entrar -> Meu perfil -> ver informacoes e lojas -> cadastrar uma loja de teste -> Funcionarios -> visualizar aprovados e solicitacoes -> aprovar/recusar uma solicitacao de teste -> criar turno com inicio e fim -> editar turno e salvar.

**Freelancer:** entrar -> Meu perfil -> consultar empresas aprovadas e pendentes -> solicitar vinculo com codigo de uma loja de teste -> registrar experiencia -> conferir historico -> consultar convites.

Em todo o fluxo acima, o endereco do navegador deve permanecer em `/gestao_freelas/app/` e o menu do React deve continuar visivel. Cadastros iniciais de usuarios e recuperacao de senha ainda dependem da interface PHP antiga.

## Publicar no GitHub depois de testar

Na raiz do projeto, execute `git status`, confira `git diff`, e NAO inclua arquivos de credenciais. Trabalhe em uma branch de integracao nova antes de `git add`, `git commit` e `git push`.

## Limites da validacao

PHP: verificado com `php -l`. JSX: validado por um parser TypeScript. Nao foi possivel fazer `npm run build` aqui porque as dependencias da instalacao do usuario nao acompanham o `git archive`. A integracao com MySQL e a interface em um navegador real ainda precisam dos testes acima.
