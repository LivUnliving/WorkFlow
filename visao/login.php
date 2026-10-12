<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css?v=76c42393c7">
    <title>LoginGeral</title>
</head>
<body>
    <section id="box">
        <h1>Login</h1>

        <?php if(isset($_GET['erro']) && $_GET['erro'] == 'dados_invalidos'): ?>
            <p style="color: #ff4d4d; text-align: center; font-weight: bold; font-size: 14px;">
                E-mail ou senha incorretos!
            </p>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'cadastro_sucesso'): ?>
            <p style="color: #2ecc71; text-align: center; font-weight: bold; font-size: 14px;">
                Cadastro realizado com sucesso! Faça o login.
            </p>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'senha_redefinida'): ?>
            <p style="color: #2ecc71; text-align: center; font-weight: bold; font-size: 14px;">
                Senha redefinida com sucesso! Faça o login.
            </p>
        <?php endif; ?>

        <form id="form" action="../logica/processamento.php" method="POST">
            
            <h3>E-mail</h3>
            <input type="text" name="email_usuario" required>
            
            <h3>Senha</h3>
            <input type="password" name="senha_usuario" required>
            
            <button class="btn" id="submit" type="submit" name="btn_login">ENTRAR</button>
            <a href="EsqueciSenha.php" style="font-size: 14px; text-align: center; display: block; margin-top: 10px;">Esqueci minha senha</a>
            
            <h2 id="txtCadastro">É novo por aqui?<br>Cadastre-se como um<br>dos tipos de usuário abaixo:</h2>
            <div id="cadastro">
                <a href="CadastroEmpresa.php">
                    <button type="button" class="btn">EMPRESAS</button>
                </a>
                <a href="CadastroFreela.php">
                    <button type="button" class="btn">FREELANCERS</button>
                </a>     
            </div>    
        </form>
    </section>
</body>
</html>