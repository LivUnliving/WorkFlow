<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Cadastro - Freelancer</title>
</head>
<body>
    <section id="box">
        <a href="login.php">
            <img id="voltar" src="../imgs/gravity-ui--arrow-left.svg">
        </a>
        <h1>Cadastro para Freelancers:</h1>
        <form id="form" action="../logica/processamento.php" method="POST">
            
            <h3>Nome Completo</h3>
            <input type="text" name="nome_usuario" required>
            
            <h3>E-mail</h3>
            <input type="email" name="email_usuario" required>
            
            <h3>Senha</h3>
            <input type="password" name="senha_usuario" required>
            
            <h3>Código da Loja (fornecido pelo dono)</h3>
            <input type="text" name="codigo_loja" placeholder="Ex: LOJA01" required>

            <h3>Telefone</h3>
            <input type="text" name="telefone">
            
            <h3>Cidade</h3>
            <input type="text" name="cidade">
            
            <h3>CPF</h3>
            <input type="text" name="cpf_usuario">
            
            <input type="hidden" name="tipo_usuario" value="freelancer">

            <button class="btn" id="submit" type="submit" name="btn_cadastrar">CADASTRAR</button>
        </form>
    </section>
</body>
</html>