<?php
include("include/seguridad.php");
include("conexion.php");

$usuario = $_SESSION['usuario'];

date_default_timezone_set('America/Los_Angeles');

$date = date_create();
$fecha = date_format($date, 'Y-m-d H:i:s');

$documento = $_POST;
$id_documento = $_POST['idDocumento'];
$nombre = $_POST['nombre'];
$nombre_archivo = $_FILES['ruta']['name'];
$id_tipo_archivo = $_POST['idTipoArchivo'];
$id_tipo_documento = $_POST['idTipoDocumento'];
$id_estatus = $_POST['estatus'];
$descripcion = $_POST['descripcion'];
$id_clasificador_documento = 0;
$orden_documento = $_POST['ordenDocumento'];
$id_departamento = $_POST['idDepartamento'];


echo "fecha: " . $fecha . "<br>";

if( ((!isset($documento['idDocumento'])) || (trim($documento['idDocumento'])) != '') && 
((!isset($documento['nombre'])) || (trim($documento['nombre'])) != '') && 
((!isset($documento['id_tipo_archivo'])) || (trim($documento['id_tipo_archivo'])) != '') && 
((!isset($documento['id_tipo_documento'])) || (trim($documento['id_tipo_documento'])) != '') &&  
((!isset($documento['estatus'])) || (trim($documento['estatus'])) != '') && 
((!isset($documento['descripcion'])) || (trim($documento['descripcion'])) != '') && 
((!isset($documento['id_clasificador_documento'])) || (trim($documento['id_clasificador_documento'])) != '') && 
((!isset($documento['ordenDocumento'])) || (trim($documento['ordenDocumento'])) != '') && 
((!isset($documento['idDepartamento'])) || (trim($documento['idDepartamento'])) != '') ) {

    // se cambian los espacios en blanco del archivo a guardar por "-"
    $nombre_archivo = preg_replace('/\s+/', '-', $nombre_archivo);
    echo "nombre archivo: " . $nombre_archivo . "<br>";

    // se verifica el tipo de documento que se va a guardar
    $query_tdoc = mysqli_query($conexion, "select ruta from tipo_documento where id_tipo_documento = $id_tipo_documento");
    $resultado_tdoc = mysqli_fetch_array($query_tdoc);
    echo "<br>" . "ruta tdoc: " . $resultado_tdoc[0] . "<br>";

    // se busca el departamento al que le corresponde el documento a guardar
    $query_dep = mysqli_query($conexion, "select ruta from departamento where id_departamento = $id_departamento");
    $resultado_dep = mysqli_fetch_array($query_dep);
    echo "<br>" . "ruta dep: " . $resultado_dep[0] . "<br>"; 
    
    // se crea la ruta en donde se guardará el archivo
    //$ruta = '/sgc/documentos_provisional/' . $resultado_tdoc[0] . "/". $resultado_dep[0] . "/";
    
    $ruta = "/sgc/documentos_provisional/" . $resultado_tdoc[0] . "/". $resultado_dep[0] . "/";
    echo "ruta archivo: " . $ruta . "<br>";

    // se verifica el tipo de archivo que se va a guardar
    $fileTempPath = $_FILES['ruta']['tmp_name'];
    $fileName = $_FILES['ruta']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    echo "File Extension: " . $fileExtension . "<br>";
    echo "Ubicación temporal: " . $fileTempPath . "<br>";
    echo "Ruta: " . $ruta . "<br>";
    
    $query_t = "select id_tipo_archivo from tipo_archivo where ruta = '" . $fileExtension . "'";
    echo "query_t: " . $query_t . "<br>";
    $query_tarch = mysqli_query($conexion, $query_t);
    $resultado_tarch = mysqli_fetch_array($query_tarch);
    echo "tipo_archivo: " . $resultado_tarch[0] . "<br>";
    $id_tipo_archivo = $resultado_tarch[0];

    // se agrega el estatus de dentro de la linea de autorización
    
    echo "correo: " . $usuario . "<br>";
    $queryl = "select num_empleado, id_rol from usuario where correo = '" . $usuario . "'";
    echo "correo: " . $queryl . "<br>";
    $query_usuario = mysqli_query($conexion, $queryl);
    $resultado_usuario = mysqli_fetch_array($query_usuario);
    echo "num_empleado: " . $resultado_usuario[0] . "<br>";
    echo "id_rol: " . $id_rol = $resultado_usuario[1] . "<br>";
    if($id_rol == 3) {
        $id_estatus = 100;
        echo "estatus: " . $id_estatus . "<br>";
    }
    else {
        $query_la = mysqli_query($conexion, "select nivel from linea_autorizacion where num_empleado = $resultado_usuario[0] and id_departamento = $id_departamento ");
        $resultado_la = mysqli_fetch_array($query_la);
        $id_estatus = $resultado_la[0];
        $id_estatus++;
        echo "estatus: " . $id_estatus . "<br>";
    }
    

    if($_FILES['ruta']['error'] > 0) {
        echo "Error 1: " . $_FILES['ruta']['error'] . "<br>";
    }
    else {
        echo "Upload: " . $_FILES['ruta']['name'] . "<br>";
        echo "Type: " . $_FILES['ruta']['type'] . "<br>";
        echo "Size: " . ($_FILES['ruta']['size'] / 1024) . " kB<br>";
        echo "Stored in: " . $_FILES['ruta']['tmp_name'] . "<br>";
        echo "Error 2: " . $_FILES['ruta']['error'] . "<br>";

        if(move_uploaded_file($fileTempPath, '/var/www/html/contaduria.uabc.mx' . $ruta . $nombre_archivo)) { 
        //if(move_uploaded_file($fileTempPath, 'C:\\xampp\\htdocs\\' . $ruta . $nombre_archivo)) { validacion windows
            chmod('/var/www/html/contaduria.uabc.mx' . $ruta . $nombre_archivo, 0777); 

            echo "Error 3: " . $_FILES['ruta']['error'] . "<br>";
            echo "Archivo guardado";
        }
        else {
            echo "El archivo no se guardó";
            echo "Error 4: " . $_FILES['ruta']['error'] . "<br>";
            print_r(error_get_last());
        }
    }
     
    // se agrega el id_documento (si es por primera vez, se agrega 0)
    if(isset($_GET['ed'])) {

        


        if($_GET['ed'] == "1") {
            echo "id_documento: " . $id_documento;
            // Se realiza el insert a la tabla
            // query linux $query = "insert into documento_prov (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento, id_documento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento,$id_documento)";
            $query = "insert into documento_prov (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento, id_documento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento,$id_documento)";
            echo "Query: " . $query . "<br>";
            $resultado = mysqli_query($conexion, $query);
            echo "Resultado: " . $resultado;
            
            if(!$resultado) {
                mysqli_close($conexion);
                header("Location: form_documento.php?tdoc=$id_tipo_documento&error=1");
                echo "Error al guardar la sección";
            }
            else {
                mysqli_close($conexion);
                header("Location: nuevo_documento.php?exitoso=1");
                if(isset($_GET['exitoso'])){
                    if($_GET['exitoso'] == "1") {
                        echo "opción de menu registrado";
                    }
                }

            }
        }
        else if($_GET['ed'] == "2") {
            // Se realiza el update a la tabla
            // query linux $query = "insert into documento_prov (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento, id_documento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento,$id_documento)";
            $query = "update documento_prov set nombre = '$nombre', ruta = '$ruta', nombre_archivo = '$nombre_archivo', id_tipo_archivo = '$id_tipo_archivo', id_tipo_documento = '$id_tipo_documento', fecha_creacion = '$fecha', id_estatus = $id_estatus, descripcion = '$descripcion', id_clasificador_documento = $id_clasificador_documento, orden_documento = $orden_documento, id_departamento = $id_departamento, id_documento = $id_documento where id_documento_prov = $id_documento"; 
            echo "Query: " . $query . "<br>";
            $resultado = mysqli_query($conexion, $query);
            echo "Resultado: " . $resultado;
            
            if(!$resultado) {
                mysqli_close($conexion);
                header("Location: form_documento.php?tdoc=$id_tipo_documento&error=1");
                echo "Error al guardar la sección";
            }
            else {
                mysqli_close($conexion);
                header("Location: nuevo_documento.php?exitoso=1");
                if(isset($_GET['exitoso'])){
                    if($_GET['exitoso'] == "1") {
                        echo "opción de menu registrado";
                    }
                }

            }
        }
        else {
            $id_documento = 0;
            // Se realiza el insert a la tabla
            // query linux $query = "insert into documento_prov (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento, id_documento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento,$id_documento)";
            $query = "insert into documento_prov (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento, id_documento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento,$id_documento)";
            echo "Query: " . $query . "<br>";
            $resultado = mysqli_query($conexion, $query);
            echo "Resultado: " . $resultado;
            
            if(!$resultado) {
                mysqli_close($conexion);
                header("Location: form_documento.php?tdoc=$id_tipo_documento&error=1");
                echo "Error al guardar la sección";
            }
            else {
                mysqli_close($conexion);
                header("Location: nuevo_documento.php?exitoso=1");
                if(isset($_GET['exitoso'])){
                    if($_GET['exitoso'] == "1") {
                        echo "opción de menu registrado";
                    }
                }

            }
        }
    }
    elseif($id_rol == 3) {
        
        //echo "id_documento: " . $id_documento;
        // Se realiza el insert a la tabla
        // query linux $query = "insert into documento_prov (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento, id_documento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento,$id_documento)";
        $query = "insert into documento (nombre, ruta, nombre_archivo, id_tipo_archivo, id_tipo_documento, fecha_creacion, id_estatus, descripcion, id_clasificador_documento, orden_documento, id_departamento) values('$nombre','$ruta','$nombre_archivo','$id_tipo_archivo','$id_tipo_documento','$fecha',$id_estatus,'$descripcion',$id_clasificador_documento,$orden_documento,$id_departamento)";
        echo "Query: " . $query . "<br>";
        $resultado = mysqli_query($conexion, $query);
        echo "Resultado: " . $resultado;
            
        if(!$resultado) {
            mysqli_close($conexion);
            header("Location: form_documento.php?tdoc=$id_tipo_documento&error=1");
            echo "Error al guardar la sección";
        }
        else {
            mysqli_close($conexion);
            header("Location: nuevo_documento.php?exitoso=1");
            if(isset($_GET['exitoso'])){
                if($_GET['exitoso'] == "1") {
                    echo "opción de menu registrado";
                }
            }
        }
    }

    
}

?>