<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tattica - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .bg-custom {
            background: linear-gradient(to right, #003087, #0052cc);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <main class="container mx-auto mt-24 p-6 max-w-md">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold text-center text-blue-900 mb-6">Login</h1>
            <div id="message" class="hidden text-center p-4 mb-4 rounded-lg text-lg"></div>
            <form id="loginForm" class="space-y-4">
                <div>
                    <label for="nome" class="block text-lg font-medium text-gray-700">Nome</label>
                    <input type="text" id="nome" name="nome" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900" required>
                </div>
                <div>
                    <label for="senha" class="block text-lg font-medium text-gray-700">Senha</label>
                    <input type="password" id="senha" name="senha" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900" required>
                </div>
                <button type="submit" class="bg-custom text-white w-full py-2 rounded-lg hover:bg-blue-700 transition">Entrar</button>
            </form>
        </div>
    </main>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const nome = formData.get('nome').trim();
            const senha = formData.get('senha').trim();
            const data = { action: 'login', nome, senha };
            
            console.log('Dados enviados:', data);

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                console.log('Status da resposta:', response.status);
                const result = await response.json();
                console.log('Resposta do servidor:', result);

                if (result.status === 'success') {
                    console.log('Redirecionando para index.php');
                    window.location.href = 'index.php';
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                showMessage('Erro ao fazer login: ' + error.message, 'error');
            }
        });

        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            messageDiv.classList.remove('hidden');
            setTimeout(() => messageDiv.classList.add('hidden'), 5000);
        }
    </script>
</body>
</html>