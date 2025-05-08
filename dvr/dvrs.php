<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tattica - DVRs Cadastrados</title>
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
        .btn-danger {
            background: linear-gradient(to right, #dc2626, #ef4444);
            transition: background 0.3s;
            font-size: 1.125rem;
        }
        .btn-danger:hover {
            background: linear-gradient(to right, #b91c1c, #dc2626);
        }
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid #e5e7eb;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .status-red {
            background-color: #dc2626;
            color: #ffffff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        .status-green {
            background-color: #16a34a;
            color: #ffffff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        .sort-icon {
            display: inline-block;
            margin-left: 4px;
            transition: transform 0.2s;
        }
        .sort-icon.asc {
            transform: rotate(180deg);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 0.5rem;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .camera-photo {
            max-width: 300px;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <header class="fixed top-0 w-full bg-gradient-to-r from-blue-900 to-blue-600 text-white shadow-lg z-10">
        <div class="container mx-auto flex justify-center items-center py-4">
            <img src="https://grupotattica.com.br/wp-content/uploads/2023/05/logo-tattica-1.png" alt="Tattica Logo" class="h-12">
        </div>
    </header>
    <main class="container mx-auto mt-24 p-6 bg-white rounded-lg shadow-xl">
        <h1 class="text-3xl font-bold text-center text-blue-900 mb-8">DVRs Cadastrados</h1>
        <div id="message" class="hidden text-center p-4 mb-4 rounded-lg text-lg"></div>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="relative w-full sm:w-1/3">
                <input type="text" id="searchInput" placeholder="Pesquisar por condomínio, marca ou modelo..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900 text-lg">
                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="flex space-x-4">
                <button onclick="exportToCSV()" class="btn-primary text-white px-6 py-2 rounded-lg shadow-lg">Exportar CSV</button>
                <button onclick="window.location.href='index.php'" class="btn-primary text-white px-6 py-2 rounded-lg shadow-lg">Voltar ao Cadastro</button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="dvrsContainer">
        </div>
    </main>

    <!-- Modal para Detalhes -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">×</span>
            <h2 class="text-2xl font-bold text-blue-900 mb-4">Detalhes do DVR</h2>
            <div id="modalContent" class="text-lg"></div>
        </div>
    </div>

    <script>
        let allDVRs = [];
        let sortDirection = {};
        let currentSortColumn = null;

        async function carregarDVRs() {
            try {
                const response = await fetch('api.php?action=ler');
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                const result = await response.json();
                if (result.status !== 'success') {
                    throw new Error(result.message || 'Erro ao carregar dados');
                }
                allDVRs = result.data;
                renderDVRs(allDVRs);
            } catch (error) {
                showMessage('Erro ao carregar DVRs: ' + error.message, 'error');
            }
        }

        function renderDVRs(dvrs) {
            const container = document.getElementById('dvrsContainer');
            container.innerHTML = '';
            if (dvrs.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-700 text-lg">Nenhum DVR cadastrado ou encontrado.</p>';
                return;
            }
            dvrs.forEach((dvr, index) => {
                const statusColor = getStatusColor(dvr.capacidade_hd, dvr.dias_gravados);
                const card = document.createElement('div');
                card.className = `card p-4 rounded-lg`;
                card.innerHTML = `
                    <h3 class="text-xl font-semibold text-blue-900">${dvr.nome_edificio || 'Condomínio não informado'}</h3>
                    <div class="grid grid-cols-1 gap-2 mt-2">
                        <div><strong>Marca DVR:</strong> ${dvr.marca || '-'}</div>
                        <div><strong>Modelo:</strong> ${dvr.modelo || '-'}</div>
                        <div><strong>Status:</strong> ${dvr.status_atualizacao || '-'}</div>
                        <div><strong>Link DVR:</strong> ${dvr.link_dvr ? `<a class="text-blue-600 hover:underline" href="${dvr.link_dvr}" target="_blank">${dvr.link_dvr}</a>` : '-'}</div>
                    </div>
                    <div class="flex space-x-2 mt-4">
                        <button onclick="showDetails(${index})" class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-600">Detalhes</button>
                        <button onclick="editarDVR(${dvr.id})" class="btn-primary text-white px-4 py-2 rounded-lg shadow">Editar</button>
                        <button onclick="excluirDVR(${dvr.id})" class="btn-danger text-white px-4 py-2 rounded-lg shadow">Excluir</button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function getStatusColor(capacity, days) {
            if (!capacity || !days) return '';
            days = parseInt(days);
            switch (capacity) {
                case '1TB':
                case '2TB':
                case '4TB':
                    return days < 16 ? 'status-red' : 'status-green';
                default:
                    return '';
            }
        }

        function showDetails(index) {
            const dvr = allDVRs[index];
            console.log('Dados do DVR no showDetails:', dvr);
            const statusColor = getStatusColor(dvr.capacidade_hd, dvr.dias_gravados);
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <div class="mb-4"><strong class="text-xl text-blue-900">Condomínio:</strong> ${dvr.nome_edificio || 'Condomínio não informado'}</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><strong>IP Condomínio:</strong> ${dvr.faixa_ip_edificio || '-'}</div>
                    <div><strong>IP DVR:</strong> ${dvr.faixa_ip_dvr || '-'}</div>
                    <div><strong>Marca DVR:</strong> ${dvr.marca || '-'}</div>
                    <div><strong>Modelo:</strong> ${dvr.modelo || '-'}</div>
                    <div><strong>Versão Atual:</strong> ${dvr.versao_firmware_atual || '-'}</div>
                    <div><strong>Última Versão:</strong> ${dvr.ultima_versao_firmware || '-'}</div>
                    <div><strong>Status:</strong> ${dvr.status_atualizacao || '-'}</div>
                    <div><strong>Link DVR:</strong> ${dvr.link_dvr ? `<a class="text-blue-600 hover:underline" href="${dvr.link_dvr}" target="_blank">${dvr.link_dvr}</a>` : '-'}</div>
                    <div><strong>Problemas Câmeras:</strong> ${dvr.tem_problema === 'Sim' ? 'Sim (' + (dvr.numero_cameras_problema || '-') + ')' : 'Não'}</div>
                    <div><strong>Detalhes Problema:</strong> ${dvr.detalhes_problema || '-'}</div>
                    ${dvr.foto_problema ? `<div class="col-span-2"><strong>Foto da Câmera:</strong><br><img src="${dvr.foto_problema}" alt="Foto da Câmera" class="camera-photo"></div>` : ''}
                    <div><strong>Canais Vagos:</strong> ${dvr.tem_canais_vagos === 'Sim' ? 'Sim (' + (dvr.canais_vagos || '-') + ')' : 'Não'}</div>
                    <div><strong>Ocorrência Aberta:</strong> ${dvr.ocorrencia_aberta || '-'}</div>
                    <div><strong>Link Ocorrência:</strong> ${dvr.link_ocorrencia ? `<a class="text-blue-600 hover:underline" href="${dvr.link_ocorrencia}" target="_blank">${dvr.link_ocorrencia}</a>` : '-'}</div>
                    <div><strong>Câmeras IP:</strong> ${dvr.tem_cameras_ip === 'Sim' ? 'Sim (' + (dvr.numeros_cameras_ip || '-') + ')' : 'Não'}</div>
                    <div><strong>Capacidade HD:</strong> ${dvr.capacidade_hd || '-'}</div>
                    <div><strong>Dias Gravados:</strong> <span class="${statusColor}">${dvr.dias_gravados || '-'}</span></div>
                    <div><strong>Tipo Gravação:</strong> ${dvr.tipo_gravacao ? dvr.tipo_gravacao.charAt(0).toUpperCase() + dvr.tipo_gravacao.slice(1) : '-'}</div>
                </div>
            `;
            document.getElementById('detailsModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function sortTable(column) {
            if (currentSortColumn === column) {
                sortDirection[column] = !sortDirection[column];
            } else {
                sortDirection = {};
                sortDirection[column] = true;
            }
            currentSortColumn = column;

            const sortedDVRs = [...allDVRs].sort((a, b) => {
                const valueA = (a[column] || '').toString().toLowerCase();
                const valueB = (b[column] || '').toString().toLowerCase();
                if (sortDirection[column]) {
                    return valueA > valueB ? 1 : -1;
                } else {
                    return valueA < valueB ? 1 : -1;
                }
            });

            renderDVRs(sortedDVRs);
        }

        function searchDVRs() {
            const searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
            if (searchTerm.length < 2 && searchTerm.length !== 0) {
                showMessage('Digite pelo menos 2 caracteres para pesquisar.', 'error');
                return;
            }
            const filteredDVRs = allDVRs.filter(dvr => 
                (dvr.nome_edificio || '').toLowerCase().includes(searchTerm) ||
                (dvr.marca || '').toLowerCase().includes(searchTerm) ||
                (dvr.modelo || '').toLowerCase().includes(searchTerm)
            );
            renderDVRs(filteredDVRs);
        }

        function exportToCSV() {
            const headers = [
                'Condomínio', 'IP Condomínio', 'IP DVR', 'Marca DVR', 'Modelo', 'Versão Atual', 'Última Versão', 'Status',
                'Link DVR', 'Problemas Câmeras', 'Nº Câmeras com Problema', 'Detalhes Problema', 'Foto da Câmera', 'Canais Vagos',
                'Ocorrência Aberta', 'Link Ocorrência', 'Câmeras IP', 'Números das Câmeras IP', 'Capacidade HD',
                'Dias Gravados', 'Tipo Gravação'
            ];
            const rows = allDVRs.map(dvr => [
                dvr.nome_edificio || '-',
                dvr.faixa_ip_edificio || '-',
                dvr.faixa_ip_dvr || '-',
                dvr.marca || '-',
                dvr.modelo || '-',
                dvr.versao_firmware_atual || '-',
                dvr.ultima_versao_firmware || '-',
                dvr.status_atualizacao || '-',
                dvr.link_dvr || '-',
                dvr.tem_problema || '-',
                dvr.numero_cameras_problema || '-',
                dvr.detalhes_problema || '-',
                dvr.foto_problema || '-',
                dvr.canais_vagos || '-',
                dvr.ocorrencia_aberta || '-',
                dvr.link_ocorrencia || '-',
                dvr.tem_cameras_ip || '-',
                dvr.numeros_cameras_ip || '-',
                dvr.capacidade_hd || '-',
                dvr.dias_gravados || '-',
                dvr.tipo_gravacao || '-'
            ]);

            const csvContent = [
                headers.join(','),
                ...rows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(','))
            ].join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'dvrs_cadastrados.csv';
            link.click();
        }

        async function excluirDVR(id) {
            if (!confirm('Tem certeza que deseja excluir este DVR?')) return;
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'excluir', id })
                });
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }
                const data = await response.json();
                if (data.status === 'success') {
                    showMessage('DVR excluído com sucesso!', 'success');
                    carregarDVRs();
                } else {
                    throw new Error(data.message || 'Erro ao excluir DVR');
                }
            } catch (error) {
                showMessage('Erro ao excluir DVR: ' + error.message, 'error');
            }
        }

        function editarDVR(id) {
            window.location.href = `index.php?id=${id}`;
        }

        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            messageDiv.classList.remove('hidden');
            setTimeout(() => messageDiv.classList.add('hidden'), 5000);
        }

        document.getElementById('searchInput').addEventListener('input', searchDVRs);
        window.onload = carregarDVRs;
    </script>
</body>
</html>