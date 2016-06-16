<?php 
//Conexion a la base de datos
include('../../../../dbconexion/conexion.php');
//funcion para abrir conexion
$cn = Conectarse();
$fechaDesde = $_GET['fechaDesde'];
$fechaHasta = $_GET['fechaHasta'];
$sede = $_GET['sede'];
$servicio = $_GET['servicio'];
$usuario = $_GET['usuario'];
$especialista = $_GET['especialista'];
$fechaDesde = date("Y-m-d",strtotime($fechaDesde));
$fechaHasta = date("Y-m-d",strtotime($fechaHasta));
//consultar la cantidad de estudios que estan agendados para la fecha especificada
$sqlagenda = mysql_query("SELECT i.id_informe, i.id_paciente,CONCAT(p.nom1,' ', p.nom2,' ',
p.ape1,' ', p.ape2) AS nombre ,e.nom_estudio, l.fecha, l.hora, pr.desc_prioridad, tp.desctipo_paciente, tec.desc_tecnica
FROM r_informe_header i
INNER JOIN r_paciente p ON p.id_paciente = i.id_paciente
INNER JOIN r_estudio e ON e.idestudio = i.idestudio
INNER JOIN r_log_informe l ON l.id_informe = i.id_informe
INNER JOIN r_prioridad pr ON pr.id_prioridad = i.id_prioridad 
INNER JOIN r_tipo_paciente tp ON tp.idtipo_paciente = i.idtipo_paciente
INNER JOIN r_tecnica tec ON tec.id_tecnica = i.id_tecnica
WHERE i.id_estadoinforme = '2' AND idfuncionario_esp = '0' AND i.idsede = '$sede' AND i.idservicio = '$servicio' AND l.id_estadoinforme = '1' AND l.fecha BETWEEN '$fechaDesde' AND '$fechaHasta' GROUP BY i.id_informe", $cn);
$sqlSede = mysql_query("SELECT descsede FROM sede WHERE idsede='$sede'", $cn);
$regSede = mysql_fetch_array($sqlSede);
$sqlServicio = mysql_query("SELECT descservicio FROM servicio WHERE idservicio = '$servicio'", $cn);
$regServicio = mysql_fetch_array($sqlServicio);
?>
<script type="text/javascript">
 $(document).ready(function(){
   $('#tabla_listado_pacientes').dataTable( { //CONVERTIMOS NUESTRO LISTADO DE LA FORMA DEL JQUERY.DATATABLES- PASAMOS EL ID DE LA TABLA
        "sPaginationType": "full_numbers",
		"aaSorting": [[ 6, "asc" ]],
"aoColumns": [ null, null, null, null, null, null, null, null ]
} );
} );
</script>
<form name="Estudios" id="Estudios" method="post" action="Querys/AccionAsignarInforme.php">
<table width="100%">
<tr bgcolor="#E1DFE3">
    <td><strong>Estudios pendientes por lectura <?php echo $regServicio['descservicio'] ?> en <?php echo $regSede['descsede'] ?></strong></td>
    <td align="right"><input type="submit" value="Asignar a Especialista" id="Asignar"></td>
</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="tabla_listado_pacientes">
<thead>
<tr>
    <th align="left" width="8%">Id</th><!--Estado-->
    <th align="left" width="15%">Paciente</th>
    <th align="left" width="30%">Estudio</th>
    <th align="left" width="10%">Tecnica</th>
    <th align="left" width="10%">T.Paciente</th>
    <th align="center" width="5%">Prioridad</th>
    <th align="center" width="5%">Fecha/Hora Estudio Tomado</th>
    <th align="center" width="12%">Tareas</th>
</tr>
</thead>
<tfoot>
<tr>
    <th></th>
    <th></th>
   <th></th>                     
</tr>
</tfoot>
<tbody>
<?php
while($reg =  mysql_fetch_array($sqlagenda))
{
//Codificar variables para pasar por URL
$idInforme = base64_encode($reg['id_informe']);
$user = base64_encode($usuario);
$Fecha = $reg['fecha'];
list($año, $mes, $dia) = explode("-",$Fecha);
$Fecha=$dia.'/'.$mes.'/'.$año;
echo '<tr>';
echo '<td align="left">'.$reg['id_paciente'].'</td>';
echo '<td align="left">'.ucwords(strtolower($reg['nombre'])).'</td>';
echo '<td align="left">'.ucwords(strtolower($reg['nom_estudio'])).'</td>';
echo '<td align="left">'.ucwords(strtolower($reg['desc_tecnica'])).'</td>';
echo '<td align="left">'.$reg['desctipo_paciente'].'</td>';
echo '<td align="center">'.ucwords(strtolower($reg['desc_prioridad'])).'</td>';
echo '<td align="center">'.$Fecha.'<br>'.$reg['hora'].'</td>';
echo '<td align="center">';
echo '<table>';
?>
<td>
<?php
	echo '<input type="checkbox" name="id['.$reg['id_informe'].']" id="'.$reg['id_informe'].'" value="'.$reg['id_informe'].'">';
?>
</td>
<td><a href="AsignarEstudio.php?idInforme=<?php echo $idInforme?>&usuario=<?php echo $user ?>" target="pop-up" onClick="window.open(this.href, this.target, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width=800, height=450, top=85, left=140'); return false;"><img src="../../../../images/dokter.png" width="15" height="15" title="Asignar al especialista" alt="Asignar al especialista" /></a></td>
</tr>
</table>
</tr>
<?php
}
mysql_close($cn);
?>
<tbody>
<input type="hidden" name="sede" value="<?php echo $sede ?>">
<input type="hidden" name="servicio" value="<?php echo $servicio ?>">
<input type="hidden" name="especialista" value="<?php echo $especialista ?>">
</form>