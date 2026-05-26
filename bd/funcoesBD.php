<?php
// bd/funcoesBD.php

// 1. FUNÇÃO DE CONEXÃO (Configurada para a porta 3307 do XAMPP)
function conectarBD() {
    $servidor = "localhost:3307";
    $usuario = "root";
    $senha = "";
    $banco = "gestao_freelas";

    $conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

    if (!$conexao) {
        die("Falha na conexão com o Banco de Dados: " . mysqli_connect_error());
    }

    return $conexao;
}

// ==========================================
// MÓDULO: AUTENTICAÇÃO (LOGIN E CADASTRO)
// ==========================================

// Função para cadastrar donos ou freelancers (vínculo pendente por padrão para freelas)
function cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja) {
    $conexao = conectarBD();
    
    // Se for freelancer, entra na lista de espera ('Pendente'). Se for dono, já entra ativo.
    $status_vinculo = ($tipo == 'freelancer') ? 'Pendente' : 'Aprovado';
    
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo, codigo_loja, status_vinculo) 
            VALUES ('$nome', '$email', '$senha', '$tipo', '$codigo_loja', '$status_vinculo')";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Função para buscar usuário por e-mail (usada na validação de login)
function buscarUsuarioPorEmail($email) {
    $conexao = conectarBD();
    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conexao, $sql);
    
    // Converte o resultado em um array associativo
    $usuario = mysqli_fetch_assoc($resultado);
    
    mysqli_close($conexao);
    return $usuario; // Retorna o usuário encontrado ou null
}


// ==========================================
// MÓDULO: FLUXO DO DONO DA EMPRESA
// ==========================================

// Função para o dono criar um turno novo (com limite de vagas, sem urgência manual)
function inserirTurno($empresa_id, $descricao, $data_turno, $horario, $valor, $funcao, $vagas_necessarias) {
    $conexao = conectarBD();
    $sql = "INSERT INTO turnos (empresa_id, descricao, data_turno, horario_inicio, valor, funcao, vagas_necessarias) 
            VALUES ('$empresa_id', '$descricao', '$data_turno', '$horario', '$valor', '$funcao', '$vagas_necessarias')";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Listar todos os turnos criados por uma empresa específica
function retornarTurnosDoDono($empresa_id) {
    $conexao = conectarBD();
    $sql = "SELECT * FROM turnos WHERE empresa_id = '$empresa_id' ORDER BY data_turno ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado; // Retorna a lista de registros do MySQL
}

// Listar os funcionários aceitos/aprovados na loja específica do dono
function retornarFuncionariosDaLoja($codigo_loja) {
    $conexao = conectarBD();
    $sql = "SELECT id, nome, email FROM usuarios 
            WHERE codigo_loja = '$codigo_loja' AND tipo = 'freelancer' AND status_vinculo = 'Aprovado' 
            ORDER BY nome ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Listar os freelancers que usaram o código e estão na "Lista de Espera" da loja
function retornarListaEsperaLoja($codigo_loja) {
    $conexao = conectarBD();
    $sql = "SELECT id, nome, email FROM usuarios 
            WHERE codigo_loja = '$codigo_loja' AND tipo = 'freelancer' AND status_vinculo = 'Pendente' 
            ORDER BY nome ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Alterar o status do freelancer na loja (Aceitar funcionário na equipe)
function alterarStatusVinculoFuncionario($freelancer_id, $novo_status) {
    $conexao = conectarBD();
    $sql = "UPDATE usuarios SET status_vinculo = '$novo_status' WHERE id = '$freelancer_id'";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}


// ==========================================
// MÓDULO: FLUXO DO FREELANCER (PERFIL E VAGAS)
// ==========================================

// Função para o freelancer alimentar as suas experiências no perfil
function cadastrarExperiencia($freelancer_id, $cargo, $tempo, $descricao) {
    $conexao = conectarBD();
    $sql = "INSERT INTO experiencias (freelancer_id, cargo, tempo, descricao) 
            VALUES ('$freelancer_id', '$cargo', '$tempo', '$descricao')";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Buscar as experiências de um freelancer específico (para o dono consultar)
function retornarExperienciasDoFreelancer($freelancer_id) {
    $conexao = conectarBD();
    $sql = "SELECT * FROM experiencias WHERE freelancer_id = '$freelancer_id'";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Listar turnos criados na loja em que o freelancer está cadastrado
function retornarTurnosDisponiveisParaFreela($codigo_loja) {
    $conexao = conectarBD();
    // Faz um JOIN básico para trazer turnos apenas da empresa que possui o mesmo código de loja do freela
    $sql = "SELECT t.* FROM turnos t 
            JOIN usuarios u ON t.empresa_id = u.id 
            WHERE u.codigo_loja = '$codigo_loja' AND t.data_turno >= CURDATE()
            ORDER BY t.data_turno ASC";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}


// ==========================================
// MÓDULO: CONTROLO DE VAGAS COMPLETAS (REGRA DO SUMIÇO)
// ==========================================

// Função auxiliar para contar quantas pessoas confirmaram presença em um turno específico
function contarVagasPreenchidasNoTurno($turno_id) {
    $conexao = conectarBD();
    // Conta quantas candidaturas foram aceitas/confirmadas para aquele ID de turno
    $sql = "SELECT COUNT(*) as total FROM candidaturas 
            WHERE turno_id = '$turno_id' AND (status = 'Aceito' OR status = 'Confirmado')";
            
    $resultado = mysqli_query($conexao, $sql);
    $dados = mysqli_fetch_assoc($resultado);
    mysqli_close($conexao);
    
    return $dados['total']; // Retorna um número inteiro (ex: 0, 1, 2...)
}
?>

