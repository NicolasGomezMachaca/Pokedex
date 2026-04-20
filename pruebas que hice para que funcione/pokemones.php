<?php
require_once 'config/Conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_pokemon = isset($_POST['numero']) ? intval($_POST['numero']) : 0;
    
    if ($numero_pokemon > 0) {
        
        // Consulta principal del Pokémon
        $sql_pokemon = "SELECT Num_Pokemon, Nom_Pokemon, Descripción FROM bases_pokemon WHERE Num_Pokemon = ?";
        $stmt = $conexion->prepare($sql_pokemon);
        $stmt->bind_param("i", $numero_pokemon);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $pokemon = $resultado->fetch_assoc();
            
            echo "<h2>#" . $pokemon['Num_Pokemon'] . " - " . htmlspecialchars($pokemon['Nom_Pokemon']) . "</h2>";
            echo "<p><strong>Descripción:</strong> " . htmlspecialchars($pokemon['Descripción']) . "</p>";
            
            // Consultar tipos del Pokémon
            $sql_tipos = "SELECT t.Nombre_tipo 
                          FROM pokemon_tipos pt 
                          JOIN tipos t ON pt.id_tipo = t.id_tipo 
                          WHERE pt.Num_Pokemon = ?";
            $stmt_tipos = $conexion->prepare($sql_tipos);
            $stmt_tipos->bind_param("i", $numero_pokemon);
            $stmt_tipos->execute();
            $resultado_tipos = $stmt_tipos->get_result();
            
            if ($resultado_tipos->num_rows > 0) {
                echo "<p><strong>Tipos:</strong> ";
                $tipos = [];
                while ($tipo = $resultado_tipos->fetch_assoc()) {
                    $tipos[] = htmlspecialchars($tipo['Nombre_tipo']);
                }
                echo implode(" / ", $tipos) . "</p>";
            }
            
            $stmt_tipos->close();
            
        } else {
            echo "<p>Pokémon no encontrado.</p>";
        }
        
        $stmt->close();
        $conexion->close();
    }
}
?>