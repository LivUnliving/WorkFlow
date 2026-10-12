# WorkFlow — guia passo a passo dos ajustes solicitados

**Versão de demonstração:** 11/10/2026. **Entrega prevista:** 12/10/2026.

Este material acompanha os códigos modificados e utiliza o banco de dados do projeto original. Você NÃO precisa colar manualmente trechos de código: os arquivos desta cópia já foram ajustados. Leia cada passo e teste antes de seguir.

> **Situação dos testes:** verifiquei a sintaxe PHP dos arquivos. Não executei os fluxos em um MySQL real, nem consegui instalar as dependências React neste ambiente (acesso ao registro npm indisponível). Portanto, é obrigatório fazer os testes no seu computador antes da apresentação.

## Parte A — Preparação (obrigatória)

1. Abra o XAMPP e ative `Apache` e `MySQL`.
2. No phpMyAdmin (`http://localhost/phpmyadmin`), clique no banco `gestao_freelas` → **Exportar** → **Rápido** → **SQL** → **Exportar**. Guarde o SQL antigo.
3. Faça uma cópia de `C:\xampp\htdocs\gestao_freelas` com o nome `gestao_freelas_backup`.
4. Extraia este ZIP e copie a pasta interna `gestao_freelas` para `C:\xampp\htdocs\`, mantendo o backup separado. Se a pasta existir, primeiro renomeie a antiga para um backup e então copie a nova. Não misture pastas antigas e novas.
5. Abra `C:\xampp\htdocs\gestao_freelas` no VS Code.
6. Abra `bd/funcoesBD.php`; na função `conectarBD`, confira a porta MySQL: o projeto original usa **3307**. Se o seu XAMPP usa **3306**, troque somente o último parâmetro `3307` por `3306`. Confira também usuário e senha. A porta do Apache é independente.
7. No phpMyAdmin, clique no banco `gestao_freelas`, abra a aba **SQL**, copie o conteúdo de **`bd/atualizacao_workflow.sql`** e clique em **Executar**. Esse script cria **apenas** as novas tabelas (`notificacoes` e `redefinicoes_senha`) se ainda não existirem. Não use o arquivo `bd/gestao_freelas.sql` em um banco que tenha dados, pois ele contém `DROP TABLE`.
8. Confirme que as novas tabelas aparecem na lista de tabelas à esquerda.
9. Abra `http://localhost/gestao_freelas/visao/Login.php` e faça login com conta existente. Caso não exista, cadastre uma conta de dono e outra freelancer.

## Passo 17 — Responsividade para smartphone

**O que foi alterado:** `css/style.css` recebeu regras para telas pequenas (até 650 px), telas maiores, campos que não ultrapassam os cartões, barra lateral que fica horizontal no celular e botão de novo turno posicionado de forma fixa.

**Como conferir:**

1. Abra o login no Chrome do computador.
2. Pressione **F12** para abrir as ferramentas do desenvolvedor.
3. Clique no pequeno ícone de celular/tablet (**Alternar barra de ferramentas do dispositivo**, normalmente Ctrl+Shift+M).
4. No seletor superior escolha um celular, por exemplo `iPhone 12` ou `Responsive`.
5. Teste larguras de **375 px** e **390 px**. Não deve existir rolagem horizontal de página.
6. Faça login como dono e examine o cabeçalho, os cartões, os botões e a criação de turno.
7. Volte ao modo desktop; teste a tela em **1366 px** de largura.
8. Repita para freelancer.

Caso um elemento ultrapasse a borda, anote a página e tire uma captura de tela. Os estilos originais têm vários tamanhos e margens fixas, então é possível que alguma página secundária ainda precise de pequenos retoques.

## Passo 18 — Logout e configurações

**Arquivos:** `visao/PaginaInicialDono.php`, `visao/PaginaInicialFreela.php`, `visao/Configuracoes.php`, `visao/Sair.php`, `imgs/logout.svg`.

**Como testar:**

