<?php
// bd/funcoesBD.php

function conectarBD() {
    $conexao = mysqli_connect("localhost", "root", "", "gestao_freelas", 3307);

    if (!$conexao) {
        die("Falha na conexão com o Banco de Dados: " . mysqli_connect_error());
    }

    return $conexao;
}

// ==========================================
// MÓDULO: AUTENTICAÇÃO (LOGIN E CADASTRO REVISADO)
// ==========================================

// REVISÃO DEFINITIVA: Cadastrar usuários incluindo tratamento de CPF, CNPJ e Status de Vínculo
function cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja, $cpf, $cnpj) {
    $conexao = conectarBD();
    
    // Se for freelancer, entra na lista de espera ('Pendente'). Se for dono, já entra aprovado.
    $status_vinculo = ($tipo == 'freelancer') ? 'Pendente' : 'Aprovado';
    
    // Tratamento para salvar NULL estrutural no MySQL caso os campos venham vazios
    $cpf_valores = !empty($cpf) ? "'$cpf'" : "NULL";
    $cnpj_valores = !empty($cnpj) ? "'$cnpj'" : "NULL";
    $loja_valores = !empty($codigo_loja) ? "'$codigo_loja'" : "NULL";
    
    // Query alinhada perfeitamente com todas as colunas reais da tabela 'usuarios'
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo, codigo_loja, status_vinculo, cpf, cnpj) 
            VALUES ('$nome', '$email', '$senha', '$tipo', $loja_valores, '$status_vinculo', $cpf_valores, $cnpj_valores)";
            
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

// REVISÃO DEFINITIVA: Sincronizado com os nomes reais das colunas de turnos do banco e automação de vagas
function inserirTurno($empresa_id, $descricao, $data_turno, $horario, $valor, $funcao, $vagas_necessarias) {
    $conexao = conectarBD();
    
    // AJUSTE: Mapeado para 'data_turno', 'funcao' (com til) e alimentando automaticamente 'vagas_disponiveis'
    $sql = "INSERT INTO turnos (empresa_id, descricao, data_turno, horario_inicio, valor, funcao, vagas_necessarias, vagas_disponiveis) 
            VALUES ('$empresa_id', '$descricao', '$data_turno', '$horario', '$valor', '$funcao', '$vagas_necessarias', '$vagas_necessarias')";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// Listar todos os turnos criados por uma empresa específica
function retornarTurnosDoDono($empresa_id) {
    $conexao = conectarBD();
    // AJUSTE: Corrigido para ordenar pela coluna real 'data_turno'
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
    // AJUSTE: Corrigido para bater com as colunas físicas reais 'data_turno'
    $sql = "SELECT t.* FROM turnos t 
            JOIN usuarios u ON t.empresa_id = u.id 
            WHERE u.codigo_loja = '$codigo_loja' AND t.data_turno >= CURDATE()
            ORDER BY t.data_turno ASC";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}


// ==========================================
// MÓDULO: CONTROLE DE VAGAS COMPLETAS (REGRA DO SUMIÇO)
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

// Adicione isso no final do seu funcoesBD.php
function cadastrarEmpresaFisica($dono_id, $nome, $telefone, $localizacao, $cnpj) {
    $conexao = conectarBD();
    
    $sql = "INSERT INTO empresas (dono_id, nome, telefone, localizacao, cnpj) 
            VALUES ('$dono_id', '$nome', '$telefone', '$localizacao', '$cnpj')";
            
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}
?>