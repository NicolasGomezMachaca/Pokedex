<!DOCTYPE html>
<html>
<head>
    <title>Test Pokémon</title>
</head>
<body>
    <h1>Test de Búsqueda</h1>
    
    <form method="post">
        <input type="number" name="numero" placeholder="Número Pokémon" required>
        <button type="submit">Buscar</button>
    </form>
    
    <?php
    if (isset($_POST['numero'])) {
        require_once 'conexion.php';
        
        $num = intval($_POST['numero']);
        
        $sql = "SELECT * FROM bases_pokemon WHERE Num_Pokemon = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $num);
        $stmt->execute();
        $result = $stmt->get_result();

      
        
        if ($row = $result->fetch_assoc()) {
            echo "<h2>#{$row['Num_Pokemon']} - {$row['Nom_Pokemon']}</h2>";
            echo "<H1>{$row['Descripción']} - {$row['Descripcion']}</H1>";

        } else {
            echo "<p>No encontrado</p>";
        }
        
        $conexion->close();
    }
    ?>
</body>
</html>