1. Faça login como dono.
2. Veja na navegação os ícones diferentes: **sino**, **engrenagem** e **sair**.
3. Clique na engrenagem: abrirá a página `Configuracoes.php` com texto provisório e seta para voltar.
4. Clique na seta: deverá retornar à página inicial do dono, SEM sair da conta.
5. Clique no ícone de sair: deverá retornar ao login.
6. Tente pressionar **Voltar** no navegador após sair: o conteúdo privado não deverá ficar disponível com acesso funcional sem nova autenticação.
7. Repita com uma conta freelancer. A página Configurações deve voltar à página correta de acordo com o perfil.

## Passo 19 — CRUD completo de turnos

**Arquivos:** `visao/PaginaInicialDono.php` mostra os botões; `visao/EditarTurno.php` é a nova tela; `logica/processamento.php` recebe as ações; `bd/funcoesBD.php` executa as consultas.

**O que cada operação faz:**

- **Criar:** tela `CriarTurno.php` já existente, agora com token CSRF e filtragem por loja dos freelancers aprovados. O número de vagas é calculado pelo número de convidados.
- **Consultar:** cartões da página inicial do dono, já existentes.
- **Editar:** modifica **data, hora, função, valor e descrição**. Não muda os freelancers convidados nem a quantidade total de vagas nesta versão básica.
- **Excluir:** remove o turno **somente se pertencer a uma empresa do dono logado**. As candidaturas desse turno também são removidas automaticamente pela chave estrangeira com `ON DELETE CASCADE`.

**Teste completo:**

1. Entre como dono, abra **Funcionários** e confirme que há pelo menos um freelancer aprovado em uma loja.
2. Na tela inicial, clique em `+` para cadastrar um turno. Escolha a loja, preencha data/hora/função/valor/descrição, marque um funcionário aprovado e publique.
3. Volte à página inicial: deve aparecer o novo cartão.
4. Clique em **Editar**, altere função ou valor, clique em **Salvar alterações** e confira o cartão atualizado.
5. Entre com o freelancer convidado, confira o turno e volte ao dono.
6. No painel do dono, clique em **Excluir** e confirme na caixa de diálogo. O cartão deverá desaparecer.
7. Entre como freelancer novamente: o convite daquele turno não deverá aparecer.
8. Teste uma conta de dono diferente: ela não deve conseguir editar ou excluir o turno do primeiro dono (o servidor confere a titularidade).

**Observação:** os dados do turno são alterados, mas não a lista de convidados durante a edição. Se o professor exigir alterar também a equipe de um turno, essa será a próxima evolução do CRUD.

## Passo 20 — Notificações para dono e freelancer

**Arquivos:** `bd/atualizacao_workflow.sql`, `bd/funcoesBD.php`, `logica/processamento.php`, `visao/Notificacoes.php`, `imgs/notificacoes.svg`, além das duas páginas iniciais.

**Como testar, começando sem notificações antigas:**

1. Faça login como dono, convide um freelancer para um novo turno.
2. Saia. Faça login com o freelancer convidado.
3. Na barra de navegação, abra o **sino**. A notificação **Convite de turno** deverá aparecer.
4. Clique **Marcar como lida**. O contador deverá diminuir quando voltar à página inicial.
5. Como freelancer, aceite ou recuse o convite na página de turnos.
6. Saia e entre novamente como dono.
7. Abra o sino: uma notificação **Resposta a turno** deverá aparecer.
8. Também é possível testar uma solicitação de vínculo entre freelancer e loja: a aprovação ou recusa gera uma notificação para o freelancer.

As notificações são **internas, consultadas ao abrir a página**; não são notificações push do sistema operacional nem alertas instantâneos com a aplicação fechada.

## Passo 21 — Recuperação de senha

**Arquivos:** `visao/EsqueciSenha.php`, `visao/RedefinirSenha.php`, `logica/processamento.php`, `bd/funcoesBD.php`, `config/email.php`.

**O que o sistema faz:** gera um token aleatório com validade de 30 minutos, armazena seu hash em `redefinicoes_senha`, permite definir nova senha com pelo menos 8 caracteres e inutiliza o token após uso. A mensagem de solicitação é genérica, para não expor quais e-mails têm conta.

