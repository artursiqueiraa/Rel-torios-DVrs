<?php
header('Content-Type: application/json');
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'dvr';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Erro ao conectar ao banco de dados: ' . $e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Erro ao conectar ao banco de dados']));
}

// Criar diretório para uploads, se não existir
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$input = file_get_contents('php://input');
error_log('Input recebido: ' . $input);

$data = json_decode($input, true) ?? [];
$action = $data['action'] ?? (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : ''));

error_log('Ação detectada: ' . $action);

switch ($action) {
    case 'cadastrar':
        $formData = $data['data'] ?? [];
        if (!empty($_FILES)) {
            foreach ($_POST as $key => $value) {
                $formData[$key] = $value;
            }
            if (isset($_FILES['foto_problema']) && $_FILES['foto_problema']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['foto_problema']['tmp_name'];
                $fileName = $_FILES['foto_problema']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = uniqid() . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $formData['foto_problema'] = $destPath;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a foto']);
                    exit;
                }
            }
        }

        if (!$formData) {
            echo json_encode(['status' => 'error', 'message' => 'Dados não fornecidos']);
            exit;
        }

        $edificioFields = ['nome_edificio', 'faixa_ip_edificio', 'faixa_ip_dvr'];
        $dvrFields = ['modelo', 'marca', 'versao_firmware_atual', 'ultima_versao_firmware', 'status_atualizacao', 'link_dvr', 'capacidade_hd', 'dias_gravados', 'tipo_gravacao'];
        $cameraFields = ['tem_problema', 'numero_cameras_problema', 'detalhes_problema', 'ocorrencia_aberta', 'link_ocorrencia', 'tem_cameras_ip', 'numeros_cameras_ip', 'tem_canais_vagos', 'canais_vagos', 'foto_problema'];

        $edificioParams = array_intersect_key($formData, array_flip($edificioFields));
        $dvrParams = array_intersect_key($formData, array_flip($dvrFields));
        $cameraParams = array_intersect_key($formData, array_flip($cameraFields));

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO Edificios (" . implode(', ', $edificioFields) . ") VALUES (:" . implode(', :', $edificioFields) . ")");
            $stmt->execute(array_filter($edificioParams));
            $id_edificio = $pdo->lastInsertId();

            $dvrParams['id_edificio'] = $id_edificio;
            $stmt = $pdo->prepare("INSERT INTO DVRs (" . implode(', ', array_keys($dvrParams)) . ") VALUES (:" . implode(', :', array_keys($dvrParams)) . ")");
            $stmt->execute(array_filter($dvrParams));
            $id_dvr = $pdo->lastInsertId();

            $cameraParams['id_dvr'] = $id_dvr;
            $stmt = $pdo->prepare("INSERT INTO Cameras (" . implode(', ', array_keys($cameraParams)) . ") VALUES (:" . implode(', :', array_keys($cameraParams)) . ")");
            foreach ($cameraParams as $key => $value) {
                $stmt->bindValue(":$key", $value, in_array($key, ['canais_vagos', 'numeros_cameras_ip']) ? PDO::PARAM_STR : PDO::PARAM_STR);
            }
            $stmt->execute();
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'DVR cadastrado com sucesso', 'id' => $id_dvr]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Erro ao salvar DVR: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar DVR: ' . $e->getMessage()]);
        }
        break;

    case 'atualizar':
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID não fornecido']);
            exit;
        }

        $edificioParams = array_intersect_key($_POST, array_flip(['nome_edificio', 'faixa_ip_edificio', 'faixa_ip_dvr']));
        $dvrParams = array_intersect_key($_POST, array_flip(['modelo', 'marca', 'versao_firmware_atual', 'ultima_versao_firmware', 'status_atualizacao', 'link_dvr', 'capacidade_hd', 'dias_gravados', 'tipo_gravacao']));
        $cameraParams = array_intersect_key($_POST, array_flip(['tem_problema', 'numero_cameras_problema', 'detalhes_problema', 'ocorrencia_aberta', 'link_ocorrencia', 'tem_cameras_ip', 'numeros_cameras_ip', 'tem_canais_vagos', 'canais_vagos', 'foto_problema']));

        if (isset($_FILES['foto_problema']) && $_FILES['foto_problema']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto_problema']['tmp_name'];
            $fileName = $_FILES['foto_problema']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $cameraParams['foto_problema'] = $destPath;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a foto']);
                exit;
            }
        }

        if ($cameraParams['tem_problema'] !== 'Sim') {
            $cameraParams['numero_cameras_problema'] = null;
            $cameraParams['detalhes_problema'] = null;
        }
        if ($cameraParams['tem_cameras_ip'] !== 'Sim') {
            $cameraParams['numeros_cameras_ip'] = null;
        }
        if ($cameraParams['tem_canais_vagos'] !== 'Sim') {
            $cameraParams['canais_vagos'] = null;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT id_edificio FROM DVRs WHERE id = ?");
            $stmt->execute([$id]);
            $id_edificio = $stmt->fetchColumn();

            if (!empty($edificioParams)) {
                $setPairs = [];
                $params = [];
                foreach ($edificioParams as $key => $value) {
                    $setPairs[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $setClause = implode(', ', $setPairs);
                $stmt = $pdo->prepare("UPDATE Edificios SET $setClause WHERE id = :id_edificio");
                $params[':id_edificio'] = $id_edificio;
                $stmt->execute($params);
            }

            if (!empty($dvrParams)) {
                $setPairs = [];
                $params = [];
                foreach ($dvrParams as $key => $value) {
                    $setPairs[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $setClause = implode(', ', $setPairs);
                $stmt = $pdo->prepare("UPDATE DVRs SET $setClause WHERE id = :id");
                $params[':id'] = $id;
                $stmt->execute($params);
            }

            if (!empty($cameraParams)) {
                $stmt = $pdo->prepare("SELECT foto_problema FROM Cameras WHERE id_dvr = ?");
                $stmt->execute([$id]);
                $oldFoto = $stmt->fetchColumn();
                if ($oldFoto && isset($cameraParams['foto_problema']) && $cameraParams['foto_problema'] !== $oldFoto && file_exists($oldFoto)) {
                    unlink($oldFoto);
                }

                $setPairs = [];
                foreach ($cameraParams as $key => $value) {
                    $setPairs[] = "$key = :$key";
                }
                $setClause = implode(', ', $setPairs);
                $stmt = $pdo->prepare("UPDATE Cameras SET $setClause WHERE id_dvr = :id");
                foreach ($cameraParams as $key => $value) {
                    $stmt->bindValue(":$key", $value, in_array($key, ['canais_vagos', 'numeros_cameras_ip']) ? PDO::PARAM_STR : PDO::PARAM_STR);
                }
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                error_log('Parâmetros enviados para UPDATE Cameras: ' . print_r($cameraParams, true));
                $stmt->execute();
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'DVR atualizado com sucesso']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Erro ao atualizar DVR: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar DVR: ' . $e->getMessage()]);
        }
        break;

    case 'ler':
        try {
            $stmt = $pdo->query("SELECT e.*, d.*, c.* FROM DVRs d JOIN Edificios e ON d.id_edificio = e.id JOIN Cameras c ON d.id = c.id_dvr");
            $dvrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('Dados retornados do banco: ' . print_r($dvrs, true));
            echo json_encode(['status' => 'success', 'data' => $dvrs]);
        } catch (PDOException $e) {
            error_log('Erro ao ler DVRs: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erro ao ler DVRs: ' . $e->getMessage()]);
        }
        break;

    case 'excluir':
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID não fornecido']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT foto_problema FROM Cameras WHERE id_dvr = ?");
            $stmt->execute([$id]);
            $foto = $stmt->fetchColumn();
            if ($foto && file_exists($foto)) {
                unlink($foto);
            }

            $stmt = $pdo->prepare("DELETE c, d, e FROM DVRs d JOIN Edificios e ON d.id_edificio = e.id JOIN Cameras c ON d.id = c.id_dvr WHERE d.id = ?");
            $stmt->execute([$id]);
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'DVR excluído com sucesso']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Erro ao excluir DVR: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir DVR: ' . $e->getMessage()]);
        }
        break;

    case 'buscarParaEditar':
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID não fornecido']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT e.*, d.*, c.* FROM DVRs d JOIN Edificios e ON d.id_edificio = e.id JOIN Cameras c ON d.id = c.id_dvr WHERE d.id = ?");
            $stmt->execute([$id]);
            $dvr = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dvr) {
                echo json_encode(['status' => 'success', 'data' => $dvr]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DVR não encontrado']);
            }
        } catch (PDOException $e) {
            error_log('Erro ao buscar DVR: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar DVR: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
        break;
}
?>