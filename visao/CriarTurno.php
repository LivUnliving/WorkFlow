<?php
session_start();

// Trava de Segurança: Se não for dono, chuta de volta para o Login
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'dono') {
    header("Location: Login.php");
    exit();
}

require_once "../bd/funcoesBD.php";
$dono_id = $_SESSION['usuario_id'];
$codigo_loja = $_SESSION['usuario_loja'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">    <title>Document</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="paginaInicialEmpresa.html">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Criar novo turno</h3>
        </header>
        <form id="formTurno" action="../logica/processamento.php" method="POST">
            <div class="campo">
                <label>Data</label>
                <input type="date" name="data">
            </div>
            <div class="campo">
                <label>Horário</label>
                <div class="horario">
                    <input type="time" name="inicio">
                    <span>até</span>
                    <input type="time" name="fim">
                </div>
            </div>
            <div class="campo">
                <label>Cargo/Função</label>
                <input
                type="text"
                name="cargo"
                placeholder="Garçom, Caixa, Cozinheiro...">
            </div>
            <div class="campo">
                <label>Detalhes do turno</label>
                <textarea
                name="descricao"
                placeholder="Descreva informações importantes"></textarea>
            </div>
            <div class="campo">
                <label>Funcionários que poderão aceitar</label>
                <div class="listaFuncionarios">
                    <div class="buscarFuncionario">
                        <input
                        type="text"
                        placeholder="Pesquisar">
                        <button
                        type="button"
                        class="btnPesquisa">
                            <img src="../imgs/streamline-sharp--magnifying-glass-solid.svg">
                        </button>
                    </div>
                    <label class="funcionarioCheck">
                        <input type="checkbox" name="funcionarios[]" value="1">Steven Universo
                    </label>
                    <label class="funcionarioCheck">
                        <input type="checkbox" name="funcionarios[]" value="2">Marceline
                    </label>
                    <label class="funcionarioCheck">
                        <input type="checkbox" name="funcionarios[]" value="3">Mordekai
                    </label>
                    <label class="funcionarioCheck">
                        <input type="checkbox" name="funcionarios[]" value="4">Steven Universo
                    </label>
                    <label class="funcionarioCheck">
                        <input type="checkbox" name="funcionarios[]" value="5">Marceline
                    </label>
                    <label class="funcionarioCheck">
                        <input type="checkbox" name="funcionarios[]" value="6">Mordekai
                    </label>
                </div>
            </div>
            <button class="btn" type="submit" id="submit">Publicar turno</button>
        </form>
    </section>
</body>
</html>