<?php
session_start();

// Trava de Segurança de ADS
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];

$conexao = conectarBD();

// 1. Busca os dados da conta do Dono (nome, email, etc.)
$sqlDono = "SELECT * FROM usuarios WHERE id = '$dono_id'";
$resultadoDono = mysqli_query($conexao, $sqlDono);
$dadosDono = mysqli_fetch_assoc($resultadoDono);

// 2. Busca todas as empresas/lojas que pertencem a esse dono na tabela nova
$sqlLojas = "SELECT * FROM empresas WHERE dono_id = '$dono_id' ORDER BY nome ASC";
$listaLojas = mysqli_query($conexao, $sqlLojas);

mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Meu Perfil - Empresa</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PaginaInicialDono.php">
                <img src="../img/gravity-ui--arrow-left.svg">
            </a>
            <h3>Meu perfil</h3>    
        </header>
        
        <main>
            <img id="foto" src="../img/qlementine-icons--user-16.svg" alt="Foto de Perfil" style="display: block; margin: 0 auto; width: 80px;">
            
            <h2 style="text-align: center; margin-top: 10px; color: #333;"><?php echo htmlspecialchars($dadosDono['nome']); ?></h2>
            
            <div class="infoPerfil" style="margin-top: 20px;">
                <h3>E-mail da Conta</h3>
                <p><?php echo htmlspecialchars($dadosDono['email']); ?></p>    
            </div>
            
            <div class="infoPerfil">
                <h3>Código de Vínculo Atual</h3>
                <p><?php echo !empty($dadosDono['codigo_loja']) ? htmlspecialchars($dadosDono['codigo_loja']) : "Nenhum código gerado"; ?></p>    
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div class="linha" style="margin-bottom: 15px;">
                <h3 style="margin-bottom: 10px;">Meus Estabelecimentos Cadastrados:</h3>
                
                <?php 
                if (mysqli_num_rows($listaLojas) > 0) {
                    while ($loja = mysqli_fetch_assoc($listaLojas)) {
                        ?>
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 8px; border-left: 4px solid #28a745;">
                            <strong style="color: #333;"><?php echo htmlspecialchars($loja['nome']); ?></strong>
                            <p style="font-size: 13px; color: #666; margin: 3px 0 0 0;">
                                📍 <?php echo htmlspecialchars($loja['localizacao']); ?> | 📞 <?php echo htmlspecialchars($loja['telefone']); ?>
                            </p>
                        </div>
                        <?php 
                    }
                } else {
                    echo "<p style='color: #aaa; font-style: italic; font-size: 14px;'>Nenhuma empresa física registrada ainda.</p>";
                }
                ?>
                
                <a href="./CadastrarLoja.php" style="text-decoration: none; display: block; margin-top: 15px;">
                    <button class="btn" id="perfilbtn" style="width: 100%;">Nova Empresa +</button>
                </a>
            </div>
        </main>
    </section>
</body>
</html>