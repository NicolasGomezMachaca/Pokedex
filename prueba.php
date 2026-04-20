<?php

$nombre_pokemon = "????";      
$descripcion_pokemon = "Ingresa un número para buscar";
$numero_pokemon = "";
$imagen_pokemon = "default.png"; 

if (isset($_POST['numero'])) {
    require_once 'conexion.php';
    
    $num = intval($_POST['numero']);
    
    $sql = "SELECT * FROM bases_pokemon WHERE Num_Pokemon = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $num);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $numero_pokemon = "#" . $row['Num_Pokemon'];
        $nombre_pokemon = $row['Nom_Pokemon'];
        $descripcion_pokemon = $row['Descripcion'];
        
        
        $ruta_imagen = "imagenes/" . $row['Num_Pokemon'] . ".png";
        
       
        if (file_exists($ruta_imagen)) {
            $imagen_pokemon = $ruta_imagen;
        } else {
            $imagen_pokemon = "imagenes/default.png"; 
        }
        
    } else {
        $nombre_pokemon = "No encontrado";
        $descripcion_pokemon = "El Pokémon #" . $num . " no existe en la base de datos.";
        $imagen_pokemon = "imagenes/default.png";
    }
    
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&display=swap" rel="stylesheet">
    <title>Pokedex</title>
</head>
<body>

<div class="Pokedex">
    <div class="izquierda">
        <!-- Luces de la pokedex -->
        <div class="Luces">
            <div class="luz-grande"></div>
            <div class="luz roja"></div>
            <div class="luz amarilla"></div>
            <div class="luz verde"></div>
        </div>
        <!-- Linea negra -->
        <div class="Linea"></div>
        
        <!-- MARCOS -->
        <div class="Contenedor-izq">
            <div class="marco-blanco">
                <div class="pantalla-celeste">
                    <!-- Imagen del Pokémon -->
                    <img src="<?php echo $imagen_pokemon; ?>" alt="Pokémon">
                </div>
            </div>
            <div class="etiqueta">
                <h2><?php echo $numero_pokemon . " - " . $nombre_pokemon; ?></h2>
            </div>
            <div class="pantalla-inf">
                <p><?php echo $descripcion_pokemon; ?></p>
            </div>
        </div>
    </div>
    
    <div class="derecha">
        <div class="pantalla">
            <form method="post">
                <input type="number" name="numero" placeholder="Número Pokémon" required>
                <button type="submit">Buscar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>