### Testar HOJE no mesmo computador — modo demonstração local

1. No VS Code, abra `config/email.php`.
2. Na linha `define('WORKFLOW_DEMO_RESET', false);`, troque `false` por `true` e salve.
3. No **próprio computador**, abra `http://localhost/gestao_freelas/visao/Login.php`. (Não teste esse modo a partir do celular.)
4. Clique em **Esqueci minha senha**.
5. Digite o e-mail de uma conta existente e clique em **Enviar**.
6. Uma mensagem exibirá **Modo demonstração local: Abrir link de teste**. Clique nesse link. O link só será exibido para requisições de loopback (`127.0.0.1` ou `::1`).
7. Digite e confirme uma nova senha de pelo menos 8 caracteres. Salve.
8. Entre no login com a nova senha.
9. Tente abrir o mesmo link de redefinição de novo: ele deverá ser recusado, pois o token já foi utilizado.
10. **Obrigatório:** após testar, volte a `config/email.php` e mude para `false`. Nunca publique o sistema com esse modo ligado.

Esse modo de demonstração **não envia e-mail** e **não é recuperação segura para produção**. Use exclusivamente no computador local e somente com contas de teste.

### Configurar envio SMTP real (trabalho adicional)

1. Instale o **Composer** no Windows (site oficial `https://getcomposer.org/`). Confirme no terminal com `composer --version`.
2. Abra o terminal do VS Code na pasta `C:\xampp\htdocs\gestao_freelas` (menu **Terminal → Novo Terminal**).
3. Execute `composer require phpmailer/phpmailer`.
4. Configure uma conta SMTP própria, obtendo o endereço do servidor, porta, usuário, senha e remetente. É possível testar com uma caixa de entrada de desenvolvimento do Mailtrap; nesse caso, os e-mails ficam na caixa de testes do Mailtrap, não são entregues ao destinatário real.
5. Dentro de `config/`, faça uma cópia de `email.local.exemplo.php`, renomeando para `email.local.php`.
6. Edite `email.local.php` e substitua os exemplos **por suas credenciais SMTP reais**. Guarde esse arquivo localmente: ele está ignorado pelo Git e não deve ser publicado.
7. Confira que `WORKFLOW_DEMO_RESET` está `false`.
8. Use novamente **Esqueci minha senha**, verifique a chegada no serviço SMTP e conclua a troca pelo link recebido.

Se você não configurar SMTP, a tentativa de envio usa a função `mail()` do PHP, que geralmente NÃO envia e-mails numa instalação padrão do XAMPP/Windows. Nesse caso, o envio real permanece pendente; o teste local acima demonstra apenas geração e uso dos tokens.

## Passo 22 — React + PWA no computador

A pasta `frontend/` contém uma interface React inicial criada para login, painéis dos dois perfis, leitura e gestão de turnos, notificações, logout e configurações. **Algumas telas (cadastro de usuários, gerenciamento de lojas/equipe, perfis e recuperação de senha) ainda usam as páginas PHP antigas.** Não é uma migração 100% concluída.

**O frontend React se comunica com o PHP pela pasta `api/`, e o PHP continua se comunicando com o MySQL.**

### Instalar ferramentas

1. Baixe o Node.js LTS pelo site oficial `https://nodejs.org/` e instale no Windows.
2. Feche e reabra o VS Code.
3. Clique no menu **Terminal → Novo Terminal**.
4. Digite `node -v` e Enter. Deve aparecer um número de versão. Depois digite `npm -v` e Enter.
5. No terminal, execute:

```powershell
cd C:\xampp\htdocs\gestao_freelas\frontend
npm install
npm run dev
```

6. Deixe esse terminal aberto. Ele exibirá o endereço de desenvolvimento. Com a configuração desta versão, abra `http://localhost:5173/gestao_freelas/app/` no Chrome. O Vite encaminha chamadas `api/` ao Apache local, então **Apache e MySQL precisam estar ligados**.
7. Entre com uma conta já cadastrada. Confira ambos os tipos de usuário.
8. Teste criar/editar/excluir turnos, responder convites e ver notificações também no React.

