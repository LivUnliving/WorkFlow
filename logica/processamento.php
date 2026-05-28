<?php
// logica/processamento.php
session_start();

// Importa o arquivo de funções do banco de dados que criamos
require_once "../bd/funcoesBD.php";

// ==========================================================
// ACTION 1: CADASTRO DE USUÁRIO REVISADO (DONO E FREELANCER)
// ==========================================================
if (isset($_POST['btn_cadastrar'])) {
    $nome = $_POST['nome_usuario'];
    $email = $_POST['email_usuario'];
    $senha = $_POST['senha_usuario'];
    $tipo = $_POST['tipo_usuario']; // 'dono' ou 'freelancer'
    
    // Captura os novos campos ou define como vazio se não existirem no form atual
    $cpf = $_POST['cpf_usuario'] ?? '';
    $cnpj = $_POST['cnpj_usuario'] ?? '';
    $codigo_loja = $_POST['codigo_loja'] ?? ''; 

    // Envia todos os dados alinhados para a função do banco
    $sucesso = cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja, $cpf, $cnpj);

    if ($sucesso) {
        header("Location: ../visao/Login.php?msg=cadastro_sucesso");
        exit();
    } else {
        $conexao = conectarBD();
        die("Erro detectado no cadastro: " . mysqli_error($conexao));
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
            header("Location: ../visao/PaginaInicialDono.php");
            exit();
        } elseif ($usuario['tipo'] == 'freelancer') {
            header("Location: ../visao/PaginaInicialFreela.php");
            exit();
        }
    } else {
        header("Location: ../visao/Login.php?erro=dados_invalidos");
        exit();
    }
}

// ==========================================================
// ACTION 3: DONO CRIANDO NOVO TURNO (Automação de vagas)
// ==========================================================
if (isset($_POST['btn_criar_turno'])) {
    $empresa_id = $_SESSION['usuario_id']; // ID do dono que está logado na sessão
    $descricao = $_POST['descricao_turno'];
    $data_turno = $_POST['data_turno'];
    $horario = $_POST['horario_turno'];
    $valor = $_POST['valor_turno'];
    $funcao = $_POST['funcao_turno']; 
    $vagas_necessarias = $_POST['vagas_turno']; 

    $conexao = conectarBD();

    // Inserção no banco utilizando os nomes reais das suas colunas (com o 'funcao' com til que vimos no phpMyAdmin)
    $sql = "INSERT INTO turnos (empresa_id, descricao, data_turno, horario_inicio, valor, funcao, vagas_necessarias, vagas_disponiveis) 
            VALUES ('$empresa_id', '$descricao', '$data_turno', '$horario', '$valor', '$funcao', '$vagas_necessarias', '$vagas_necessarias')";
    
    if ($sucesso) {
        mysqli_close($conexao);
        header("Location: ../visao/ConfirmaTurno.php");
        exit();
    }
    else {
    die("Erro ao criar turno: " . mysqli_error($conexao));
    }
}

// ==========================================================
// ACTION 4: FREELANCER ADICIONANDO EXPERIÊNCIA NO PERFIL
// ==========================================================
if (isset($_POST['btn_adicionar_experiencia'])) {
    $freelancer_id = $_SESSION['usuario_id']; // Puxa o ID seguro da sessão
    $cargo = $_POST['cargo_experiencia'];     // Puxa a Função digitada
    $tempo = $_POST['tempo_experiencia'];     // Puxa a Duração digitada
    
    // Pegamos o local digitado e montamos uma descrição amigável automaticamente
    $local = $_POST['local_experiencia'];
    $descricao = "Trabalhou como " . $cargo . " no estabelecimento " . $local . ".";

    // Envia os 4 dados prontos para a função do banco de dados
    $sucesso = cadastrarExperiencia($freelancer_id, $cargo, $tempo, $descricao);

    if ($sucesso) {
        // Redireciona para a tela de sucesso
        header("Location: ../visao/ConfirmaExp.php");
        exit();
    } else {
        echo "Erro ao salvar experiência.";
    }
}

// ==========================================================
// ACTION 5: FREELANCER ACEITANDO UM TURNO (CANDIDATURA)
// ==========================================================
if (isset($_POST['btn_aceitar_turno'])) {
    $turno_id = $_POST['turno_id'];
    $freelancer_id = $_SESSION['usuario_id'];

    $conexao = conectarBD();

    // 1. Grava o relacionamento N:M na tabela intermediária
    $sqlCandidatura = "INSERT INTO candidaturas (turno_id, freelancer_id) VALUES ('$turno_id', '$freelancer_id')";
    $executou = mysqli_query($conexao, $sqlCandidatura);

    if ($executou) {
        // 2. Decrementa uma vaga disponível no turno atualizado
        $sqlAtualizaVaga = "UPDATE turnos SET vagas_disponiveis = vagas_disponiveis - 1 WHERE id = '$turno_id'";
        mysqli_query($conexao, $sqlAtualizaVaga);

        // CORREÇÃO: Adicionado a letra "a" que faltava no final do nome do arquivo
        header("Location: ../visao/PaginaInicialFreela.php");
        exit();
    } else {
        echo "Erro ao se candidatar ao turno.";
    }
    mysqli_close($conexao);
}

// ==========================================================
// ACTION 6: DONO VINCULANDO OU ALTERANDO O CÓDIGO DA LOJA
// ==========================================================
if (isset($_POST['btn_vincular_loja'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $novo_codigo = $_POST['codigo_loja'];

    // ... Código anterior da ACTION 6 igual ...
    $sqlVinculo = "UPDATE usuarios SET codigo_loja = '$novo_codigo' WHERE id = '$usuario_id'";
    $executou = mysqli_query($conexao, $sqlVinculo);

    // COLOQUE ISSO AQUI PARA DESCOBRIR O ERRO:
    if (!$executou) {
        die("Erro no Banco de Dados: " . mysqli_error($conexao));
    }

    mysqli_close($conexao);

    if ($executou) {
        $_SESSION['usuario_loja'] = $novo_codigo;
        header("Location: ../visao/ConfirmaNovaLoja.php");
        exit();
    }
}

// ==========================================================
// ACTION 7: CADASTRO DOS DADOS FÍSICOS DA EMPRESA/LOJA
// ==========================================================
if (isset($_POST['btn_adicionar_loja'])) {
    $dono_id = $_SESSION['usuario_id']; // ID do dono logado
    $nome_loja = $_POST['nome_loja'];
    $telefone_loja = $_POST['telefone_loja'];
    $local_loja = $_POST['local_loja'];
    $cnpj_loja = $_POST['cnpj_loja'];

    // Chama a função nova que criamos no funcoesBD.php
    $sucesso = cadastrarEmpresaFisica($dono_id, $nome_loja, $telefone_loja, $local_loja, $cnpj_loja);

    if ($sucesso) {
        // Redireciona para a tela de confirmação de Loja que sua dupla fez!
        header("Location: ../visao/ConfirmaLoja.php");
        exit();
    } else {
        $conexao = conectarBD();
        // Isso vai parar o código e mostrar o erro exato na tela em vez de ficar em branco
        die("Erro detectado no banco: " . mysqli_error($conexao));
    }
}
