<?php
session_start();
require_once "../bd/funcoesBD.php";
require_once "../config/seguranca.php";
require_once "../config/email.php";

/* ACTION 1: CADASTRO DE USUÁRIO */
if (isset($_POST['btn_cadastrar'])) {

    $nome = $_POST['nome_usuario'] ?? '';
    $email = $_POST['email_usuario'] ?? '';
    $senha = $_POST['senha_usuario'] ?? '';
    $tipo = $_POST['tipo_usuario'] ?? '';

    $cpf = $_POST['cpf_usuario'] ?? '';
    $cnpj = $_POST['cnpj_usuario'] ?? '';
    $codigo_loja = $_POST['codigo_loja'] ?? '';
    $telefone = $_POST['telefone_usuario'] ?? ($_POST['telefone_empresa'] ?? ($_POST['telefone'] ?? ''));
    $cidade = $_POST['cidade_usuario'] ?? ($_POST['cidade_empresa'] ?? ($_POST['cidade'] ?? ''));

    $usuario = cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja, $cpf, $cnpj, $telefone, $cidade);

    if ($usuario) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_tipo'] = $tipo;
        $_SESSION['usuario_loja'] = $usuario['codigo_loja'] ?? '';

        if ($tipo === 'dono') {
            header("Location: ../visao/PaginaInicialDono.php");
            exit();
        }

        header("Location: ../visao/PaginaInicialFreela.php");
        exit();
    }

    die("Erro no cadastro. Verifique se o e-mail já não está cadastrado.");
}

/* ACTION 2: LOGIN */
if (isset($_POST['btn_login'])) {

    $email = $_POST['email_usuario'] ?? '';
    $senha = $_POST['senha_usuario'] ?? '';

    $usuario = buscarUsuarioPorEmail($email);

    if ($usuario && senhaConfere($senha, $usuario['senha'])) {
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        if ($usuario['tipo'] === 'dono') {
            header("Location: ../visao/PaginaInicialDono.php");
            exit();
        }

        header("Location: ../visao/PaginaInicialFreela.php");
        exit();
    }

    header("Location: ../visao/Login.php?erro=dados_invalidos");
    exit();
}

/* ACTION 3: DONO CRIANDO NOVO TURNO */
if (isset($_POST['btn_criar_turno'])) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
        header("Location: ../visao/Login.php");
        exit();
    }

    if (!verificarCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); die('Formulário expirado. Atualize a página.'); }
    $dono_id = $_SESSION['usuario_id'];
    $empresa_id = $_POST['empresa_id'] ?? 0;
    $descricao = $_POST['descricao'] ?? '';
    $data_turno = $_POST['data'] ?? '';
    $horario_inicio = $_POST['inicio'] ?? '';
    $horario_fim = $_POST['fim'] ?? '';
    $funcao = $_POST['cargo'] ?? '';
    $valor = $_POST['valor'] ?? '0.00';
    $funcionarios = $_POST['funcionarios'] ?? [];
    $vagas = count($funcionarios);
    $conexaoValidacao = conectarBD();
    $ids = array_values(array_unique(array_map('intval', is_array($funcionarios) ? $funcionarios : [])));
    $empresaSegura = (int)$empresa_id;
    $permitidos = [];
    $rs = mysqli_query($conexaoValidacao, "SELECT freelancer_id FROM usuario_empresas WHERE empresa_id='$empresaSegura' AND status='Aprovado'");
    while ($row = mysqli_fetch_assoc($rs)) $permitidos[] = (int)$row['freelancer_id'];
    mysqli_close($conexaoValidacao);
    if (count($ids) === 0 || count(array_diff($ids, $permitidos)) > 0) die('Escolha apenas freelancers aprovados para essa empresa.');
    $funcionarios = $ids;
    $vagas = count($ids);
    if (!DateTimeImmutable::createFromFormat('!Y-m-d', $data_turno) || trim($descricao)==='' || trim($funcao)==='' || (float)$valor<0) die('Dados do turno invalidos.');

    if ($vagas === 0) {
        die("Selecione pelo menos um freelancer para receber o chamado do turno.");
    }

    if (!empresaPertenceAoDono($empresa_id, $dono_id)) {
        die("Empresa inválida para este usuário.");
    }

    $sucesso = inserirTurno($empresa_id, $descricao, $data_turno, $horario_inicio, $horario_fim, $valor, $funcao, $vagas, $funcionarios);

    if ($sucesso) {
        header("Location: ../visao/ConfirmaTurno.php");
        exit();
    }

    die("Erro ao criar o turno no banco de dados.");
}

