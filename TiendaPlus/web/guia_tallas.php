<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Guía de Tallas - Tienda Plus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #fff;
      color: #333;
    }
    .banner {
      background-image: url("img/tallas.jpg"); /* Cambia por tu imagen */
      background-size: cover;
      background-position: center;
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .banner h1 {
      background: rgba(255,255,255,0.7);
      padding: 15px 30px;
      border-radius: 8px;
      font-size: 2.5rem;
      color: #000;
    }
    table {
      text-align: center;
    }
    th {
      background-color: #f8f9fa;
      font-weight: bold;
    }
    .destacada {
      background-color: #ffb6c1 !important;
      font-weight: bold;
    }
    footer {
      background: #ff69aa;
      color: white;
      text-align: center;
      padding: 20px;
      margin-top: 50px;
    }
  </style>
</head>
<body>

<!-- Navbar simple para volver -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">Tienda Plus</a>
  </div>
</nav>

<!-- Banner -->
<div class="banner">
  <h1>Guía de tallas</h1>
</div>

<div class="container my-5">
  <h2 class="text-center mb-4">PRENDAS SUPERIORES</h2>
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>TALLA</th>
          <th>CONTORNO BRAZO</th>
          <th>BUSTO</th>
          <th>CINTURA</th>
        </tr>
      </thead>
      <tbody>
        <tr class="destacada">
          <td>14</td><td>40–42 cm</td><td>112–116 cm</td><td>92–98 cm</td>
        </tr>
        <tr><td>16</td><td>42–44 cm</td><td>116–120 cm</td><td>92–108 cm</td></tr>
        <tr><td>18</td><td>44–46 cm</td><td>120–124 cm</td><td>108–116 cm</td></tr>
        <tr><td>20</td><td>46–48 cm</td><td>124–128 cm</td><td>116–124 cm</td></tr>
        <tr><td>22</td><td>48–50 cm</td><td>128–132 cm</td><td>124–132 cm</td></tr>
        <tr><td>24</td><td>50–52 cm</td><td>132–136 cm</td><td>132–140 cm</td></tr>
      </tbody>
    </table>
  </div>

  <h2 class="text-center mt-5 mb-4">PRENDAS INFERIORES</h2>
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>TALLA</th>
          <th>CINTURA</th>
          <th>CADERA</th>
          <th>CONTORNO PIERNA</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>14</td><td>92–98 cm</td><td>108–118 cm</td><td>76–80 cm</td></tr>
        <tr><td>16</td><td>92–108 cm</td><td>118–128 cm</td><td>80–84 cm</td></tr>
        <tr><td>18</td><td>108–116 cm</td><td>128–138 cm</td><td>84–86 cm</td></tr>
        <tr><td>20</td><td>116–124 cm</td><td>138–148 cm</td><td>86–88 cm</td></tr>
        <tr><td>22</td><td>124–132 cm</td><td>148–158 cm</td><td>88–94 cm</td></tr>
        <tr><td>24</td><td>132–140 cm</td><td>158–168 cm</td><td>94–98 cm</td></tr>
      </tbody>
    </table>
  </div>
</div>

<footer>
  <p>© 2025 Tienda Plus Size - Todos los derechos reservados</p>
</footer>

</body>
</html>
