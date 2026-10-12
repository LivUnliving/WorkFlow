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
    return !preg_match('/^\$2[aby]\$|^\$argon2/', $senhaBanco) && hash_equals((string)$senhaBanco, (string)$senhaDigitada);
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

// TURNOS//

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
            $adicionou = mysqli_query($conexao, "INSERT IGNORE INTO candidaturas (turno_id, freelancer_id, status) VALUES ('$turno_id', '$freelancer_id', 'Pendente')");
            if ($adicionou && mysqli_affected_rows($conexao) > 0) {
                registrarNotificacaoComConexao($conexao, $freelancer_id, 'Convite de turno', 'Voce recebeu um novo convite de trabalho.', 'PaginaInicialFreela.php');
            }
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
    if ($resultado) {
        $alvo = mysqli_query($conexao, "SELECT e.dono_id FROM turnos t INNER JOIN empresas e ON e.id=t.empresa_id WHERE t.id='$turno_id' LIMIT 1");
        $dono = mysqli_fetch_assoc($alvo);
        if ($dono) registrarNotificacaoComConexao($conexao, (int)$dono['dono_id'], 'Resposta a turno', 'Um freelancer respondeu a um convite: ' . $status, 'PaginaInicialDono.php');
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

    if ($dias <= 7) {
        return 'normal';
    }

    return 'sem_urgencia';
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

function criarSolicitacaoRedefinicaoSenha($email) {
    $conexao = conectarBD();
    $email = limparTexto($conexao, $email);

    $resultado = mysqli_query($conexao, "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1");
    $usuario = mysqli_fetch_assoc($resultado);

    if (!$usuario) {
        mysqli_close($conexao);
        return false;
    }

    $usuario_id = (int)$usuario['id'];

    $token = bin2hex(random_bytes(32));
    $tokenHash = password_hash($token, PASSWORD_DEFAULT);
    $expiraEm = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    mysqli_query($conexao, "UPDATE redefinicoes_senha SET usado = 1 WHERE usuario_id = '$usuario_id' AND usado = 0");

    $tokenHashSeguro = limparTexto($conexao, $tokenHash);

    $sql = "INSERT INTO redefinicoes_senha (usuario_id, token_hash, expira_em)
            VALUES ('$usuario_id', '$tokenHashSeguro', '$expiraEm')";

    $sucesso = mysqli_query($conexao, $sql);

    mysqli_close($conexao);

    if (!$sucesso) {
        return false;
    }

    return $token;
}

function buscarSolicitacaoRedefinicaoValida($token) {
    $conexao = conectarBD();

    $agora = date('Y-m-d H:i:s');

    $sql = "SELECT rs.*, u.email
            FROM redefinicoes_senha rs
            INNER JOIN usuarios u ON rs.usuario_id = u.id
            WHERE rs.usado = 0
              AND rs.expira_em >= '$agora'
            ORDER BY rs.id DESC";

    $resultado = mysqli_query($conexao, $sql);

    while ($linha = mysqli_fetch_assoc($resultado)) {
        if (password_verify($token, $linha['token_hash'])) {
            mysqli_close($conexao);
            return $linha;
        }
    }

    mysqli_close($conexao);
    return false;
}

function redefinirSenhaComToken($token, $novaSenha) {
    $solicitacao = buscarSolicitacaoRedefinicaoValida($token);

    if (!$solicitacao) {
        return false;
    }

    $conexao = conectarBD();

    $usuario_id = (int)$solicitacao['usuario_id'];
    $solicitacao_id = (int)$solicitacao['id'];
    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $senhaHash = limparTexto($conexao, $senhaHash);

    $sqlSenha = "UPDATE usuarios SET senha = '$senhaHash' WHERE id = '$usuario_id'";
    $atualizouSenha = mysqli_query($conexao, $sqlSenha);

    if ($atualizouSenha) {
        mysqli_query($conexao, "UPDATE redefinicoes_senha SET usado = 1 WHERE id = '$solicitacao_id'");
    }

    mysqli_close($conexao);

    return $atualizouSenha;
}


// Edição/exclusão: sempre cruzar empresa do turno com o dono autenticado.
function buscarTurnoDoDono($turno_id, $dono_id) {
    $c = conectarBD();
    $sql = 'SELECT t.*, e.nome AS empresa_nome FROM turnos t INNER JOIN empresas e ON e.id=t.empresa_id WHERE t.id=? AND e.dono_id=? LIMIT 1';
    $st = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($st, 'ii', $turno_id, $dono_id);
    mysqli_stmt_execute($st);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_close($c); return $r ?: false;
}
function atualizarTurnoDoDono($turno_id, $dono_id, $dados) {
    if (!buscarTurnoDoDono($turno_id, $dono_id)) return false;
    $descricao = trim($dados['descricao'] ?? '');
    $funcao = trim($dados['funcao'] ?? '');
    $data = $dados['data_turno'] ?? '';
    $hora = $dados['horario_inicio'] ?? '';
    $valor = (float)($dados['valor'] ?? -1);
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    if (!$d || $d->format('Y-m-d') !== $data || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', substr($hora,0,5))
        || $descricao === '' || mb_strlen($descricao)>150 || $funcao === '' || mb_strlen($funcao)>50 || $valor < 0) return false;
    $c = conectarBD();
    $st = mysqli_prepare($c, 'UPDATE turnos t INNER JOIN empresas e ON e.id=t.empresa_id SET t.descricao=?, t.data_turno=?, t.horario_inicio=?, t.valor=?, t.funcao=? WHERE t.id=? AND e.dono_id=?');
    mysqli_stmt_bind_param($st, 'sssdsii', $descricao, $data, $hora, $valor, $funcao, $turno_id, $dono_id);
    $ok = mysqli_stmt_execute($st);
    if ($ok) {
        $r = mysqli_query($c, 'SELECT freelancer_id FROM candidaturas WHERE turno_id=' . (int)$turno_id);
        while ($f = mysqli_fetch_assoc($r)) registrarNotificacaoComConexao($c, (int)$f['freelancer_id'], 'Turno atualizado', 'Os dados de um turno para o qual voce foi convidado foram alterados.', 'PaginaInicialFreela.php');
    }
    mysqli_close($c); return $ok;
}
function excluirTurnoDoDono($turno_id, $dono_id) {
    $c = conectarBD();
    $st = mysqli_prepare($c, 'DELETE t FROM turnos t INNER JOIN empresas e ON e.id=t.empresa_id WHERE t.id=? AND e.dono_id=?');
    mysqli_stmt_bind_param($st, 'ii', $turno_id, $dono_id);
    $ok = mysqli_stmt_execute($st) && mysqli_stmt_affected_rows($st) === 1;
    mysqli_close($c); return $ok;
}
function registrarNotificacaoComConexao($c, $usuario_id, $titulo, $mensagem, $link) {
    $st = mysqli_prepare($c, 'INSERT INTO notificacoes (usuario_id, titulo, mensagem, link) VALUES (?, ?, ?, ?)');
    mysqli_stmt_bind_param($st, 'isss', $usuario_id, $titulo, $mensagem, $link);
    return mysqli_stmt_execute($st);
}
function registrarNotificacao($usuario_id, $titulo, $mensagem, $link) {
    $c=conectarBD();$ok=registrarNotificacaoComConexao($c,$usuario_id,$titulo,$mensagem,$link);mysqli_close($c);return $ok;
}
function retornarNotificacoes($usuario_id) {
    $c=conectarBD();
    $st=mysqli_prepare($c,'SELECT * FROM notificacoes WHERE usuario_id=? ORDER BY criada_em DESC, id DESC LIMIT 60');
    mysqli_stmt_bind_param($st,'i',$usuario_id);mysqli_stmt_execute($st);
    $r=mysqli_stmt_get_result($st);$rows=mysqli_fetch_all($r,MYSQLI_ASSOC);mysqli_close($c);return $rows;
}
function contarNotificacoesNaoLidas($usuario_id) {
    $c=conectarBD();$st=mysqli_prepare($c,'SELECT COUNT(*) AS qtd FROM notificacoes WHERE usuario_id=? AND lida=0');
    mysqli_stmt_bind_param($st,'i',$usuario_id);mysqli_stmt_execute($st);$row=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_close($c);return (int)$row['qtd'];
}
function marcarNotificacaoLida($notificacao_id, $usuario_id) {
    $c=conectarBD();$st=mysqli_prepare($c,'UPDATE notificacoes SET lida=1 WHERE id=? AND usuario_id=?');
    mysqli_stmt_bind_param($st,'ii',$notificacao_id,$usuario_id);$ok=mysqli_stmt_execute($st);mysqli_close($c);return $ok;
}
function listarTurnosDoDonoArray($dono_id) {return mysqli_fetch_all(retornarTurnosDoDono($dono_id), MYSQLI_ASSOC);}

?>
