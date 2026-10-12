<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'freelancer') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$freelancer_id = $_SESSION['usuario_id'];
$dadosUsuario = buscarUsuarioPorId($freelancer_id);
$listaEmpresas = retornarEmpresasDoFreelancer($freelancer_id, 'Aprovado');
$listaPendentes = retornarEmpresasDoFreelancer($freelancer_id, 'Pendente');
$listaExperiencias = retornarExperienciasDoFreelancer($freelancer_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Meu Perfil</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PaginaInicialFreela.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Meu perfil</h3>
        </header>
        <main>
            <img id="foto" src="../imgs/qlementine-icons--user-16.svg" style="display: block; margin: 0 auto; width: 80px;">
            <h2 style="text-align: center; margin-top: 10px; color: #333;"><?php echo htmlspecialchars($dadosUsuario['nome']); ?></h2>

            <div class="infoPerfil">
                <h3>Telefone</h3>
                <p><?php echo !empty($dadosUsuario['telefone']) ? htmlspecialchars($dadosUsuario['telefone']) : "Não cadastrado"; ?></p>
            </div>

            <div class="linha">
                <h3>Empresas com quem trabalho</h3>
                <a href="NovaLoja.php">
                    <button class="btn" id="perfilbtn">Nova Empresa +</button>
                </a>
            </div>

            <div class="empresas">
                <?php
                if (mysqli_num_rows($listaEmpresas) > 0) {
                    while ($empresa = mysqli_fetch_assoc($listaEmpresas)) {
                        ?>
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 8px; border-left: 4px solid #28a745;">
                            <strong><?php echo htmlspecialchars($empresa['nome']); ?></strong>
                            <p style="font-size: 13px; color: #666; margin: 3px 0 0 0;">
                                <?php echo htmlspecialchars($empresa['localizacao']); ?> | Código: <?php echo htmlspecialchars($empresa['codigo_loja']); ?>
                            </p>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic;'>Você ainda não cadastrou nenhuma empresa aprovada.</p>";
                }

                if (mysqli_num_rows($listaPendentes) > 0) {
                    echo "<h4 style='margin-bottom: 0;'>Solicitações pendentes</h4>";
                    while ($empresa = mysqli_fetch_assoc($listaPendentes)) {
                        echo "<p style='font-size: 13px; color: #666; margin: 0;'>" . htmlspecialchars($empresa['nome']) . " - aguardando confirmação do dono.</p>";
                    }
                }
                ?>
            </div>

            <div class="linha">
                <h3>Experiência</h3>
                <a href="RegistrarExp.php">
                    <button class="btn" id="perfilbtn">Novo Registro +</button>
                </a>
            </div>

            <div class="historico-experiencias">
                <?php
                if (mysqli_num_rows($listaExperiencias) > 0) {
                    while ($exp = mysqli_fetch_assoc($listaExperiencias)) {
                        ?>
                        <div class="card-experiencia-salva" style="background: rgba(158, 157, 157, 0.7); padding: 10px; margin-bottom: 10px; border-radius: 8px;">
                            <h4 style="margin: 0; color: #fff;"><?php echo htmlspecialchars($exp['cargo']); ?></h4>
                            <small style="color: #fff;">Duração: <?php echo htmlspecialchars($exp['tempo']); ?></small>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: #fff;"><?php echo htmlspecialchars($exp['descricao']); ?></p>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #777; font-style: italic; text-align: center;'>Nenhuma experiência registrada ainda.</p>";
                }
                ?>
            </div>
        </main>
    </section>
</body>
</html>
