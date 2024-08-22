<?php
$result = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_query = $_POST['search_query'] ?? '';

    // Conecte-se ao banco de dados PostgreSQL
    $conn = pg_connect(
        "host=localhost 
        dbname=Buscapordados
        user=postgres
        password=Rede@147"
    );

    if (!$conn) {
        die("Conexão falhou: " . pg_last_error());
    }

    // Escreva a consulta SQL para buscar os dados com limite de um resultado
    $sql = "SELECT nome, cpf, ddd, telefone1 FROM contatos 
            WHERE nome ILIKE $1 
            OR cpf ILIKE $1 
            OR ddd || telefone1 ILIKE $1 
            LIMIT 1";

    $query_result = pg_query_params($conn, $sql, array('%' . $search_query . '%'));

    if ($query_result && pg_num_rows($query_result) > 0) {
        $row = pg_fetch_assoc($query_result);

        // Trate valores nulos
        $nome = isset($row['nome']) ? htmlspecialchars($row['nome']) : 'Nome não disponível';
        $cpf = isset($row['cpf']) ? htmlspecialchars($row['cpf']) : 'CPF não disponível';
        $ddd = isset($row['ddd']) ? htmlspecialchars($row['ddd']) : '';
        $telefone1 = isset($row['telefone1']) ? htmlspecialchars($row['telefone1']) : '';

        $telefone_completo = $ddd . ' ' . $telefone1;
        $result = "
            <div class='result-item'>
                <span><strong>Nome:</strong> $nome</span><br>
                <span><strong>CPF:</strong> $cpf</span><br>
                <span><strong>Telefone:</strong> $telefone_completo</span>
            </div>";
    } else {
        $result = "<div class='result-item'>Nenhum resultado encontrado.</div>";
    }

    pg_close($conn);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site de Busca</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Busca de Contatos</h1>
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Digite o nome, CPF ou telefone"
         value="<?php echo isset($search_query) ? htmlspecialchars($search_query) : ''; ?>">
        <button type="submit">Buscar</button>
    </form>
    <div id="results">
        <?php echo $result; ?>
    </div>
</body>
</html>
