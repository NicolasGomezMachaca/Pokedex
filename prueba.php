<?php
//-------------------------- empieza el codigo PHP --------------------------------------------- -->


#Variables que se usan para encontrar la informacion del pokemon, que esta en la Base de Datos, y se muesta en la pagina
$nombre_pokemon = "????";      
$descripcion_pokemon = "Ingresa un número para buscar";
$numero_pokemon = "";
$imagen_pokemon = "default.png"; 
$tipos_pokemon = "";
$habilidades_pokemon = "";
$altura_pokemon = "";
$peso_pokemon = "";
$genero_pokemon = "";
$debilidades_array = [];
$resistencias_array = [];
$inmunidades_array = [];


// conexion a la base de datos
require_once 'conexion.php';


// si no se ha enviado un número, se muestra el pokemon #1(bulbasaur) por defecto
if (!isset($_POST['numero'])) {
    $num = 1;
} else {
    $num = intval($_POST['numero']);
}


//peticion a la Base de datos para obtener el total de pokemones, para poder hacer el ciclo de los botones de siguiente y anterior
$sql_total = "SELECT COUNT(*) as total FROM bases_pokemon";
$result_total = $conexion->query($sql_total); // Ejecutar la consulta para obtener el total de pokemones
$row_total = $result_total->fetch_assoc(); // Obtener el resultado como un array asociativo
$total_pokemon = $row_total['total']; // Almacenar el total de pokemones en una variable


$anterior = $num - 1; 
if ($anterior < 1) { 
    $anterior = $total_pokemon; 
}

$siguiente = $num + 1;
if ($siguiente > $total_pokemon) { 
    $siguiente = 1; 
}


// Consulta principal para obtener la información del Pokémon , numero y descripción
$sql = "SELECT * FROM bases_pokemon WHERE Num_Pokemon = ?";
$stmt = $conexion->prepare($sql); // Prepara la consulta para evitar inyecciones SQL
$stmt->bind_param("i", $num); // Vincula el número del Pokémon como un parámetro entero
$stmt->execute(); // Ejecuta la consulta
$result = $stmt->get_result(); // Obtieniene el resultado de la consulta


