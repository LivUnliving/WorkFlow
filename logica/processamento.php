<?php
session_start();
require_once "../bd/funcoesBD.php";

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

    $dono_id = $_SESSION['usuario_id'];
    $empresa_id = $_POST['empresa_id'] ?? 0;
    $descricao = $_POST['descricao'] ?? '';
    $data_turno = $_POST['data'] ?? '';
    $horario = $_POST['inicio'] ?? '';
    $funcao = $_POST['cargo'] ?? '';
    $valor = $_POST['valor'] ?? '0.00';
    $funcionarios = $_POST['funcionarios'] ?? [];
    $vagas = count($funcionarios);

    if ($vagas === 0) {
        die("Selecione pelo menos um freelancer para receber o chamado do turno.");
    }

    if (!empresaPertenceAoDono($empresa_id, $dono_id)) {
        die("Empresa inválida para este usuário.");
    }

    $sucesso = inserirTurno($empresa_id, $descricao, $data_turno, $horario, $valor, $funcao, $vagas, $funcionarios);

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

    alterarStatusVinculoFuncionario($vinculo_id, $dono_id, $status);

    header("Location: ../visao/Funcionarios.php");
    exit();
}

/* ACTION 9: SOLICITAR REDEFINIÇÃO DE SENHA */
if (isset($_POST['btn_solicitar_redefinicao'])) {
    $email = $_POST['email_usuario'] ?? '';

    $token = criarSolicitacaoRedefinicaoSenha($email);

    if ($token) {
        header("Location: ../visao/RedefinirSenha.php?token=" . urlencode($token));
        exit();
    }

    header("Location: ../visao/EsqueciSenha.php?erro=email_nao_encontrado");
    exit();
}

/* ACTION 10: REDEFINIR SENHA */
if (isset($_POST['btn_redefinir_senha'])) {
    $token = $_POST['token'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($novaSenha === '' || $novaSenha !== $confirmarSenha) {
        header("Location: ../visao/RedefinirSenha.php?token=" . urlencode($token) . "&erro=senhas_diferentes");
        exit();
    }

    $sucesso = redefinirSenhaComToken($token, $novaSenha);

    if ($sucesso) {
        header("Location: ../visao/Login.php?msg=senha_redefinida");
        exit();
    }

    header("Location: ../visao/EsqueciSenha.php?erro=token_invalido");
    exit();
}

header("Location: ../visao/Login.php");
exit();
