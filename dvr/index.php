<?php
session_start();
$isEditing = isset($_GET['id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tattica - Cadastro de DVR</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .btn-primary {
            background: linear-gradient(to right, #003087, #0052cc);
            transition: background 0.3s;
            font-size: 1.125rem;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #002060, #003087);
        }
        .btn-secondary {
            background: linear-gradient(to right, #6b7280, #9ca3af);
            transition: background 0.3s;
            font-size: 1.125rem;
        }
        .btn-secondary:hover {
            background: linear-gradient(to right, #4b5563, #6b7280);
        }
        .card {
            border: 2px solid #e5e7eb;
            transition: box-shadow 0.2s;
        }
        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        label {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
        }
        input, select, textarea {
            font-size: 1.125rem;
            border: 2px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            width: 100%;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #003087;
            outline: none;
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .conditional-field {
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <header class="fixed top-0 w-full bg-gradient-to-r from-blue-900 to-blue-600 text-white shadow-lg z-10">
        <div class="container mx-auto flex justify-between items-center py-4">
            <img src="https://grupotattica.com.br/wp-content/uploads/2023/05/logo-tattica-1.png" alt="Tattica Logo" class="h-12">
        </div>
    </header>
    <main class="container mx-auto mt-24 p-6 bg-white rounded-lg shadow-xl">
        <h1 class="text-3xl font-bold text-center text-blue-900 mb-8"><?php echo $isEditing ? 'Editar DVR' : 'Cadastrar DVR'; ?></h1>
        <div id="message" class="hidden text-center p-4 mb-4 rounded-lg text-lg"></div>

        <div class="card p-6">
            <form id="dvrForm" onsubmit="event.preventDefault(); salvarDVR();" enctype="multipart/form-data">
                <div class="form-section">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Informações do Condomínio</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="nome_edificio">Condomínio *</label>
                            <input type="text" id="nome_edificio" name="nome_edificio" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="faixa_ip_edificio">IP Condomínio</label>
                                <input type="text" id="faixa_ip_edificio" name="faixa_ip_edificio">
                            </div>
                            <div>
                                <label for="faixa_ip_dvr">IP DVR</label>
                                <input type="text" id="faixa_ip_dvr" name="faixa_ip_dvr">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Detalhes do DVR</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="marca">Marca DVR *</label>
                                <select id="marca" name="marca" required>
                                    <option value="Intelbras">Intelbras</option>
                                    <option value="Hikvision">Hikvision</option>
                                    <option value="Motorola">Motorola</option>
                                    <option value="JFL">JFL</option>
                                    <option value="Tecvoz">Tecvoz</option>
                                </select>
                            </div>
                            <div>
                                <label for="modelo">Modelo *</label>
                                <input type="text" id="modelo" name="modelo" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="versao_firmware_atual">Versão Atual</label>
                                <input type="text" id="versao_firmware_atual" name="versao_firmware_atual">
                            </div>
                            <div>
                                <label for="ultima_versao_firmware">Última Versão</label>
                                <input type="text" id="ultima_versao_firmware" name="ultima_versao_firmware">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status_atualizacao">Status</label>
                                <select id="status_atualizacao" name="status_atualizacao">
                                    <option value="atualizado">Atualizado</option>
                                    <option value="desatualizado">Desatualizado</option>
                                </select>
                            </div>
                            <div>
                                <label for="link_dvr">Link DVR</label>
                                <input type="url" id="link_dvr" name="link_dvr">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Problemas, Câmeras e Canais</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="tem_problema">Problemas Câmeras</label>
                            <select id="tem_problema" name="tem_problema" onchange="toggleProblemaFields()">
                                <option value="Não">Não</option>
                                <option value="Sim">Sim</option>
                            </select>
                            <div id="problemaFields" class="conditional-field hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="numero_cameras_problema">Nº Câmeras com Problema</label>
                                        <input type="number" id="numero_cameras_problema" name="numero_cameras_problema">
                                    </div>
                                    <div>
                                        <label for="detalhes_problema">Detalhes Problema</label>
                                        <input type="text" id="detalhes_problema" name="detalhes_problema">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="foto_problema">Foto da Câmera</label>
                                    <input type="file" id="foto_problema" name="foto_problema" accept="image/*">
                                    <p id="fotoAtual" class="mt-2 text-sm text-gray-600 hidden"></p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ocorrencia_aberta">Ocorrência Aberta</label>
                                <select id="ocorrencia_aberta" name="ocorrencia_aberta">
                                    <option value="Não">Não</option>
                                    <option value="Sim">Sim</option>
                                </select>
                            </div>
                            <div>
                                <label for="link_ocorrencia">Link Ocorrência</label>
                                <input type="url" id="link_ocorrencia" name="link_ocorrencia">
                            </div>
                        </div>
                        <div>
                            <label for="tem_cameras_ip">Câmeras IP</label>
                            <select id="tem_cameras_ip" name="tem_cameras_ip" onchange="toggleCamerasIPFields()">
                                <option value="Não">Não</option>
                                <option value="Sim">Sim</option>
                            </select>
                            <div id="camerasIPFields" class="conditional-field hidden">
                                <label for="numeros_cameras_ip">Números das Câmeras IP</label>
                                <input type="text" id="numeros_cameras_ip" name="numeros_cameras_ip" placeholder="Ex: 1, 2, 3">
                            </div>
                        </div>
                        <div>
                            <label for="tem_canais_vagos">Canais Vagos</label>
                            <select id="tem_canais_vagos" name="tem_canais_vagos" onchange="toggleCanaisVagosFields()">
                                <option value="Não">Não</option>
                                <option value="Sim">Sim</option>
                            </select>
                            <div id="canaisVagosFields" class="conditional-field hidden">
                                <label for="canais_vagos">Nº Canais Vagos</label>
                                <input type="text" id="canais_vagos" name="canais_vagos" placeholder="Ex: 13, 14, 15, 16">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Gravação</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="capacidade_hd">Capacidade HD</label>
                            <select id="capacidade_hd" name="capacidade_hd">
                                <option value="">Selecione</option>
                                <option value="1TB">1TB</option>
                                <option value="2TB">2TB</option>
                                <option value="4TB">4TB</option>
                                <option value="8TB">8TB</option>
                            </select>
                        </div>
                        <div>
                            <label for="dias_gravados">Dias Gravados</label>
                            <input type="number" id="dias_gravados" name="dias_gravados">
                        </div>
                        <div>
                            <label for="tipo_gravacao">Tipo Gravação</label>
                            <select id="tipo_gravacao" name="tipo_gravacao">
                                <option value="">Selecione</option>
                                <option value="continua">Contínua</option>
                                <option value="movimento">Movimento</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Anotações</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="anotacoes">Anotações</label>
                            <textarea id="anotacoes" name="anotacoes" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button type="button" onclick="window.location.href='dvrs.php'" class="btn-secondary text-white px-6 py-2 rounded-lg shadow-lg">Voltar</button>
                    <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg shadow-lg"><?php echo $isEditing ? 'Salvar Alterações' : 'Cadastrar'; ?></button>
                </div>
                <?php if ($isEditing) { ?>
                    <input type="hidden" id="dvrId" name="id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                <?php } ?>
            </form>
        </div>
    </main>
    <script>
        const isEditing = <?php echo json_encode($isEditing); ?>;
        const dvrId = <?php echo json_encode($_GET['id'] ?? null); ?>;

        async function carregarDadosEdicao() {
            if (!isEditing || !dvrId) return;

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'buscarParaEditar', id: dvrId })
                });
                const result = await response.json();
                if (result.status !== 'success') {
                    throw new Error(result.message || 'Erro ao carregar dados para edição');
                }

                const dvr = result.data;
                document.getElementById('nome_edificio').value = dvr.nome_edificio || '';
                document.getElementById('faixa_ip_edificio').value = dvr.faixa_ip_edificio || '';
                document.getElementById('faixa_ip_dvr').value = dvr.faixa_ip_dvr || '';
                document.getElementById('marca').value = dvr.marca || '';
                document.getElementById('modelo').value = dvr.modelo || '';
                document.getElementById('versao_firmware_atual').value = dvr.versao_firmware_atual || '';
                document.getElementById('ultima_versao_firmware').value = dvr.ultima_versao_firmware || '';
                document.getElementById('status_atualizacao').value = dvr.status_atualizacao || '';
                document.getElementById('link_dvr').value = dvr.link_dvr || '';
                document.getElementById('tem_problema').value = dvr.tem_problema || 'Não';
                document.getElementById('numero_cameras_problema').value = dvr.numero_cameras_problema || '';
                document.getElementById('detalhes_problema').value = dvr.detalhes_problema || '';
                if (dvr.foto_problema) {
                    const fotoAtual = document.getElementById('fotoAtual');
                    fotoAtual.innerHTML = `Foto atual: <a href="${dvr.foto_problema}" target="_blank" class="text-blue-600 hover:underline">Ver foto</a>`;
                    fotoAtual.classList.remove('hidden');
                }
                document.getElementById('ocorrencia_aberta').value = dvr.ocorrencia_aberta || 'Não';
                document.getElementById('link_ocorrencia').value = dvr.link_ocorrencia || '';
                document.getElementById('tem_cameras_ip').value = dvr.tem_cameras_ip || 'Não';
                document.getElementById('numeros_cameras_ip').value = dvr.numeros_cameras_ip || '';
                document.getElementById('tem_canais_vagos').value = dvr.tem_canais_vagos || 'Não';
                document.getElementById('canais_vagos').value = dvr.canais_vagos || '';
                document.getElementById('capacidade_hd').value = dvr.capacidade_hd || '';
                document.getElementById('dias_gravados').value = dvr.dias_gravados || '';
                document.getElementById('tipo_gravacao').value = dvr.tipo_gravacao || '';
                document.getElementById('anotacoes').value = dvr.anotacoes || '';

                toggleProblemaFields();
                toggleCamerasIPFields();
                toggleCanaisVagosFields();
            } catch (error) {
                showMessage('Erro ao carregar dados: ' + error.message, 'error');
            }
        }

        async function salvarDVR() {
            const form = document.getElementById('dvrForm');
            const formData = new FormData(form);
            // Depuração: Log dos dados enviados
            for (let [key, value] of formData.entries()) {
                console.log(`Enviando ${key}: ${value}`);
            }
            formData.append('action', isEditing ? 'atualizar' : 'cadastrar');
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showMessage(isEditing ? 'DVR atualizado com sucesso!' : 'DVR cadastrado com sucesso!', 'success');
                    setTimeout(() => window.location.href = 'dvrs.php', 2000);
                } else {
                    showMessage('Erro ao salvar DVR: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Erro ao salvar DVR: ' + error.message, 'error');
            }
        }

        function toggleProblemaFields() {
            const temProblema = document.getElementById('tem_problema').value;
            const problemaFields = document.getElementById('problemaFields');
            if (temProblema === 'Sim') {
                problemaFields.classList.remove('hidden');
            } else {
                problemaFields.classList.add('hidden');
                document.getElementById('numero_cameras_problema').value = '';
                document.getElementById('detalhes_problema').value = '';
                document.getElementById('foto_problema').value = '';
            }
        }

        function toggleCamerasIPFields() {
            const temCamerasIP = document.getElementById('tem_cameras_ip').value;
            const camerasIPFields = document.getElementById('camerasIPFields');
            if (temCamerasIP === 'Sim') {
                camerasIPFields.classList.remove('hidden');
            } else {
                camerasIPFields.classList.add('hidden');
                document.getElementById('numeros_cameras_ip').value = '';
            }
        }

        function toggleCanaisVagosFields() {
            const temCanaisVagos = document.getElementById('tem_canais_vagos').value;
            const canaisVagosFields = document.getElementById('canaisVagosFields');
            if (temCanaisVagos === 'Sim') {
                canaisVagosFields.classList.remove('hidden');
            } else {
                canaisVagosFields.classList.add('hidden');
                document.getElementById('canais_vagos').value = '';
            }
        }

        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            messageDiv.classList.remove('hidden');
            setTimeout(() => messageDiv.classList.add('hidden'), 5000);
        }

        window.onload = carregarDadosEdicao;
    </script>
</body>
</html>