/* ACTION 4: EXPERIÊNCIA */
if (isset($_POST['btn_adicionar_experiencia'])) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
        header("Location: ../visao/Login.php");
        exit();
    }

    $freelancer_id = $_SESSION['usuario_id'];
    $cargo = $_POST['cargo_experiencia'] ?? '';
    $tempo = $_POST['tempo_experiencia'] ?? '';
    $local = $_POST['local_experiencia'] ?? '';
    $descricaoDigitada = $_POST['descricao_experiencia'] ?? '';

    $descricao = trim($descricaoDigitada) !== ''
        ? $descricaoDigitada
        : "Trabalhou como $cargo no estabelecimento $local.";

    $sucesso = cadastrarExperiencia($freelancer_id, $cargo, $tempo, $descricao);

    if ($sucesso) {
        header("Location: ../visao/ConfirmaExp.php");
        exit();
    }

    die("Erro ao salvar experiência.");
}

/* ACTION 5: FREELANCER CONFIRMA OU RECUSA TURNO */
if (isset($_POST['btn_aceitar_turno']) || isset($_POST['btn_recusar_turno'])) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
        header("Location: ../visao/Login.php");
        exit();
    }

    $turno_id = $_POST['turno_id'] ?? 0;
    $freelancer_id = $_SESSION['usuario_id'];
    $status = isset($_POST['btn_aceitar_turno']) ? 'Confirmado' : 'Recusado';

    responderTurno($turno_id, $freelancer_id, $status);

    header("Location: ../visao/PaginaInicialFreela.php");
    exit();
}

/* ACTION 6: FREELANCER SOLICITA VÍNCULO COM EMPRESA */
if (isset($_POST['btn_vincular_loja'])) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
        header("Location: ../visao/Login.php");
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $novo_codigo = $_POST['codigo_loja'] ?? '';

    $executou = candidatarFreelancerEmpresa($usuario_id, $novo_codigo);

    if ($executou) {
        header("Location: ../visao/ConfirmaNovaLoja.php");
        exit();
    }

    die("Não encontramos uma empresa com esse código. Verifique o código e tente novamente.");
}

/* ACTION 7: DONO CADASTRA MAIS UM ESTABELECIMENTO */
if (isset($_POST['btn_adicionar_loja'])) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
        header("Location: ../visao/Login.php");
        exit();
    }

    $dono_id = $_SESSION['usuario_id'];
    $nome = $_POST['nome_loja'] ?? '';
    $telefone = $_POST['telefone_loja'] ?? '';
    $local = $_POST['local_loja'] ?? '';
    $cnpj = $_POST['cnpj_loja'] ?? '';
    $codigo = $_POST['codigo_loja'] ?? '';

    $sucesso = cadastrarEmpresaFisica($dono_id, $nome, $telefone, $local, $cnpj, $codigo);

    if ($sucesso) {
        header("Location: ../visao/ConfirmaLoja.php");
        exit();
    }

    die("Erro no banco ao cadastrar empresa.");
}

