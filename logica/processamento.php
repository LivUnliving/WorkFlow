<?php
session_start();
require_once "../bd/funcoesBD.php";

/* ==========================================================
   ACTION 1: CADASTRO DE USUÁRIO
========================================================== */
if (isset($_POST['btn_cadastrar'])) {

    $nome = $_POST['nome_usuario'];
    $email = $_POST['email_usuario'];
    $senha = $_POST['senha_usuario'];
    $tipo = $_POST['tipo_usuario'];

    $cpf = $_POST['cpf_usuario'] ?? '';
    $cnpj = $_POST['cnpj_usuario'] ?? '';
    $codigo_loja = $_POST['codigo_loja'] ?? '';

    $sucesso = cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja, $cpf, $cnpj);

    if ($sucesso) {

        if ($tipo == 'dono') {
            header("Location: ../visao/ConfirmaLoja.php");
            exit();
        } elseif ($tipo == 'freelancer') {
            header("Location: ../visao/ConfirmaExp.php");
            exit();
        }

    } else {
        die("Erro no cadastro: " . mysqli_error(conectarBD()));
    }
}


/* ==========================================================
   ACTION 2: LOGIN
========================================================== */
if (isset($_POST['btn_login'])) {

    $email = $_POST['email_usuario'];
    $senha = $_POST['senha_usuario'];

    $usuario = buscarUsuarioPorEmail($email);

    if ($usuario && $usuario['senha'] == $senha) {

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_loja'] = $usuario['codigo_loja'];

        if ($usuario['tipo'] == 'dono') {
            header("Location: ../visao/PaginaInicialDono.php");
            exit();
        } else {
            header("Location: ../visao/PaginaInicialFreela.php");
            exit();
        }

    } else {
        header("Location: ../visao/Login.php?erro=dados_invalidos");
        exit();
    }
}


/* ==========================================================
   ACTION 3: CRIAR TURNO (CORRIGIDO)
========================================================== */
if (isset($_POST['btn_criar_turno'])) {

    $empresa_id = $_SESSION['usuario_id'];
    $descricao = $_POST['descricao_turno'];
    $data_turno = $_POST['data_turno'];
    $horario = $_POST['horario_turno'];
    $valor = $_POST['valor_turno'];
    $funcao = $_POST['funcao_turno'];
    $vagas = $_POST['vagas_turno'];

    $conexao = conectarBD();

    $sql = "INSERT INTO turnos 
    (empresa_id, descricao, data_turno, horario_inicio, valor, funcao, vagas_necessarias, vagas_disponiveis)
    VALUES
    ('$empresa_id', '$descricao', '$data_turno', '$horario', '$valor', '$funcao', '$vagas', '$vagas')";

    $sucesso = mysqli_query($conexao, $sql);

    if ($sucesso) {
        mysqli_close($conexao);
        header("Location: ../visao/ConfirmaTurno.php");
        exit();
    } else {
        die("Erro ao criar turno: " . mysqli_error($conexao));
    }
}


/* ==========================================================
   ACTION 4: EXPERIÊNCIA
========================================================== */
if (isset($_POST['btn_adicionar_experiencia'])) {

    $freelancer_id = $_SESSION['usuario_id'];
    $cargo = $_POST['cargo_experiencia'];
    $tempo = $_POST['tempo_experiencia'];
    $local = $_POST['local_experiencia'];

    $descricao = "Trabalhou como $cargo no estabelecimento $local.";

    $sucesso = cadastrarExperiencia($freelancer_id, $cargo, $tempo, $descricao);

    if ($sucesso) {
        header("Location: ../visao/ConfirmaExp.php");
        exit();
    } else {
        die("Erro ao salvar experiência.");
    }
}


/* ==========================================================
   ACTION 5: ACEITAR TURNO
========================================================== */
if (isset($_POST['btn_aceitar_turno'])) {

    $turno_id = $_POST['turno_id'];
    $freelancer_id = $_SESSION['usuario_id'];

    $conexao = conectarBD();

    $sql = "INSERT INTO candidaturas (turno_id, freelancer_id)
            VALUES ('$turno_id', '$freelancer_id')";

    $executou = mysqli_query($conexao, $sql);

    if ($executou) {

        mysqli_query($conexao,
        "UPDATE turnos SET vagas_disponiveis = vagas_disponiveis - 1 WHERE id = '$turno_id'"
        );

        mysqli_close($conexao);

        header("Location: ../visao/PaginaInicialFreela.php");
        exit();

    } else {
        die("Erro candidatura: " . mysqli_error($conexao));
    }
}


/* ==========================================================
   ACTION 6: VINCULAR LOJA
========================================================== */
if (isset($_POST['btn_vincular_loja'])) {

    $usuario_id = $_SESSION['usuario_id'];
    $novo_codigo = $_POST['codigo_loja'];

    $conexao = conectarBD();

    $sql = "UPDATE usuarios SET codigo_loja = '$novo_codigo' WHERE id = '$usuario_id'";
    $executou = mysqli_query($conexao, $sql);

    if ($executou) {

        $_SESSION['usuario_loja'] = $novo_codigo;

        mysqli_close($conexao);

        header("Location: ../visao/ConfirmaNovaLoja.php");
        exit();

    } else {
        die("Erro ao atualizar loja: " . mysqli_error($conexao));
    }
}


/* ==========================================================
   ACTION 7: CADASTRAR EMPRESA FÍSICA
========================================================== */
if (isset($_POST['btn_adicionar_loja'])) {

    $dono_id = $_SESSION['usuario_id'];
    $nome = $_POST['nome_loja'];
    $telefone = $_POST['telefone_loja'];
    $local = $_POST['local_loja'];
    $cnpj = $_POST['cnpj_loja'];

    $sucesso = cadastrarEmpresaFisica($dono_id, $nome, $telefone, $local, $cnpj);

    if ($sucesso) {
        header("Location: ../visao/ConfirmaLoja.php");
        exit();
    } else {
        die("Erro no banco ao cadastrar empresa.");
    }
}