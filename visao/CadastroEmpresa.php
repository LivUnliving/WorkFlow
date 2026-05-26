<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Cadastro</title>
</head>
<body>
    <section id="box">
        <a href="Login.php">
            <img id="voltar" src="../img/gravity-ui--arrow-left.svg">
        </a>
        <h1>Cadastro para empresas:</h1>
        <form id="form" action="../logica/processamento.php" method="POST">
            <h3>Nome da Empresa</h3>
            <input type="text" name="nome_usuario" required>
            <h3>Senha</h3>
            <input type="email" name="email_usuario" required>
            <h3>Telefone</h3>
            <input type="password" name="senha_usuario" required>
            <h3>Criar Código da Loja (Ex: LOJA01)</h3>
            <input type="text" name="codigo_loja" placeholder="Código que seus funcionários usarão" required>
            <h3>Telefone</h3>
            <input type="tel" name="telefone_empresa">
            <h3>Cidade</h3>
            <input type="text" name="cidade_empresa">
            <h3>CNPJ</h3>
            <input type="number" name="cnpj_empresa">
            <input type="hidden" name="tipo_usuario" value="dono">
            <button class="btn" id="submit" type="submit" name="bnt_cadastrar">CADASTRAR</button>       
        </form>
         
    </section>
    
</body>
</html>