/* ACTION 8: DONO ACEITA OU RECUSA FREELANCER NA EQUIPE */
if (isset($_POST['btn_aceitar_funcionario']) || isset($_POST['btn_recusar_funcionario'])) {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
        header("Location: ../visao/Login.php");
        exit();
    }

    $vinculo_id = $_POST['vinculo_id'] ?? 0;
    $dono_id = $_SESSION['usuario_id'];
    $status = isset($_POST['btn_aceitar_funcionario']) ? 'Aprovado' : 'Recusado';

    $atualizou = alterarStatusVinculoFuncionario($vinculo_id, $dono_id, $status);
    if ($atualizou) {
        $c = conectarBD();
        $st = mysqli_prepare($c, 'SELECT ue.freelancer_id FROM usuario_empresas ue INNER JOIN empresas e ON e.id=ue.empresa_id WHERE ue.id=? AND e.dono_id=?');
        mysqli_stmt_bind_param($st, 'ii', $vinculo_id, $dono_id); mysqli_stmt_execute($st);
        $destino = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        if ($destino) registrarNotificacaoComConexao($c, (int)$destino['freelancer_id'], 'Resposta de vinculo', 'Sua solicitacao foi ' . ($status === 'Aprovado' ? 'aprovada.' : 'recusada.'), 'PerfilFreela.php');
        mysqli_close($c);
    }

    header("Location: ../visao/Funcionarios.php");
    exit();
}

/* CRUD DOS TURNOS / NOTIFICACOES - exige sessão, perfil e CSRF. */
if (isset($_POST['btn_editar_turno']) || isset($_POST['btn_excluir_turno'])) {
    if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'dono') { http_response_code(403); exit('Sem permissao.'); }
    if (!verificarCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit('Formulario expirado.'); }
    $turno_id = (int)($_POST['turno_id'] ?? 0);
    $dono_id = (int)$_SESSION['usuario_id'];
    if (isset($_POST['btn_editar_turno'])) {
        $ok = atualizarTurnoDoDono($turno_id, $dono_id, [
            'descricao'=>$_POST['descricao']??'', 'data_turno'=>$_POST['data']??'',
            'horario_inicio'=>$_POST['inicio']??'', 'horario_fim'=>$_POST['fim']??'', 'valor'=>$_POST['valor']??'', 'funcao'=>$_POST['cargo']??''
        ]);
    } else {
        $ok = excluirTurnoDoDono($turno_id, $dono_id);
    }
    if (!$ok) { http_response_code(400); exit('Falha: verifique os dados e se o turno pertence a voce.'); }
    header('Location: ../visao/PaginaInicialDono.php');exit;
}
if (isset($_POST['btn_marcar_notificacao'])) {
    if (empty($_SESSION['usuario_id']) || !verificarCsrf($_POST['csrf_token']??'')) {http_response_code(403);exit('Sem permissao.');}
    marcarNotificacaoLida((int)($_POST['notificacao_id']??0),(int)$_SESSION['usuario_id']);
    header('Location: ../visao/Notificacoes.php');exit;
}

/* ACTION 9: SOLICITAR REDEFINICAO DE SENHA */
if (isset($_POST['btn_solicitar_redefinicao'])) {
    $email = trim($_POST['email_usuario'] ?? '');
    $token = filter_var($email, FILTER_VALIDATE_EMAIL) ? criarSolicitacaoRedefinicaoSenha($email) : false;
    unset($_SESSION['demo_reset_url']);
    if ($token) {
        $pasta = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $origem = ($isHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        $url = $origem . $pasta . '/visao/RedefinirSenha.php?token=' . urlencode($token);
        if (ambienteDemoLocal()) {
            $_SESSION['demo_reset_url'] = $url;
        } else {
            if (!enviarEmailRecuperacao($email, $url)) error_log('WorkFlow: nao foi possivel enviar email de recuperacao. Configure um servidor de email.');
        }
    }
    // Resposta generica para nao revelar quem possui uma conta.
    header('Location: ../visao/EsqueciSenha.php?msg=enviado'); exit;
}
/* ACTION 10: REDEFINIR SENHA */
if (isset($_POST['btn_redefinir_senha'])) {
    $token = $_POST['token'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    if (strlen($novaSenha) < 8 || $novaSenha !== $confirmarSenha) {
        header('Location: ../visao/RedefinirSenha.php?token=' . urlencode($token) . '&erro=senhas_diferentes'); exit;
    }
    if (redefinirSenhaComToken($token, $novaSenha)) {
        unset($_SESSION['demo_reset_url']);
        header('Location: ../visao/Login.php?msg=senha_redefinida'); exit;
    }
    header('Location: ../visao/EsqueciSenha.php?erro=token_invalido'); exit;
}
header("Location: ../visao/Login.php");
exit();
