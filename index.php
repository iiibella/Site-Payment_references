<?php   
session_start();  
$verifica = 1;  
$_SESSION["verifica"] = $verifica;  
?>  

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link href="include/style.css" rel="stylesheet" type="text/css" />
	<title>Generador de referencias</title>
	<script type="text/javascript" src="include/livevalidation_standalone.compressed.js"></script>
	<script type="text/javascript" src="include/jquery-1.3.2.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {	
			$('#tex_vivienda').blur(function(){
				var dataString = "vivienda=" + document.formareg.tex_vivienda.value;
				
				$.ajax({
					type: "POST",
					url: "vivienda_valida.php",
					data: dataString,
					success: function(data) {
						$('#Info').fadeIn(1000).html(data);
						//alert(data);
					}
				});
			});              
		});    
	</script>
	<script type="text/javascript">
		function isCheck() 
		{ 
			if (document.getElementById( 'TDC' ).checked == true) 
			{ 
				document.getElementById( 'tex_autorizacion' ).disabled = false; 
				document.getElementById( 'tex_monto' ).disabled = false; 
			} 
			else 
			{ 
				document.getElementById( 'tex_autorizacion' ).disabled = true; 
				document.getElementById( 'tex_monto' ).disabled = true; 
			} 
		} 
	</script>
	<script type="text/javascript">
		function validar(frm) 
		{  
			if (frm.valida.value.length > 0) 
			{
				return true;
			}		 
			return false;  
		}
	</script>
</head>

<body>
	<br />
	<br />
	<div class="box">Genera referencias para bancos y OXXO			
	</div>
	<br />
	<br />
	<!--table cellpadding="0" cellspacing="0" border="0" width="75%" align= "center">
			<tr>				
				<td class="td1" align="center" colspan="2" > <h3> IMPORTANTE: Para los pagos de estacionamientos y/o bodegas, debes ingresar el número de cliente y clave primaria asociada a la vivienda. </h3>  </td>
			</tr>
	</table-->
	<form action="generador.php" method="POST" name="formareg" id="formareg" onsubmit="return validar(this)">
		<table cellpadding="0" cellspacing="0" border="0" width="60%" align= "center">
						
			<tr>
				<td class="td1" align="right" >Clave de la vivienda (clave primaria)</td>
				<td  class="td1" align="left" >
					<input name="tex_vivienda" id="tex_vivienda" type="text" maxlength="11" disabled="disabled"/>
					<script type="text/javascript">
						var vivienda = new LiveValidation('tex_vivienda', { validMessage: " " } );
						vivienda.add( Validate.Presence, { failureMessage: "" } );
						vivienda.add( Validate.Length, { is: 11, failureMessage: "" } );
					</script>
					<div id="Info"><input type="hidden" id="valida" name="valida" value="" /></div>
				</td>
			</tr>
			<tr>
				<td class="td1" align="right">Número del cliente</td>
				<td  class="td1" align="left">
					<input name="tex_cliente" id="tex_cliente" type="text" maxlength="5" disabled="disabled"/>
					<script type="text/javascript">
						var numcliente = new LiveValidation('tex_cliente');
						numcliente.add( Validate.Numericality, { onlyInteger: true } );
						numcliente.add( Validate.Presence );
						numcliente.add( Validate.Length, { is: 5 } );
					</script>					
				</td>
			</tr>
			
			<tr>
				<td class="td1" colspan ="2" align="center">&nbsp;</td>
			</tr>
			<tr>
				<td class="td1" colspan ="2" align="center"><h2> IMPORTANTE <br><br> Todas las referencias de pago deben de ser emitidas en Dynamics. </h2></td>
			</tr>			

			<tr>
				<td class="td1" colspan ="2" align="center">&nbsp;</td>
			</tr>			
			<tr>
				<td width="35%" class="td1" align="right" >Nombre completo del cliente</td>
				<td  width="65%" class="td1" align="left" >
					<input name="tex_nombre" id="tex_nombre" type="text" maxlength="50" disabled="disabled"/>
					<script type="text/javascript">
						var nombre = new LiveValidation('tex_nombre');
						nombre.add( Validate.Presence );
					</script>
				</td>
			</tr>
			<tr>
				<td class="td1" align="right" >E-mail del cliente</td>
				<td  class="td1" align="left" >
					<input name="tex_email" id="tex_email" type="text" maxlength="50" size="35" disabled="disabled"/>
					<script type="text/javascript">
						var email = new LiveValidation('tex_email');
						email.add(Validate.Presence);
						email.add(Validate.Email);
					</script>
				</td>
			</tr>			
			<tr>
				<td class="td1" align="right">Nombre del asesor</td>
				<td  class="td1" align="left">
					<input name="tex_asesor" id="tex_asesor" type="text" maxlength="20" disabled="disabled"/>
					<script type="text/javascript">
						var asesor = new LiveValidation('tex_asesor');
						asesor.add( Validate.Presence );
					</script>
				</td>
			</tr>						
			<tr>
				<td class="td1" align="right">Pago con tarjeta</td>
				<td  class="td1" align="left"><input name="TDC" id="TDC" type="checkbox" value="S" onclick="isCheck()" disabled="disabled"></td>
			</tr>
			<tr>
				<td class="td1" align="right">Número de aprobación</td>
				<td  class="td1" align="left">
					<input name="tex_autorizacion" id="tex_autorizacion" type="text" maxlength="11" disabled="disabled"/>
					<script type="text/javascript">
						var autorizacion = new LiveValidation('tex_autorizacion');
						autorizacion.add( Validate.Presence );
					</script>
				</td>
			</tr>
			<tr>
				<td class="td1" align="right">Monto de pago</td>
				<td  class="td1" align="left">
					<input name="tex_monto" id="tex_monto" type="text" maxlength="7" disabled="disabled"/>
					<script type="text/javascript">
						var monto = new LiveValidation('tex_monto');
						monto.add( Validate.Presence );
						monto.add( Validate.Numericality );
					</script>
				</td>
			</tr>			
			<tr>
				<td class="td1" colspan ="2" align="center">&nbsp;</td>
			</tr>
		</table>
		<table cellpadding="0" cellspacing="0" border="0" width="50%" align= "center">
			<tr>				
				<td class="td1" align="right" width="30%"><input type="button" value="Limpiar datos" onClick=" window.location.href='index.php'" disabled="disabled"></td>
				<td class="td1" align="right" width="50%"><input type="submit" name="generar" value="Generar referencia" disabled="disabled"></td>				
				<td class="td1" align="center" width="20%"></td>
			</tr>
		</table>
	</form>
</body>

</html>