<?php
#bases pokemon
$nombre_pokemon = "????";      
$descripcion_pokemon = "Ingresa un número para buscar";
$numero_pokemon = "";
$imagen_pokemon = "default.png"; 
$tipos_pokemon = "";
$habilidades_pokemon = "";

#

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
        
        $sql_tipos = "SELECT * FROM tipos WHERE Id_tipos IN (SELECT Id_tipos FROM pokemon_tipos WHERE Num_Pokemon = ?)";
        
        $stmt_tipos = $conexion->prepare($sql_tipos);
        $stmt_tipos->bind_param("i", $num);
        $stmt_tipos->execute();
        $result_tipos = $stmt_tipos->get_result();
        
        $tipos_array = [];
        while ($tipo = $result_tipos->fetch_assoc()) {
            $tipos_array[] = $tipo['Nom_tipo'];
        }
        $tipos_pokemon = !empty($tipos_array) ? implode(" / ", $tipos_array) : "Desconocido";
        
        // Obtener las habilidades del Pokémon
        $sql_habilidades = "SELECT * FROM habilidades WHERE Id_habilidad IN (SELECT Id_habilidad FROM poke_habilidades WHERE Num_Pokemon = ?)";
        
        $stmt_habilidades = $conexion->prepare($sql_habilidades);
        $stmt_habilidades->bind_param("i", $num);
        $stmt_habilidades->execute();
        $result_habilidades = $stmt_habilidades->get_result();
        
        $habilidades_array = [];
        
        while ($habilidad = $result_habilidades->fetch_assoc()) {
            $habilidades_array[] = [
                'nombre' => $habilidad['Nom_Habilidad'],
                'descripcion' => $habilidad['Habilidad_descrip']
            ];
        }

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
            <div class="formulario">
            <form method="post" >
                <input type="number" name="numero" placeholder="Número Pokémon" required>
                <button type="submit">Buscar</button>
            </form>
            </div>
            <div class="info">
            <h2>Tipos:</h2>
            <p><?php echo $tipos_pokemon; ?></p>
            <h2>Habilidades:</h2>
            <p><?php echo $habilidades_array[0]['nombre']; ?></p>
            <p><?php echo $habilidades_array[0]['descripcion']; ?></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>