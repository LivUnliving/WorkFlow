<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Registrar Experiência</title>
</head>
<body>
    <section id="boxperfil">
        <header>
            <a href="PerfilFreela.php">
                <img src="../imgs/gravity-ui--arrow-left.svg">
            </a>
            <h3>Registrar experiência</h3>    
        </header> 
        
        <form id="form" action="../logica/processamento.php" method="POST">
            
            <h3>Função</h3>
            <input class="inputExp" type="text" name="cargo_experiencia" placeholder="Garçom, barista, etc." required>
            
            <h3>Local</h3>
            <input class="inputExp" type="text" name="local_experiencia" placeholder="Café das rosas, bar esquina, etc." required>
            
            <h3>Duração</h3>
            <input class="inputExp" type="text" name="tempo_experiencia" placeholder="2 meses, 1 ano, etc." required>
            
            <button class="btn" id="btnExp" type="submit" name="btn_adicionar_experiencia">CONFIRMAR</button>
        </form>   
    </section>
</body>
</html>