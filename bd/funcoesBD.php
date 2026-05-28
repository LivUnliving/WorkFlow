<?php
// bd/funcoesBD.php

function conectarBD() {
    $conexao = mysqli_connect("localhost", "root", "", "gestao_freelas", 3307);

    if (!$conexao) {
        die("Falha na conexão com o Banco de Dados: " . mysqli_connect_error());
    }

    mysqli_set_charset($conexao, "utf8mb4");
    return $conexao;
}

function limparTexto($conexao, $valor) {
    return mysqli_real_escape_string($conexao, trim((string)$valor));
}

function gerarCodigoEmpresa($conexao, $codigoDesejado = '') {
    $codigo = strtoupper(preg_replace('/[^A-Z0-9]/', '', $codigoDesejado));

    if ($codigo === '') {
        $codigo = 'LOJA' . rand(1000, 9999);
    }

    $codigoBase = $codigo;
    $contador = 1;

    while (true) {
        $codigoSeguro = limparTexto($conexao, $codigo);
        $sql = "SELECT id FROM empresas WHERE codigo_loja = '$codigoSeguro' LIMIT 1";
        $resultado = mysqli_query($conexao, $sql);

        if (mysqli_num_rows($resultado) === 0) {
            return $codigo;
        }

        $codigo = $codigoBase . $contador;
        $contador++;
    }
}

// ==========================================
// AUTENTICAÇÃO E CADASTRO
// ==========================================

function cadastrarUsuario($nome, $email, $senha, $tipo, $codigo_loja = '', $cpf = '', $cnpj = '', $telefone = '', $cidade = '') {
    $conexao = conectarBD();

    $nome = limparTexto($conexao, $nome);
    $email = limparTexto($conexao, $email);
    $tipo = limparTexto($conexao, $tipo);
    $codigo_loja = limparTexto($conexao, $codigo_loja);
    $cpf = limparTexto($conexao, $cpf);
    $cnpj = limparTexto($conexao, $cnpj);
    $telefone = limparTexto($conexao, $telefone);
    $cidade = limparTexto($conexao, $cidade);
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $emailExistente = mysqli_query($conexao, "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1");
    if (mysqli_num_rows($emailExistente) > 0) {
        mysqli_close($conexao);
        return false;
    }

    $status_vinculo = ($tipo === 'freelancer') ? 'Sem vínculo' : 'Aprovado';
    $codigoUsuario = ($tipo === 'dono') ? gerarCodigoEmpresa($conexao, $codigo_loja) : '';

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo, status_vinculo, cpf, cnpj, telefone, cidade)
            VALUES ('$nome', '$email', '$senhaHash', '$tipo', '$status_vinculo', '$cpf', '$cnpj', '$telefone', '$cidade')";

    if (!mysqli_query($conexao, $sql)) {
        mysqli_close($conexao);
        return false;
    }

    $usuario_id = mysqli_insert_id($conexao);

    if ($tipo === 'dono') {
        $nomeEmpresa = $nome;
        $localizacao = $cidade;
        $codigoEmpresa = $codigoUsuario;

        $sqlEmpresa = "INSERT INTO empresas (dono_id, nome, telefone, localizacao, cnpj, codigo_loja)
                       VALUES ('$usuario_id', '$nomeEmpresa', '$telefone', '$localizacao', '$cnpj', '$codigoEmpresa')";
        mysqli_query($conexao, $sqlEmpresa);
    }

    if ($tipo === 'freelancer' && $codigo_loja !== '') {
        candidatarFreelancerEmpresaComConexao($conexao, $usuario_id, $codigo_loja);
    }

    mysqli_close($conexao);

    return [
        'id' => $usuario_id,
        'tipo' => $tipo,
        'codigo_loja' => $codigoUsuario
    ];
}

function buscarUsuarioPorEmail($email) {
    $conexao = conectarBD();
    $email = limparTexto($conexao, $email);
    $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultado = mysqli_query($conexao, $sql);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_close($conexao);
    return $usuario;
}

