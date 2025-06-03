<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$id = $_SESSION['usuarioId'];
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sistema_bmw';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Erro de conexão']);
    exit;
}

if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Nenhum arquivo enviado']);
    exit;
}

$file = $_FILES['profile_image'];
$extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$tipos_mime_permitidos = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif'
];
$max_tamanho = 2 * 1024 * 1024; // 2MB
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $extensoes_permitidas) || $file['size'] > $max_tamanho) {
    echo json_encode(['success' => false, 'error' => 'Arquivo inválido ou muito grande']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime_type, $tipos_mime_permitidos)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido']);
    exit;
}

// Busca foto antiga
$stmt = $conn->prepare('SELECT foto_perfil FROM clientes WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($foto_antiga);
$stmt->fetch();
$stmt->close();

// Remove foto antiga se existir
if (!empty($foto_antiga) && file_exists(__DIR__ . '/../img/perfis/' . basename($foto_antiga))) {
    @unlink(__DIR__ . '/../img/perfis/' . basename($foto_antiga));
}

$novo_nome = 'perfil_' . $id . '_' . time() . '.' . $ext;
$destino = __DIR__ . '/../img/perfis/' . $novo_nome;
if (!move_uploaded_file($file['tmp_name'], $destino)) {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar arquivo']);
    exit;
}

$caminho_web = 'img/perfis/' . $novo_nome;
$stmt = $conn->prepare('UPDATE clientes SET foto_perfil = ? WHERE id = ?');
$stmt->bind_param('si', $caminho_web, $id);
$stmt->execute();
$stmt->close();

// Atualiza sessão
$_SESSION['usuarioFotoPerfil'] = $caminho_web;

$conn->close();
echo json_encode(['success' => true, 'caminho' => '../' . $caminho_web]);
