<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		
		<link href="include/style.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" type="text/css" href="css/calendar-eightysix-v1.1-default.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="css/calendar-eightysix-v1.1-vista.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="css/calendar-eightysix-v1.1-osx-dashboard.css" media="screen" />
		
		<script type="text/javascript" src="js/mootools-1.2.4-core.js"></script>
		<script type="text/javascript" src="js/mootools-1.2.4.4-more.js"></script>
		<script type="text/javascript" src="js/calendar-eightysix-v1.1.js"></script>
		
		<script type="text/javascript">
			window.addEvent('domready', function() {
								
				MooTools.lang.set('sp-ES', 'Date', {
					months:    ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
					days:      ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
					dateOrder: ['dia', 'mes', 'año', '/']
				});
				
				new CalendarEightysix('fechaIni', { 'offsetY': -4, 'format': '%d/%m/%Y', 'theme': 'default red' });				
				$('fechaIni').addEvent('click', function() { MooTools.lang.setLanguage('sp-ES'); });
				
				new CalendarEightysix('fechaFin', { 'offsetY': -4, 'format': '%d/%m/%Y', 'theme': 'default red' });				
				$('fechaFin').addEvent('click', function() { MooTools.lang.setLanguage('sp-ES'); });
			});				
		</script>
		<script type="text/javascript">
			function validar(frm) 
			{  
				return true;
			}
		</script>
		<script type="text/javascript">
		function isCheck_f() 
		{ 
			document.getElementById( 'reporteTodo' ).checked = false; 		
		} 
		
		function isCheck_t() 
		{ 
			document.getElementById( 'reporteFecha' ).checked = false; 		
		} 
		</script>
		
		<title>Generador de referencias - Reporte</title>
	</head>

	<body>
		<br />
		<br />
		<div class="box">Reporte de referencias de pago	en formato MS Excel
		</div>
		<br />
		<br /> <!--reporteexcel.php-->
		<form action="reporteexcel.php" method="POST" name="formareporte" id="formareporte" onsubmit="return validar(this)">
			<table cellpadding="0" cellspacing="0" border="0" width="25%" align= "center">
				<tr>
					<td colspan="2" class="td1" align="left">
						<input name="reporteFecha" id="reporteFecha" type="radio" checked="true" onclick="isCheck_f()">Datos por rango de fecha</input>
					</td>				
				</tr>
				<tr>
					<td colspan="2" class="td1" align="left">&nbsp;</td>				
				</tr>
				<tr>
					<td class="td1" align="right" width="40%" >Fecha inicial</td>					
					<td class="td1" align="left" >
						<input id="fechaIni" name="fechaIni" type="text" maxlength="10" />										
					</td>
				</tr>
				<tr>
					<td class="td1" align="right" >Fecha final</td>					
					<td class="td1" align="left" >
						<input id="fechaFin" name="fechaFin" type="text" maxlength="10" />						
					</td>
				</tr>
				<tr>
					<td colspan="2" class="td1" align="left">&nbsp;</td>				
				</tr>
				<tr>
					<td colspan="2" class="td1" align="left">
						<input name="reporteTodo" id="reporteTodo" type="radio" onclick="isCheck_t()">Todos los datos</input>
					</td>				
				</tr>
				<tr>
					<td colspan="2" class="td1" align="left">&nbsp;</td>				
				</tr>			
				<tr>
					<td colspan="2" class="td1" align="center" >Clave de acceso
					</td>
				</tr>
				<tr>
					<td colspan="2" class="td1" align="center" ><input id="pwd" name="pwd" type="password" maxlength="20" />						
					</td>
				</tr>
				<tr>
					<td colspan="2" class="td1" align="center">
						<input type="submit" name="generar" value="Generar">
					</td>				
				</tr>
			</table>	
			
		</form>
	</body>
</html>
