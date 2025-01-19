<?php
// Configurações de conexão com o banco de dados MySQL
$servername = "127.0.0.1";
$username = "root";
$password = "1337";
$dbname = "agendamentos";

try {
    // Criar conexão usando PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar o modo de erro do PDO para exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Configurar o idioma para português
    setlocale(LC_TIME, 'pt_BR.UTF-8', 'Portuguese_Brazil.1252');

    // Obter os dados do formulário
    $destino = $_POST['destino'];
    $data_chegada = $_POST['data_chegada'];
    $data_saida = $_POST['data_saida'];
    $numero_pessoas = $_POST['numero_pessoas'];

    // Obter o nome do mês da data de chegada em português
    $mes_nome = strftime('%B', strtotime($data_chegada));
    echo "Mês identificado: " . $mes_nome . "<br>";

    // Consultar o ID do mês correspondente
    $stmt_mes = $conn->prepare("SELECT id FROM meses WHERE LOWER(nome) = LOWER(?)");
    $stmt_mes->execute([$mes_nome]);
    $result_mes = $stmt_mes->fetch(PDO::FETCH_ASSOC);

    if ($result_mes) {
        $mes_id = $result_mes['id'];
        echo "ID do mês: " . $mes_id . "<br>";
    } else {
        die("Erro: Mês '$mes_nome' não encontrado no banco de dados.");
    }

    // Iniciar transação
    $conn->beginTransaction();

    try {
        // Inserir a reserva na tabela `reservas`
        $stmt_reserva = $conn->prepare(
            "INSERT INTO reservas (destino, data_chegada, data_saida, numero_pessoas, mes_id)
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt_reserva->execute([$destino, $data_chegada, $data_saida, $numero_pessoas, $mes_id])) {
            // Confirma a transação
            $conn->commit();
            echo "Reserva registrada com sucesso!";
        } else {
            // Desfaz a transação em caso de erro
            $conn->rollBack();
            echo "Erro ao registrar a reserva.";
        }
    } catch (Exception $e) {
        // Em caso de exceção, desfaz a transação
        $conn->rollBack();
        echo "Erro durante a transação: " . $e->getMessage();
    }
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}
?>
<div style="margin-top: 20px;">
    <form action="http://localhost/projeto" method="get">
        <button type="submit" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Voltar à Página Inicial</button>
    </form>
</div>
