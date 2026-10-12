<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>Cadastro Empresa</title>
</head>
<body>
    <section id="box">
        <a href="Login.php">
            <img id="voltar" src="../imgs/gravity-ui--arrow-left.svg">
        </a>
        <h1>Cadastro para empresas:</h1>
        <form id="form" action="../logica/processamento.php" method="POST">
            <h3>Nome da Empresa</h3>
            <input type="text" name="nome_usuario" required>

            <h3>Senha</h3>
            <input type="password" name="senha_usuario" required>

            <h3>E-mail</h3>
            <input type="email" name="email_usuario" required>

            <h3>Código da Loja</h3>
            <input type="text" name="codigo_loja" placeholder="Ex: LOJA01" required>

            <h3>Telefone</h3>
            <input type="text" name="telefone_empresa" required>

            <h3>Cidade</h3>
            <input type="text" name="cidade_empresa" required>

            <h3>CNPJ</h3>
            <input type="text" name="cnpj_usuario" required>

            <input type="hidden" name="tipo_usuario" value="dono">
            <button class="btn" id="submit" type="submit" name="btn_cadastrar">CADASTRAR</button>
        </form>
    </section>
</body>
</html>
