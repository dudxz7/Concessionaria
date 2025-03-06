<?php
// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Criptografando a nova senha
$nova_senha = 'SenhaqVCQUER';  // Aqui você coloca a nova senha desejada
$nova_senha_criptografada = password_hash($nova_senha, PASSWORD_DEFAULT);

// Atualizando a senha no banco de dados
$sql = "UPDATE clientes SET senha = ? WHERE email = 'admin@gmail.com'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nova_senha_criptografada);

if ($stmt->execute()) {
    echo "Senha atualizada com sucesso!";
} else {
    echo "Erro ao atualizar a senha: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
