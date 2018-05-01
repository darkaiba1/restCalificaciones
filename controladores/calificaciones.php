<?php
/**
 *
 Acceder al recurso materias
 * GET
 * http://localhost:8080/restCalificaciones/calificaciones
 Registro de calificaciones
 * POST
 * http://localhost:8080/restCalificaciones/calificaciones/registro
 *
 Obtener calificacion por id
 * GET
 *  http://localhost:8080/restCalificaciones/calificaciones/[id]
 Modificar calificacion
 * PUT
 * localhost/~instructor/restDocente/calificaciones/[id]
 Eliminar calificaciones
 * DELETE
 * localhost/~instructor/restDocente/materia/[id]
 */
/**
 *
 */
include_once(dirname(__FILE__,2) . '\datos\conexionbd.php');
/**
 * Docente
 * Authorization
 * 2205ee732786a41f2fc0350888840510
 * 
 * Alumno
 * Authorization
 * ce086073cd0dceb567d5272ac5ddcd44
 */
class calificaciones {
    const NOMBRE_TABLA = "calificacion";
    const ID_CALIFICACIONES = "idCalificaciones";
    const ID_MATERIA = "idmateria";
    const ID_ALUMNO = "idAlumno";
    const ID_UNIDAD = "unidad";
    const CALIFICACION = "calificacion";
    const ID_DOCENTE = "idDocente";
    public static function get($solicitud)
    {
      $idAlumno = alumnos::autorizar();
    
      
      if (empty($solicitud)) {
        return self::obtenerCalificaciones($idAlumno);
      } else {
        return self::obtenerCalificaciones($idAlumno, $solicitud[0]);
      }
    }
    public static function post()
    {
      $idDocente = docente::autorizar();
      
      echo "autorizado" .$idDocente;
      $cuerpo = file_get_contents('php://input');
      $calificacion = json_decode($cuerpo);
  
      $claveCalificacion = self::crearCalificacion($idDocente, $calificacion);
  
      http_response_code(201);
  
      return [
        "estado"=>"Registro exitoso",
        "mensaje"=>"calificacion creada",
        "Clave"=>$claveCalificacion
      ];
    }
    public static function put($solicitud)
    {

      $idDocente=docente::autorizar();
      if (!empty($solicitud)) {
      $cuerpo = file_get_contents('php://input');
      $calificacion = json_decode($cuerpo);
      if (self::actualizarCalificacion($idDocente,$calificacion,$solicitud[0])>0) {
        http_response_code(200);
        return [
          "estado"=>"OK",
          "mensaje"=>"Registro Actualizado"
        ];
      }else{
        throw new ExceptionApi("Materia no actualizada",
        "No se actualizo la materia solicitada",404);
      }
      }
      else{
        throw new ExceptionApi("Parametros incorrectos",
        "Faltan parametros para consulta",422);
      }
    }
    public static function delete($solicitud)
    {
      echo $solicitud[0];
      $idDocente=docente::autorizar();
      if (!empty($solicitud)) {
      if (self::eliminarCalificacion($idDocente,$solicitud[0])>0) {
        http_response_code(200);
        return [
          "estado"=>"OK",
          "mensaje"=>"Registro Eliminado"
        ];
      }else{
        throw new ExceptionApi("Materia no Eliminada",
        "No se actualizo la materia solicitada",404);
      }
      }
      else{
        throw new ExceptionApi("Parametros incorrectos",
        "Faltan parametros para consulta",422);
      }
    

    }
    private function obtenerCalificaciones($idAlumno, $claveMateria = NULL)
    {
      try {
        if (!$claveMateria) {
          $sql = "SELECT * FROM " . self::NOMBRE_TABLA .
                 " WHERE " . self::ID_ALUMNO . "=?";
          $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
          $query = $pdo->prepare($sql);
          $query->bindParam(1,$idAlumno,PDO::PARAM_INT);
        } else {
          $sql = "SELECT * FROM " . self::NOMBRE_TABLA .
                 " WHERE " . self::ID_ALUMNO . "=? AND " .
                 self::ID_MATERIA . "=?";
          $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
          $query = $pdo->prepare($sql);
          $query->bindParam(1,$idAlumno,PDO::PARAM_INT);
          $query->bindParam(2,$claveMateria,PDO::PARAM_STR);
        }
        if ($query->execute()) {
          http_response_code(200);
          return [
            "estado" => "OK",
            "mensaje" => $query->fetchAll(PDO::FETCH_ASSOC)
          ];
        } else {
          throw new ExceptionApi("Error en consulta",
                  "Se ha producido un error al ejecutar la consulta");
        }
      } catch (PDOException $e) {
        throw new ExceptionApi("Error de PDO",
                $e->getMessage());
      }
  
    }
    private function crearCalificacion($idDocente, $calificacion){
      if ($calificacion) {
        try {
          $sql = "INSERT INTO " . self::NOMBRE_TABLA . " (" .
            self::ID_MATERIA . "," .
            self::ID_ALUMNO . "," .
            self::ID_UNIDAD . "," .
            self::CALIFICACION . "," .
            self::ID_DOCENTE .
            ") VALUES(?,?,?,?,?);";
          echo $sql;
          $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
          $query = $pdo->prepare($sql);
          $query->bindParam(1,$calificacion->materia,PDO::PARAM_INT);
          $query->bindParam(2,$calificacion->alumno,PDO::PARAM_INT);
          $query->bindParam(3,$calificacion->unidad,PDO::PARAM_INT);
          $query->bindParam(4,$calificacion->calificacion,PDO::PARAM_INT);
          $query->bindParam(5,$idDocente);
          $query->execute();
          
          return $calificacion->calificacion;
  
        } catch (PDOException $e) {
          throw new ExceptionApi("Error de BD",
                  $e->getMessage());
        }
  
      } else {
        throw new ExceptionApi("Error de parametros",
                "Error al pasar la Materia");
      }
    }
    private function actualizarCalificacion($idDocente, $materia, $claveMateria)
    {
      echo "la clave materia es " . $claveMateria;
      try{
        $sql= "UPDATE " . self::NOMBRE_TABLA .
         " set " . self::NOMBRE ."=?, " .
        self::CREDITOS ."=?, " .
        self::HT ."=?, " .
        self::HP ."=? " .
         "WHERE " . self::CLAVE . "=? and " .
         self::ID_DOCENTE . " =?;";
         
         $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
         $query = $pdo->prepare($sql);
         $query->bindParam(1,$materia->nombre);
         $query->bindParam(2,$materia->creditos);
         $query->bindParam(3,$materia->ht);
         $query->bindParam(4,$materia->hp);
         $query->bindParam(5,$claveMateria);
         $query->bindParam(6,$idDocente);
         $query->execute();
         return $query-> rowCount();

      } catch(PDOException $e){
        throw new ExceptionApi("Error en cosulta",$e->getMessage());
      }
    }
    private function eliminarCalificacion($idDocente, $materia)
    {
      try{
        $sql= "DELETE FROM " . self::NOMBRE_TABLA . " WHERE "
         .self::ID_DOCENTE."=? and " 
         .self::CLAVE ."=?;";
         $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
         $query = $pdo->prepare($sql);
         $query->bindParam(1,$idDocente);
         $query->bindParam(2,$materia);
         echo $sql;
         $query->execute();
         return $query-> rowCount();
      } catch(PDOException $e){
        throw new ExceptionApi("Error en cosulta",$e->getMessage());
      }
    }
  }
?>