Se a porta Apache não for `80`, ajuste `frontend/vite.config.js`: no campo `server.proxy['/gestao_freelas/api'].target`, troque `http://localhost` por `http://localhost:8080` ou a porta realmente utilizada pelo Apache.

### Gerar a versão final para servir pelo XAMPP

1. No terminal do VS Code, continue dentro da pasta `frontend`.
2. Pressione `Ctrl+C` para parar o servidor de desenvolvimento (se estiver rodando).
3. Execute:

```powershell
npm run build
```

4. O Vite cria a pasta `C:\xampp\htdocs\gestao_freelas\app` com os arquivos prontos.
5. No computador, abra **`http://localhost/gestao_freelas/app/`**. Essa é a versão React compilada pelo Apache.
6. Se tudo funcionar, teste no celular com **`http://IP_DO_PC/gestao_freelas/app/`** (substituindo `IP_DO_PC` pelo IPv4 do computador). O PHP e o banco continuam executando no computador.

### PWA: instalação no celular

Esta versão tem `manifest.webmanifest`, ícones 192/512 e `sw.js` para recursos estáticos. Entretanto, **abrir pelo IP local com HTTP não equivale a acessar uma origem HTTPS segura**. Para instalar um PWA de forma confiável no smartphone, use uma URL HTTPS válida (ou um ambiente local com certificado devidamente confiável no aparelho). Em `localhost` no próprio computador, os navegadores compatíveis podem mostrar uma opção de instalação.

**Importante:** o service worker NÃO salva dados de turnos para uso offline. Sem conexão com o servidor PHP/MySQL, o usuário não consegue criar ou responder turnos. Isso evita armazenar dados privados sensíveis em cache sem planejamento.

## Antes da demonstração: verificação final

- [ ] A versão PHP ainda abre e permite login como dono e freelancer.
- [ ] A versão React compilada abre pelo Apache.
- [ ] A tela funciona no celular, sem cortes ou rolagem horizontal.
- [ ] O sino existe nas duas telas iniciais e exibe novas notificações.
- [ ] O botão sair encerra a sessão; configurações apenas volta à página anterior.
- [ ] O dono consegue criar, consultar, editar e excluir um turno próprio.
- [ ] O freelancer consegue aceitar/recusar um convite.
- [ ] A redefinição funciona com token local de teste; o modo demo foi desligado.
- [ ] Se SMTP for exigido na avaliação, o e-mail real foi testado separadamente.
- [ ] Os dados usados na apresentação são de teste; o SQL antigo foi preservado.

## Problemas frequentes

| Sintoma | O que conferir |
|---|---|
| `Unknown table notificacoes` | Executou `bd/atualizacao_workflow.sql` no banco `gestao_freelas`? |
| Erro de conexão SQL | `bd/funcoesBD.php`, porta 3306/3307, MySQL ligado e banco correto |
| Tela PHP `404` | Pasta extraída está em `C:\xampp\htdocs\gestao_freelas`? |
| Tela React `404` | Fez `npm run build` para gerar `app/`? |
| React não abre o login | Verifique F12 → Console/Network e a API `http://localhost/gestao_freelas/api/index.php` |
| `npm` não reconhecido | Instale Node.js e reinicie o terminal do VS Code |
| `npm install` falhou | Verifique a conexão com internet: as dependências precisam ser baixadas |
| Senha não chegou no e-mail | É preciso configurar SMTP; `mail()` não é suficiente no XAMPP padrão |
| Celular não abre a aplicação | Mesma rede, IPv4 correto, porta Apache e firewall de rede privada |
| PWA não instala usando IP HTTP | Use HTTPS confiável para o celular; HTTP por IP de rede não é contexto seguro |

**Observação sobre histórico Git:** este ZIP foi gerado sem a pasta `.git` para evitar incluir informações do repositório. Mantenha o repositório original de vocês separado e faça os commits das modificações que conseguirem validar.