if ($row = $result->fetch_assoc()) { // Si se encuentra el Pokémon, se extraen sus datos
    $numero_pokemon = "N°" . $row['Num_Pokemon'];  
    $nombre_pokemon = $row['Nom_Pokemon']; // Nombre del Pokémon
    $descripcion_pokemon = $row['Descripcion']; // Descripción del Pokémon

    // Tipos
    $sql_tipos = "SELECT * FROM tipos WHERE Id_tipos IN (SELECT Id_tipos FROM pokemon_tipos WHERE Num_Pokemon = ?)"; // Consulta para obtener los tipos del Pokémon
    $stmt_tipos = $conexion->prepare($sql_tipos); 
    $stmt_tipos->bind_param("i", $num); 
    $stmt_tipos->execute();
    $result_tipos = $stmt_tipos->get_result(); // Obtiene el resultado de la consulta
    
    $tipos_array = []; // Array para almacenar los tipos del Pokémon
    while ($tipo = $result_tipos->fetch_assoc()) { // Recorre los tipos obtenidos y los almacena en el array
        $tipos_array[] = $tipo['Nom_tipo'];
    }
    $tipos_pokemon = !empty($tipos_array) ? implode(" / ", $tipos_array) : "Desconocido"; //Cuando no hay tipos, se muestra "Desconocido"
    
    // Habilidades
    $sql_habilidades = "SELECT * FROM habilidades WHERE Id_habilidad IN (SELECT Id_habilidad FROM poke_habilidades WHERE Num_Pokemon = ?)"; // Consulta para obtener las habilidades del Pokémon
    $stmt_habilidades = $conexion->prepare($sql_habilidades); // Prepara la consulta para evitar inyecciones SQL
    $stmt_habilidades->bind_param("i", $num); 
    $stmt_habilidades->execute();
    $result_habilidades = $stmt_habilidades->get_result();
    

    
    $habilidades_array = []; // Array para almacenar las habilidades del Pokémon
    while ($habilidad = $result_habilidades->fetch_assoc()) { 
        $habilidades_array[] = [
            'nombre' => $habilidad['Nom_Habilidad'],//guarda el nombre de la habilidad en la variable nom_habilidad
            'descripcion' => $habilidad['Habilidad_descrip']
        ];
    }
    
    // Datos
    $sql_datos = "SELECT * FROM Datos WHERE id_Dato IN (SELECT id_Datos FROM datos_pokemones WHERE id_pokemon = ?)"; // Consulta para obtener los datos del Pokémon
    $stmt_datos = $conexion->prepare($sql_datos);
    $stmt_datos->bind_param("i", $num);
    $stmt_datos->execute();
    $result_datos = $stmt_datos->get_result();

    if ($row_datos = $result_datos->fetch_assoc()) { //
        $altura_pokemon = $row_datos['altura'] . " m";//guarda la altura del pokemon y le agrega "m" para indicar metros
        $peso_pokemon = $row_datos['peso'] . " kg";//guarda el peso del pokemon y le agrega "kg" para indicar kilogramos
        $genero_pokemon = $row_datos['genero'];//guarda el genero del pokemon (si solamente tiene un genero, o ambos , tambien puede ser desconocido)
    } else {// si no encuentra datos, se muestra desconocido
        $altura_pokemon = "Desconocida";
        $peso_pokemon = "Desconocido";
        $genero_pokemon = "Desconocido";
    }

    // Debilidades, Resistencias e Inmunidades 
    $sql_todos_tipos = "SELECT * FROM tipos WHERE Id_tipos IN (SELECT atacante FROM debilidades WHERE defensor IN (SELECT Id_tipos FROM pokemon_tipos WHERE Num_Pokemon = ?))";
    $stmt_todos = $conexion->prepare($sql_todos_tipos);
    $stmt_todos->bind_param("i", $num);
    $stmt_todos->execute();
    $result_todos = $stmt_todos->get_result();
    
    while ($tipo_atacante = $result_todos->fetch_assoc()) { 
        $multiplicador_total = 1.0;
        
        $sql_mult = "SELECT multiplicador FROM debilidades  WHERE atacante = ? AND defensor IN (SELECT Id_tipos FROM pokemon_tipos WHERE Num_Pokemon = ?)"; 
        //consulta para obtener el multiplicador de cada tipo de atacante contra los tipos del pokemon
        $stmt_mult = $conexion->prepare($sql_mult);
        $stmt_mult->bind_param("ii", $tipo_atacante['Id_tipos'], $num);
        $stmt_mult->execute();
        $result_mult = $stmt_mult->get_result();
        
        while ($mult = $result_mult->fetch_assoc()) {
            // multiplica los multiplicadores de cada tipo atacante contra los tipos del pokemon para obtener el multiplicador total
            $multiplicador_total *= $mult['multiplicador'];
        }
        
        if ($multiplicador_total > 1) { 
            //si el multiplicador total es mayor a 1 , guarda el tipo de atacante y el multiplicador en debilidades
            $debilidades_array[] = [
                'tipo' => $tipo_atacante['Nom_tipo'], 
                'multiplicador' => number_format($multiplicador_total, 1) . 'x'
            ];
        } elseif ($multiplicador_total < 1 && $multiplicador_total > 0) {
            //si el multiplicador total es menor a 1, pero mayor a cero ,guarda el tipo del atacante y su multiplicador en resistencias
            $resistencias_array[] = [
                'tipo' => $tipo_atacante['Nom_tipo'], 
                'multiplicador' => number_format($multiplicador_total, 1) . 'x'
            ];
        } elseif ($multiplicador_total == 0) { 
            //si el multiplicador total es igual a cero, guarda el tipo del atacante en inmunidades
            $inmunidades_array[] = $tipo_atacante['Nom_tipo'];
        }
    }

    // Imagen
    $ruta_imagen = "imagenes/" . $row['Num_Pokemon'] . ".png"; // Construye la ruta de la imagen basada en el número del Pokémon
    if (file_exists($ruta_imagen)) { // Verifica si la imagen existe 
        $imagen_pokemon = $ruta_imagen; 
    } else {
        $imagen_pokemon = "No se encontro la imagen"; 
    }
    
} else { // si no se encuentra el Pokémon, se muestran valores por defecto indicando que no se encontró el Pokémon
    $nombre_pokemon = "No encontrado";
    $descripcion_pokemon = "El Pokémon #" . $num . " no existe en la base de datos.";
    $imagen_pokemon = "imagenes/default.png";
}
// Cierra la conexión a la base de datos
$conexion->close();
?>

