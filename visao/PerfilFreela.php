<?php
// 1. INÍCIO DA SESSÃO: Obrigatório em qualquer página protegida
session_start();

// Se o usuário não estiver logado, chuta ele de volta para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Importa as funções do banco de dados
require_once "../bd/funcoesBD.php";

// Puxa o ID do freelancer que está guardado na sessão
$freelancer_id = $_SESSION['usuario_id'];

// Vamos buscar os dados atualizados desse usuário no banco (para pegar o telefone correto)
$conexao = conectarBD();
$sqlUsuario = "SELECT * FROM usuarios WHERE id = '$freelancer_id'";
$resultadoUsuario = mysqli_query($conexao, $sqlUsuario);
$dadosUsuario = mysqli_fetch_assoc($resultadoUsuario);

// Busca todas as experiências cadastradas por ESTE freelancer
$listaExperiencias = retornarExperienciasDoFreelancer($freelancer_id);
mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Meu Perfil</title>
</head>
<body>
    <section id="boxperfil">
    <header>
        <a href="dashboard_freela.php">
            <img src="../imgs/gravity-ui--arrow-left.svg">
        </a>
        <h3>Meu perfil</h3>    
    </header>
    <main>
        <img id="foto" src="../imgs/qlementine-icons--user-16.svg">
        
        <h2 style="text-align: center; margin-top: 10px;"><?php echo $dadosUsuario['nome']; ?></h2>

        <div class="infoPerfil">
            <h3>Telefone</h3>
            <p><?php echo !empty($dadosUsuario['telefone']) ? $dadosUsuario['telefone'] : "Não cadastrado"; ?></p>    
        </div>
        
        <div class="linha">
            <h3>Minhas Lojas</h3>
            <a href="">
                <button class="btn" id="perfilbtn">Nova Loja + </button>
            </a>
        </div>
        
        <div class="linha">
            <h3>Experiência</h3>
            <a href="RegistrarExp.php">
                <button class="btn" id="perfilbtn">Novo Registro + </button>
            </a> 
        </div>  

        <div class="historico-experiencias" style="margin-top: 20px;">
            <?php 
            // O loop while vai passar de linha em linha puxando as experiências do banco
            if (mysqli_num_rows($listaExperiencias) > 0) {
                while ($exp = mysqli_fetch_assoc($listaExperiencias)) {
                    ?>
                    <div class="card-experiencia-salva" style="background: rgba(255,255,255,0.1); padding: 10px; margin-bottom: 10px; border-radius: 8px;">
                        <h4 style="margin: 0; color: #fff;"><?php echo $exp['cargo']; ?></h4>
                        <small style="color: #ccc;">Duração: <?php echo $exp['tempo']; ?></small>
                        <p style="margin: 5px 0 0 0; font-size: 14px; color: #ddd;"><?php echo $exp['descricao']; ?></p>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='color: #aaa; font-style: italic; text-align: center;'>Nenhuma experiência registrada ainda.</p>";
            }
            ?>
        </div>
        </main>
    </section>
</body>
</html>