function senhaConfere($senhaDigitada, $senhaBanco) {
    if (password_verify($senhaDigitada, $senhaBanco)) {
        return true;
    }

    // Mantém compatibilidade com senhas antigas salvas sem criptografia.
    return $senhaDigitada === $senhaBanco;
}

function buscarUsuarioPorId($usuario_id) {
    $conexao = conectarBD();
    $usuario_id = (int)$usuario_id;
    $resultado = mysqli_query($conexao, "SELECT * FROM usuarios WHERE id = '$usuario_id' LIMIT 1");
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_close($conexao);
    return $usuario;
}

// ==========================================
// EMPRESAS, ESTABELECIMENTOS E VÍNCULOS
// ==========================================

function cadastrarEmpresaFisica($dono_id, $nome, $telefone, $localizacao, $cnpj, $codigo_loja = '') {
    $conexao = conectarBD();

    $dono_id = (int)$dono_id;
    $nome = limparTexto($conexao, $nome);
    $telefone = limparTexto($conexao, $telefone);
    $localizacao = limparTexto($conexao, $localizacao);
    $cnpj = limparTexto($conexao, $cnpj);
    $codigo = gerarCodigoEmpresa($conexao, $codigo_loja);

    $sql = "INSERT INTO empresas (dono_id, nome, telefone, localizacao, cnpj, codigo_loja)
            VALUES ('$dono_id', '$nome', '$telefone', '$localizacao', '$cnpj', '$codigo')";

    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function retornarEmpresasDoDono($dono_id) {
    $conexao = conectarBD();
    $dono_id = (int)$dono_id;
    $sql = "SELECT * FROM empresas WHERE dono_id = '$dono_id' ORDER BY nome ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function empresaPertenceAoDono($empresa_id, $dono_id) {
    $conexao = conectarBD();
    $empresa_id = (int)$empresa_id;
    $dono_id = (int)$dono_id;
    $resultado = mysqli_query($conexao, "SELECT id FROM empresas WHERE id = '$empresa_id' AND dono_id = '$dono_id' LIMIT 1");
    $pertence = mysqli_num_rows($resultado) > 0;
    mysqli_close($conexao);
    return $pertence;
}

function candidatarFreelancerEmpresaComConexao($conexao, $freelancer_id, $codigo_loja) {
    $freelancer_id = (int)$freelancer_id;
    $codigo_loja = limparTexto($conexao, $codigo_loja);

    $resultadoEmpresa = mysqli_query($conexao, "SELECT id FROM empresas WHERE codigo_loja = '$codigo_loja' LIMIT 1");
    $empresa = mysqli_fetch_assoc($resultadoEmpresa);

    if (!$empresa) {
        return false;
    }

    $empresa_id = (int)$empresa['id'];
    $resultadoVinculo = mysqli_query($conexao, "SELECT id FROM usuario_empresas WHERE empresa_id = '$empresa_id' AND freelancer_id = '$freelancer_id' LIMIT 1");

    if (mysqli_num_rows($resultadoVinculo) > 0) {
        $vinculo = mysqli_fetch_assoc($resultadoVinculo);
        $vinculo_id = (int)$vinculo['id'];
        return mysqli_query($conexao, "UPDATE usuario_empresas SET status = 'Pendente' WHERE id = '$vinculo_id' AND status = 'Recusado'");
    }

    return mysqli_query($conexao, "INSERT INTO usuario_empresas (empresa_id, freelancer_id, status) VALUES ('$empresa_id', '$freelancer_id', 'Pendente')");
}

function candidatarFreelancerEmpresa($freelancer_id, $codigo_loja) {
    $conexao = conectarBD();
    $resultado = candidatarFreelancerEmpresaComConexao($conexao, $freelancer_id, $codigo_loja);
    mysqli_close($conexao);
    return $resultado;
}

function retornarFuncionariosConfirmadosDoDono($dono_id) {
    $conexao = conectarBD();
    $dono_id = (int)$dono_id;

    $sql = "SELECT ue.id AS vinculo_id, 
                   u.id AS freelancer_id, 
                   u.nome, 
                   u.email, 
                   u.telefone, 
                   e.nome AS empresa_nome, 
                   e.codigo_loja
            FROM usuario_empresas ue
            INNER JOIN empresas e ON ue.empresa_id = e.id
            INNER JOIN usuarios u ON ue.freelancer_id = u.id
            WHERE e.dono_id = '$dono_id' 
              AND ue.status = 'Aprovado'
            ORDER BY e.nome ASC, u.nome ASC";

    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function retornarCandidaturasPendentesDoDono($dono_id) {
    $conexao = conectarBD();
    $dono_id = (int)$dono_id;
    $sql = "SELECT ef.id AS vinculo_id, u.nome, u.email, u.telefone, e.nome AS empresa_nome, e.codigo_loja
            FROM usuario_empresas ef
            INNER JOIN empresas e ON ef.empresa_id = e.id
            INNER JOIN usuarios u ON ef.freelancer_id = u.id
            WHERE e.dono_id = '$dono_id' AND ef.status = 'Pendente'
            ORDER BY e.nome ASC, u.nome ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function alterarStatusVinculoFuncionario($vinculo_id, $dono_id, $novo_status) {
    $conexao = conectarBD();
    $vinculo_id = (int)$vinculo_id;
    $dono_id = (int)$dono_id;
    $novo_status = limparTexto($conexao, $novo_status);

    $sql = "UPDATE usuario_empresas ef
            INNER JOIN empresas e ON ef.empresa_id = e.id
            SET ef.status = '$novo_status'
            WHERE ef.id = '$vinculo_id' AND e.dono_id = '$dono_id'";

    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function retornarEmpresasDoFreelancer($freelancer_id, $status = 'Aprovado') {
    $conexao = conectarBD();
    $freelancer_id = (int)$freelancer_id;
    $status = limparTexto($conexao, $status);

    $sql = "SELECT e.*, ef.status
            FROM usuario_empresas ef
            INNER JOIN empresas e ON ef.empresa_id = e.id
            WHERE ef.freelancer_id = '$freelancer_id' AND ef.status = '$status'
            ORDER BY e.nome ASC";

    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

// ==========================================
// TURNOS
// ==========================================

function inserirTurno($empresa_id, $descricao, $data_turno, $horario, $valor, $funcao, $vagas_necessarias, $funcionarios = []) {
    $conexao = conectarBD();

    $empresa_id = (int)$empresa_id;
    $descricao = limparTexto($conexao, $descricao);
    $data_turno = limparTexto($conexao, $data_turno);
    $horario = limparTexto($conexao, $horario);
    $valor = (float)$valor;
    $funcao = limparTexto($conexao, $funcao);
    $vagas_necessarias = (int)$vagas_necessarias;

    $sql = "INSERT INTO turnos (empresa_id, descricao, data_turno, horario_inicio, valor, funcao, vagas_necessarias, vagas_disponiveis)
            VALUES ('$empresa_id', '$descricao', '$data_turno', '$horario', '$valor', '$funcao', '$vagas_necessarias', '$vagas_necessarias')";

    if (!mysqli_query($conexao, $sql)) {
        mysqli_close($conexao);
        return false;
    }

    $turno_id = mysqli_insert_id($conexao);

    foreach ($funcionarios as $freelancer_id) {
        $freelancer_id = (int)$freelancer_id;
        if ($freelancer_id > 0) {
            mysqli_query($conexao, "INSERT IGNORE INTO candidaturas (turno_id, freelancer_id, status) VALUES ('$turno_id', '$freelancer_id', 'Pendente')");
        }
    }

    mysqli_close($conexao);
    return true;
}

function retornarTurnosDoDono($dono_id) {
    $conexao = conectarBD();
    $dono_id = (int)$dono_id;
    $sql = "SELECT t.*, e.nome AS empresa_nome, e.codigo_loja
            FROM turnos t
            INNER JOIN empresas e ON t.empresa_id = e.id
            WHERE e.dono_id = '$dono_id'
            ORDER BY t.data_turno ASC, t.horario_inicio ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function retornarTurnosPendentesDoFreelancer($freelancer_id) {
    $conexao = conectarBD();
    $freelancer_id = (int)$freelancer_id;
    $sql = "SELECT c.id AS candidatura_id, c.status, t.*, e.nome AS empresa_nome
            FROM candidaturas c
            INNER JOIN turnos t ON c.turno_id = t.id
            INNER JOIN empresas e ON t.empresa_id = e.id
            WHERE c.freelancer_id = '$freelancer_id'
              AND c.status = 'Pendente'
              AND t.data_turno >= CURDATE()
            ORDER BY t.data_turno ASC, t.horario_inicio ASC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function retornarTurnosRespondidosDoFreelancer($freelancer_id) {
    $conexao = conectarBD();
    $freelancer_id = (int)$freelancer_id;
    $sql = "SELECT c.status, t.*, e.nome AS empresa_nome
            FROM candidaturas c
            INNER JOIN turnos t ON c.turno_id = t.id
            INNER JOIN empresas e ON t.empresa_id = e.id
            WHERE c.freelancer_id = '$freelancer_id'
              AND (c.status <> 'Pendente' OR t.data_turno < CURDATE())
            ORDER BY t.data_turno DESC, t.horario_inicio DESC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function responderTurno($turno_id, $freelancer_id, $status) {
    $conexao = conectarBD();
    $turno_id = (int)$turno_id;
    $freelancer_id = (int)$freelancer_id;
    $status = limparTexto($conexao, $status);

    $resultadoAtual = mysqli_query($conexao, "SELECT status FROM candidaturas WHERE turno_id = '$turno_id' AND freelancer_id = '$freelancer_id' LIMIT 1");
    $atual = mysqli_fetch_assoc($resultadoAtual);

    if (!$atual || $atual['status'] !== 'Pendente') {
        mysqli_close($conexao);
        return false;
    }

    $sql = "UPDATE candidaturas SET status = '$status' WHERE turno_id = '$turno_id' AND freelancer_id = '$freelancer_id'";
    $resultado = mysqli_query($conexao, $sql);

    if ($resultado && $status === 'Confirmado') {
        mysqli_query($conexao, "UPDATE turnos SET vagas_disponiveis = GREATEST(vagas_disponiveis - 1, 0) WHERE id = '$turno_id'");
    }

    mysqli_close($conexao);
    return $resultado;
}

function classeUrgenciaTurno($data_turno) {
    $hoje = new DateTime(date('Y-m-d'));
    $data = new DateTime($data_turno);
    $dias = (int)$hoje->diff($data)->format('%r%a');

    if ($dias <= 2) {
        return 'urgente';
    }

    if ($dias <= 4) {
        return 'atencao';
    }

    return 'normal';
}

// ==========================================
// EXPERIÊNCIAS
// ==========================================

function cadastrarExperiencia($freelancer_id, $cargo, $tempo, $descricao) {
    $conexao = conectarBD();
    $freelancer_id = (int)$freelancer_id;
    $cargo = limparTexto($conexao, $cargo);
    $tempo = limparTexto($conexao, $tempo);
    $descricao = limparTexto($conexao, $descricao);

    $sql = "INSERT INTO experiencias (freelancer_id, cargo, tempo, descricao)
            VALUES ('$freelancer_id', '$cargo', '$tempo', '$descricao')";

    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}

function retornarExperienciasDoFreelancer($freelancer_id) {
    $conexao = conectarBD();
    $freelancer_id = (int)$freelancer_id;
    $sql = "SELECT * FROM experiencias WHERE freelancer_id = '$freelancer_id' ORDER BY id DESC";
    $resultado = mysqli_query($conexao, $sql);
    mysqli_close($conexao);
    return $resultado;
}
?>