<!--- -------------------------- empieza el codigo HTML --------------------------------------------- -->

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

<div class="Pokedex"><!-- contenedor de la pokedex -->
    <div class="izquierda">
        <div class="Luces">
            <div class="luz-grande"></div>
            <div class="luz roja"></div>
            <div class="luz amarilla"></div>
            <div class="luz verde"></div>
        </div>
        <div class="Linea"></div>
        
        <div class="Contenedor-izq">
            <div class="marco-blanco">
                <div class="pantalla-celeste">
                    <img src="<?php echo $imagen_pokemon; ?>" alt="Pokémon"> <!--llama a la variable  $imagen_pokemon -->
                </div>
            </div>
            <div class="etiqueta">
                <h2><?php echo $numero_pokemon . " - " . $nombre_pokemon; ?></h2><!--llama a la variable  $nombre_pokemon -->
            </div>
            <div class="pantalla-inf">
                <p><?php echo $descripcion_pokemon; ?></p><!--llama a la variable  $descripcion_pokemon -->
            </div>
        </div>
    </div>
    
    <div class="derecha">
        <div class="pantalla">
            <!-- Botones de navegación para ir al Pokémon anterior o siguiente, y un formulario para buscar por número -->
            <form method="post" class="form-flecha-izquierda">
                <input type="hidden" name="numero" value="<?php echo $anterior; ?>">
                <button type="submit" class="flecha-esquina">◀</button>
            </form>

            <form method="post" class="form-flecha-derecha">
                <input type="hidden" name="numero" value="<?php echo $siguiente; ?>">
                <button type="submit" class="flecha-esquina">▶</button>
            </form>

            <div class="formulario">
            <form method="post" >
                <input type="number" name="numero" placeholder="Número Pokémon" required>
                <button type="submit">Buscar</button>
            </form>
            </div>
            
            <div class="info">
    <!-- Radio buttons ocultos -->
    <input type="radio" name="tabs" id="tab1" checked>
    <input type="radio" name="tabs" id="tab2">
    
    <!-- Botones de pestañas -->
    <div class="tabs">
        <label for="tab1" class="tab-button">Información</label>
        <label for="tab2" class="tab-button">Tipo</label>
    </div>
    
    <!-- PESTAÑA 1: INFORMACIÓN -->
    <div class="tab-content" id="content-tab1">
        <h2>Habilidad:</h2>
        <!-- Si el array de habilidades no está vacío, se muestran las habilidades del Pokémon, de lo contrario se muestra "Sin habilidades" -->
        <?php if (!empty($habilidades_array)): ?>
            <?php foreach($habilidades_array as $habilidad): ?>
                <p class="habilidad-nombre"><?php echo $habilidad['nombre']; ?></p>
                <p class="habilidad-desc"><?php echo $habilidad['descripcion']; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Sin habilidades</p>
        <?php endif; ?>
        <!-- llama a las variables y las muestra -->
        <h2>Datos:</h2>
        <p>Altura: <?php echo $altura_pokemon; ?></p>
        <p>Peso: <?php echo $peso_pokemon; ?></p>
        <p>Género: <?php echo $genero_pokemon; ?></p>
    </div>
    
    <!-- PESTAÑA 2: TIPO -->
    <div class="tab-content" id="content-tab2">
        <h2>Tipo:</h2>
        <p><?php echo $tipos_pokemon; ?></p>
        
        <h2> Débil contra:</h2>
        <?php if (!empty($debilidades_array)): ?>
            <?php foreach ($debilidades_array as $debilidad): ?>
                <p class="debilidad"><?php echo $debilidad['tipo'] . " (" . $debilidad['multiplicador'] . ")"; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Ninguna</p>
        <?php endif; ?>
        
        <h2> Resiste:</h2>
        <?php if (!empty($resistencias_array)): ?>
            <?php foreach ($resistencias_array as $resistencia): ?>        
                <p class="resistencia"><?php echo $resistencia['tipo'] . " (" . $resistencia['multiplicador'] . ")"; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Ninguna</p>
        <?php endif; ?>
        
        <h2> Inmune:</h2>
        <?php if (!empty($inmunidades_array)): ?>
            <?php foreach ($inmunidades_array as $inmunidad): ?>
                <p class="inmunidad"><?php echo $inmunidad; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Ninguna</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>