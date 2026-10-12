<?php
// API JSON do WorkFlow, para o frontend React. A mesma sessao PHP autentica usuarios.
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../bd/funcoesBD.php';
require_once __DIR__ . '/../config/seguranca.php';
function responderJson($dados, $codigo = 200) { http_response_code($codigo);echo json_encode($dados,JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);exit; }
function exigirUsuario($tipo=null) {
    if (empty($_SESSION['usuario_id'])) responderJson(['erro'=>'Faca login novamente.'],401);
    if ($tipo !== null && ($_SESSION['usuario_tipo']??'') !== $tipo) responderJson(['erro'=>'Acao nao permitida.'],403);
    return (int)$_SESSION['usuario_id'];
}
function atualizarEstado() {
    $usuario = empty($_SESSION['usuario_id']) ? null : [
        'id'=>(int)$_SESSION['usuario_id'], 'nome'=>$_SESSION['usuario_nome']??'', 'tipo'=>$_SESSION['usuario_tipo']??''
    ];
    $r=['usuario'=>$usuario, 'csrf'=>tokenCsrf()];
    if (!$usuario) return $r;
    $id=$usuario['id'];
    $r['notificacoes']=retornarNotificacoes($id);
    $r['naoLidas']=contarNotificacoesNaoLidas($id);
    if ($usuario['tipo']==='dono') {
        $r['turnos']=mysqli_fetch_all(retornarTurnosDoDono($id),MYSQLI_ASSOC);
        $r['empresas']=mysqli_fetch_all(retornarEmpresasDoDono($id),MYSQLI_ASSOC);
        $r['funcionarios']=mysqli_fetch_all(retornarFuncionariosConfirmadosDoDono($id),MYSQLI_ASSOC);
    } else {
        $r['pendentes']=mysqli_fetch_all(retornarTurnosPendentesDoFreelancer($id),MYSQLI_ASSOC);
        $r['respondidos']=mysqli_fetch_all(retornarTurnosRespondidosDoFreelancer($id),MYSQLI_ASSOC);
    }
    return $r;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') responderJson(atualizarEstado());
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJson(['erro'=>'Metodo nao permitido.'],405);
$raw=file_get_contents('php://input');
$dados=json_decode($raw, true);
if (!is_array($dados)) responderJson(['erro'=>'Corpo JSON invalido.'],400);
$acao=$dados['acao']??'';
if ($acao === 'login') {
    $email=trim($dados['email']??''); $senha=$dados['senha']??'';
    $u=buscarUsuarioPorEmail($email);
    if (!$u || !senhaConfere($senha,$u['senha'])) responderJson(['erro'=>'E-mail ou senha incorretos.'],401);
    session_regenerate_id(true);
    $_SESSION['usuario_id']=(int)$u['id'];$_SESSION['usuario_nome']=$u['nome'];$_SESSION['usuario_tipo']=$u['tipo'];
    responderJson(atualizarEstado());
}
$uid=exigirUsuario();
if (!verificarCsrf($dados['csrf'] ?? '')) responderJson(['erro'=>'Sessao expirada. Atualize a pagina.'],403);
if ($acao === 'logout') {
    $_SESSION=[];if (ini_get('session.use_cookies')) {
        $params=session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']);
    }
    session_destroy(); responderJson(['ok'=>true]);
}
if ($acao === 'marcar_lida') {
    marcarNotificacaoLida((int)($dados['id']??0),$uid);responderJson(atualizarEstado());
}
if ($acao === 'responder_turno') {
    exigirUsuario('freelancer');
    $status=($dados['status']??'')==='Confirmado'?'Confirmado':(($dados['status']??'')==='Recusado'?'Recusado':null);
    if (!$status || !responderTurno((int)($dados['id']??0),$uid,$status)) responderJson(['erro'=>'Nao foi possivel responder ao turno.'],400);
    responderJson(atualizarEstado());
}
if (in_array($acao, ['criar_turno','editar_turno','excluir_turno'],true)) {
    exigirUsuario('dono');
    if ($acao==='excluir_turno') {
        if (!excluirTurnoDoDono((int)($dados['id']??0),$uid)) responderJson(['erro'=>'Turno nao encontrado ou sem permissao.'],404);
    } elseif ($acao==='editar_turno') {
        if (!atualizarTurnoDoDono((int)($dados['id']??0),$uid,$dados)) responderJson(['erro'=>'Confira os dados do turno.'],400);
    } else {
        $empresa=(int)($dados['empresa_id']??0);
        if (!empresaPertenceAoDono($empresa,$uid)) responderJson(['erro'=>'Esta empresa nao pertence a voce.'],403);
        $ids=$dados['funcionarios']??[];
        if (!is_array($ids)) responderJson(['erro'=>'Funcionarios invalidos.'],400);
        $ids=array_values(array_unique(array_map('intval',$ids)));
        $aprovados=[];
        $c=conectarBD();$st=mysqli_prepare($c,"SELECT freelancer_id FROM usuario_empresas WHERE empresa_id=? AND status='Aprovado'");
        mysqli_stmt_bind_param($st,'i',$empresa);mysqli_stmt_execute($st);
        foreach (mysqli_fetch_all(mysqli_stmt_get_result($st),MYSQLI_ASSOC) as $linha) $aprovados[]=(int)$linha['freelancer_id'];
        mysqli_close($c);
        if (!$ids || array_diff($ids,$aprovados)) responderJson(['erro'=>'Escolha ao menos um freelancer aprovado para a empresa escolhida.'],400);
        $descricao=trim($dados['descricao']??'');
        $funcao=trim($dados['funcao']??'');
        $data=$dados['data_turno']??'';
        $hora=$dados['horario_inicio']??'';
        $horaFim=$dados['horario_fim']??'';
        $valor=(float)($dados['valor']??-1);
        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$data);
        if (!$d || $d->format('Y-m-d')!==$data || !preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', substr($hora,0,5))
            || $descricao==='' || mb_strlen($descricao)>150 || $funcao==='' || mb_strlen($funcao)>50 || $valor<0)
            responderJson(['erro'=>'Preencha os campos de turno corretamente.'],400);
        if (!inserirTurno($empresa,$descricao,$data,$hora,$valor,$funcao,count($ids),$ids)) responderJson(['erro'=>'Nao foi possivel cadastrar o turno.'],500);
    }
    responderJson(atualizarEstado());
}
responderJson(['erro'=>'Acao desconhecida.'],400);
?>
