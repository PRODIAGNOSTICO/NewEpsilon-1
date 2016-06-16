<?php
	//Conexion a la base de datos
	include('../../../dbconexion/conexion.php');
	$cn = Conectarse();
	//declaracion de variables con POST
	$idturno = $_POST['idTurno'];
	$desde = $_POST['desde'];
	$hasta = $_POST['hasta'];
	$nota = $_POST['nota'];
	$fecha = $_POST['fecha'];
	$evento = $_POST['evento'];
	// ------------------ Variables de referencia para el calculo de las horas -------------------------------- //
	$inicio_nocturnas = "22:00";
	$inicio_diurnas = "06:00";
	$cambio_dia = "00:00";
	$ref_nocturna = strtotime($inicio_nocturnas);
	$ref_diurna = strtotime($inicio_diurnas);
	$ref_cambio = strtotime($cambio_dia);
	//separar la fecha para extraer año, mes, dia
	list($anio, $mes, $dia) = explode("-",$fecha);
	//fecha de referencia para obtener valores en las consultas	
	$fechaReferencia = $fecha;
	//validar campos obligatorios
	if($idTurno=="" || $desde=="" || $hasta=="" || $fecha="")
	{
		echo 'Verifique que no existan campos vacios';
	}
	else
	{
		//consultar que no se haya registrado una novedad para ese turno
		$sql = mysql_query("SELECT * FROM novedad_turno WHERE idturno = '$idTurno'", $cn);
		$reg = mysql_num_rows($sql);
		if($reg>=1)
		{
			echo 'Ya se registro una novedad para el turno';
		}
		else
		{
			//validar el dia de inicio	
			$wkdy = (((mktime ( 0, 0, 0, $mes, $dia, $anio) - mktime ( 0, 0, 0, 7, 17, 2006))/(60*60*24))+700000) % 7;
			
			if($wkdy==6)
			{
				//si equivale a 6 es igual a domingo
				//validar si la novedad se registra el mismo dia
				if($desde<$hasta || $hasta == $cambio_dia)
				{
					//la novedad se cumple dentro del mismo dia
					$ini = strtotime($desde);
					$fin = strtotime($hasta);
					include("Acciones/FestivoIgual.php");
				}
				else
				{
					//validar si se registra de un dia hacia otro y a que equivale el dia siguiente	
					$nuevafecha = strtotime ( '+1 day' , strtotime ( $fechaReferencia ) ) ;
					$nuevafecha = date ( 'Y-m-d' , $nuevafecha );
					//Validar el tipo de dia de finalizacion
					$sqlFecha = mysql_query("SELECT * FROM dia_festivo WHERE fecha_festivo = '$nuevafecha'", $cn);
					$conFecha = mysql_num_rows($sqlFecha);
					
					if($conFecha==0 || $conFecha=="")
					{
						//el dia de finalizacion es un dia ordinario
						$ini = strtotime($desde);
						$fin = strtotime($hasta);
						include("Acciones/FestivoOrdinario.php");
					}
					else
					{
						//el dia de finalizacion es festivo
						$ini = strtotime($desde);
						$fin = strtotime($hasta);
						include("Acciones/FestivoFestivo.php");
					}	
				}	
			}
			else
			{
				//validar si la novedad se registra el mismo dia
				if($desde<$hasta || $hasta==$cambio_dia)
				{
					//la novedad se cumple dentro del mismo dia
					$ini = strtotime($desde);
					$fin = strtotime($hasta);
					include("Acciones/OrdinarioIgual.php");
				}
				else
				{
					//validar si se registra de un dia hacia otro y a que equivale el dia siguiente	
					$nuevafecha = strtotime ( '+1 day' , strtotime ( $fechaReferencia ) ) ;
					$nuevafecha = date ( 'Y-m-d' , $nuevafecha );
					
					list($anio, $mes, $dia) = explode("-",$nuevafecha); 
					//validar dia de finalizacion
					$wkdy = (((mktime ( 0, 0, 0, $mes, $dia, $anio) - mktime ( 0, 0, 0, 7, 17, 2006))/(60*60*24))+700000) % 7;
					if($wkdy==6)
					{
						$ini = strtotime($desde);
						$fin = strtotime($hasta);
						include("Acciones/OrdinarioFestivo.php");
					}
					else
					{
						//Validar el tipo de dia de finalizacion
						$sqlFecha = mysql_query("SELECT * FROM dia_festivo WHERE fecha_festivo = '$nuevafecha'", $cn);
						$conFecha = mysql_num_rows($sqlFecha);
					
						if($conFecha==0 || $conFecha=="")
						{
							//el dia de finalizacion es un dia ordinario
							$ini = strtotime($desde);
							$fin = strtotime($hasta);
							include("Acciones/OrdinarioOrdinario.php");
						}
						else
						{
							//el dia de finalizacion es festivo
							$ini = strtotime($desde);
							$fin = strtotime($hasta);
							include("Acciones/OrdinarioFestivo.php");
						}	
					}
				}	
			}
		}
	}
?>