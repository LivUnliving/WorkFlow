<?php
// logica/processamento.php
session_start();

// Importa o arquivo de funções do banco de dados que criamos
require_once "../bd/funcoesBD.php";

// ==========================================================
// ACTION 1: CADASTRO DE USUÁRIO (DONO OU FREELANCER)
// ==========================================================
if (isset($_POST['btn_cadastrar'])) {
    $nome = $_POST['nome_usuario'];
    $email = $_POST['email_usuario'];
    $senha = $_POST['senha_usuario'];
    $tipo = $_POST['tipo_usuario']; // 'dono' ou 'freelancer'
    $codigo_loja = $_POST['codigo_loja']; // Código de sala/loja para vincular

    // Executa a função mapeada no funcoesBD.php
    $sucesso = cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja);

    if ($sucesso) {
        // Redireciona para o login informando sucesso na URL
        header("Location: ../visao/login.php?msg=cadastro_sucesso");
        exit();
    } else {
        echo "Erro ao salvar o usuário no banco de dados.";
    }
}

// ==========================================================
// ACTION 2: FLUXO DE LOGIN UNIFICADO
// ==========================================================
if (isset($_POST['btn_login'])) {
    $email = $_POST['email_usuario'];
    $senha = $_POST['senha_usuario'];

    // Busca os dados do usuário no banco através do e-mail
    $usuario = buscarUsuarioPorEmail($email);

    // Valida se o usuário existe e se a senha está correta
    if ($usuario && $usuario['senha'] == $senha) {
        
        // Guarda as informações cruciais do usuário na Sessão ($_SESSION)
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_loja'] = $usuario['codigo_loja'];

        // Redireciona para a tela inicial baseada no TIPO de usuário
        if ($usuario['tipo'] == 'dono') {
            header("Location: ../visao/dashboard_dono.php");
            exit();
        } elseif ($usuario['tipo'] == 'freelancer') {
            header("Location: ../visao/dashboard_freela.php");
            exit();
        }
    } else {
        // Se errar a senha ou e-mail, volta para o login com aviso de erro
        header("Location: ../visao/login.php?erro=dados_invalidos");
        exit();
    }
}

// ==========================================================
// ACTION 3: DONO CRIANDO NOVO TURNO (Sem urgência manual)
// ==========================================================
if (isset($_POST['btn_criar_turno'])) {
    $empresa_id = $_SESSION['usuario_id']; // ID do dono que está logado
    $descricao = $_POST['descricao_turno'];
    $data_turno = $_POST['data_turno'];
    $horario = $_POST['horario_turno'];
    $valor = $_POST['valor_turno'];
    $funcao = $_POST['funcao_turno']; // Ex: Garçom, Caixa
    $vagas_necessarias = $_POST['vagas_turno']; // Nova coluna!

    $sucesso = inserirTurno($empresa_id, $descricao, $data_turno, $horario, $valor, $funcao, $vagas_necessarias);

    if ($sucesso) {
        header("Location: ../visao/dashboard_dono.php?msg=turno_criado");
        exit();
    } else {
        echo "Erro ao criar o turno.";
    }
}

// ==========================================================
// ACTION 4: FREELANCER ADICIONANDO EXPERIÊNCIA NO PERFIL
// ==========================================================
if (isset($_POST['btn_adicionar_experiencia'])) {
    $freelancer_id = $_SESSION['usuario_id']; // ID do freela logado
    $cargo = $_POST['cargo_experiencia'];
    $tempo = $_POST['tempo_experiencia'];
    $descricao = $_POST['descricao_experiencia'];

    $sucesso = cadastrarExperiencia($freelancer_id, $cargo, $tempo, $descricao);

    if ($sucesso) {
        // Redireciona de volta para o perfil do freelancer
        header("Location: ../visao/perfil_freela.php?msg=exp_adicionada");
        exit();
    } else {
        echo "Erro ao salvar experiência.";
    }
}
?>