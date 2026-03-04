<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recrear Base de Datos - TiendaPlus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #2c2c2c;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #resultado {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .success {
            color: #10b981;
        }

        .error {
            color: #ef4444;
        }

        .info {
            background: #e0e7ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .links {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .links a {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: #d4a574;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 165, 116, 0.3);
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Recrear Base de Datos TiendaPlus</h1>

        <div class="info">
            <strong>⚠️ Importante:</strong> Este proceso eliminará la base de datos actual y creará una nueva con datos
            de prueba.
        </div>

        <button class="btn" onclick="recrearDB()" id="btnRecrear">
            ▶️ Ejecutar Recreación de Base de Datos
        </button>

        <div id="resultado"></div>
    </div>

    <script>
        async function recrearDB() {
            const btn = document.getElementById('btnRecrear');
            const resultado = document.getElementById('resultado');

            btn.disabled = true;
            btn.textContent = '⏳ Ejecutando...';
            resultado.style.display = 'block';
            resultado.innerHTML = '<p>Procesando...</p>';

            try {
                const response = await fetch('ejecutar_db.php');
                const html = await response.text();

                resultado.innerHTML = html;
                btn.textContent = '✅ Completado';

                // Agregar enlaces después de completar
                resultado.innerHTML += `
                    <div class="links">
                        <a href="web/login.php">🔐 Ir al Login</a>
                        <a href="backend/panel.php">👨‍💼 Panel Admin</a>
                    </div>
                `;
            } catch (error) {
                resultado.innerHTML = `<p class="error">❌ Error: ${error.message}</p>`;
                btn.disabled = false;
                btn.textContent = '🔄 Reintentar';
            }
        }
    </script>
</body>

</html>