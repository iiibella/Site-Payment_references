<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
<script type="text/javascript" src="include/jquery-1.3.2.min.js"></script>    
<script type="text/javascript" src="include/jquery-barcode.js"></script>  
<link href="include/style.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>

<?php
session_start();  
include('barcode/code128.class.php');
include ("include/conexion.php");

function luhn_check($number) 
{
	// If the total mod 10 equals 0, the number is valid
	return (luhn_process($number) % 10 == 0) ? TRUE : FALSE;
}

function luhn_process($number) 
{	
	// Srip any non-digits (useful for credit card numbers with spaces and hyphens)
	$number=preg_replace('/\D/', '', $number);

	// Set the string length and parity
	$number_length=strlen($number);
	$parity=$number_length % 2;

	// Loop through each digit and do the maths
	$total=0;
	for ($i=0; $i<$number_length; $i++) 
	{
		$digit=$number[$i];
		// Multiply alternate digits by two
		if ($i % 2 == $parity) 
		{
			$digit*=2;
			// If the sum is two digits, add them together (in effect)
			if ($digit > 9) 
			{
				$digit-=9;
			}
		}
		// Total up the digits
		$total+=$digit;
	}
	return $total;
}

function luhn_checkdigit($number) 
{
	$num = 10 - (luhn_process($number.'0') % 20);
	if ( $num < 0 )
		$num = 10 - abs($num);
	if ( $num > 9)
		$num = substr($num,1,1);

	return $num;
}

function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) 
{
	$uid = md5(uniqid(time()));
	$mime_boundary = "==Multipart_Boundary_x{$uid}x"; 
	
	$name = basename($file);
	
	$header = "From: ".$from_name." <".$from_mail.">\r\n";
	$header .= "Reply-To: ".$replyto."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$mime_boundary."\"\r\n\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n";
	$header .= "--".$mime_boundary."\r\n";
	$header .= "Content-type:text/plain; charset=utf-8\r\n";
	$header .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	$header .= $message."\r\n\r\n";
	$header .= "--".$mime_boundary."\r\n";
	
	$file = $path.$filename;
	$file_size = filesize($file);
	$handle = fopen($file, "rb");
	$content = fread($handle, $file_size);
	fclose($handle);
	$content = chunk_split(base64_encode($content));

	$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
	$header .= "Content-Transfer-Encoding: base64\r\n";
	$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
	$header .= $content."\r\n\r\n";
	//$header .= "--".$mime_boundary."\n";
	$header .= "--".$mime_boundary."--";

	/*$file = $path."Guia_Transferencias.pdf";
	$file_size = filesize($file);
	$handle = fopen($file, "rb");
	$content = fread($handle, $file_size);
	fclose($handle);
	$content = chunk_split(base64_encode($content));
	
	$header .= "Content-Type: application/octet-stream; name=\""."Guia_Transferencias.pdf"."\"\r\n"; // use different content types here
	$header .= "Content-Transfer-Encoding: base64\r\n";
	$header .= "Content-Disposition: attachment; filename=\""."Guia_Transferencias.pdf"."\"\r\n\r\n";
	$header .= $content."\r\n\r\n";	
	$header .= "--".$mime_boundary."--";*/

	if (!mail($mailto, $subject, "", $header)) 
		echo "mail send ... ERROR!";
}

$verifica = $_SESSION["verifica"];  

if ($verifica == 1)
{   
	unset($_SESSION['verifica']);  

	//variables pasadas por metodo POST
	$nomclinete = $_POST["tex_nombre"];
	$ncliente   = $_POST["tex_cliente"];
	$vivienda   = $_POST["tex_vivienda"];
	$asesor		= $_POST["tex_asesor"];
	$fecha		= date("Y-m-d H:i:s");
	$emailcte	= $_POST["tex_email"];
	$nautorizacion = "";
	$monto = "";
	
	if (isset($_POST["TDC"]))
	{
		$pagotarjeta = $_POST["TDC"];
		$nautorizacion = $_POST["tex_autorizacion"];
		$monto	       = $_POST["tex_monto"];
	}
	
	/* Se obtiene la clave de referencia de la base de datos */
	$vivienda = strtoupper($vivienda);
	$cvedes = "";
	
	$total = mysql_num_rows(mysql_query("select claveprimaria, idnumeroventa from numeroventa where claveprimaria = '".strtoupper($vivienda)."' AND numclienteabraham = '".$ncliente."'"));
	if($total==0)
		mysql_query("insert into numeroventa(claveprimaria, numclienteabraham) values('".strtoupper($vivienda)."','".$ncliente."')");
	
	$query = "select claveprimaria, idnumeroventa from numeroventa where claveprimaria = '".strtoupper($vivienda)."' AND numclienteabraham = '".$ncliente."'";
	$resultado = mysql_query($query) or die('ok');	
	$fila = mysql_fetch_row($resultado);
	
	if ($fila[0] == strtoupper($vivienda))
		$cvedes = $fila[1];
	else
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; url=index.php'>";

	//Concatenamos el digito verificador asignado por Oxxo 09  + Concatenamos un cero para tener una longitud de 6 digitos en numerocliente
	$cvedes = "300".$cvedes;
	//echo "Refencia numerica con digito asignado por Oxxo ->".$cvedes." <br/>";
	//echo "longitud -> ".strlen($cvedes)." <br/>";
	
	//Asignamos el digito verificador con el algoritmo de Luhn o Modulo 10
	$cvedes = $cvedes.luhn_checkdigit($cvedes);
	//echo "Refencia numerica con digito verificador para Oxxo ->".$cvedes." <br/>";
	//echo "longitud -> ".strlen($cvedes)." <br/>";
	//Verificamos si es correcto el digito verificador
	//echo "Verifico si es valido el modulo 10 para ".$cvedes." -> ".(luhn_check($cvedes) ? 'Valido' : 'Invalido')."<br/>";
	
	//Este codigo fue creado por Abraham pero ya no sirve :-P
	//$monto_oxxo= $monto_oxxo."00";	
	//$largo_monto_oxxo = strlen($monto_oxxo);	
	//for ($t=7-(7-$largo_monto_oxxo); $t<7; $t++)
	//{			
	//	$monto_oxxo=  "0".$monto_oxxo;		
	//}
	
	//$referenciaoxxo = substr($cvedes,0,-1)."12122060".$monto_oxxo.luhn_checkdigit(substr($cvedes,0,-1)."12122060".$monto_oxxo);	
	$referenciaoxxo = substr($cvedes,0,-1)."00000000000".luhn_checkdigit(substr($cvedes,0,-1)."00000000000");	
	
	//$referenciaoxxo = $cvedes;
	$referenciahsbc = $cvedes;

	$sql = "INSERT INTO referenciapagos(nombrecliente, nombreasesor, numerocliente, numerovivienda, numeroreferencia, rutapdf, numeroaprobacion, monto, email) VALUES ('$nomclinete', '$asesor', '$ncliente', '$vivienda', '$referenciahsbc', 'ReferenciasPagos_GuiaTraspasos-$ncliente.pdf', '$nautorizacion', '$monto', '$emailcte');";
	//echo $sql." <br/>";
	$result = mysql_query($sql);
		
	echo "<table cellpadding='0' cellspacing='0' width='80%' align='center' border='0'>";	
	echo "	<tr>";
	//echo "		<td align='left'><a href='http://localhost:8080/referenciasdepago/docs/ReferenciasPagos_GuiaTraspasos-".$ncliente.".pdf' target='_blank'>Ver PDF</a></td>";
	echo "		<td colspan='3' align='left'><a href='http://quierocasaya.com.mx/referenciasdepago/docs/ReferenciasPagos_GuiaTraspasos-".$ncliente.".pdf' target='_blank'>Ver PDF</a></td>";
	echo "		<td align='right'><a href='index.php'>Ingresar referencia</a></td>";
	echo "	</tr>";
	echo "  <tr><td colspan='2'>&nbsp;</td></tr> ";
	echo "</table>";
	ob_start(); 
?>

<body>
	
	<table cellpadding="0" cellspacing="0" width="80%" align="center" border="0">
		
		<tr>
			<td colspan="2"><img src="images/logoIQC.jpg" width="260" height="55"></td>
			<td colspan="1" align="right"><?php $hoy = $fecha; echo "<b>Fecha:</b> ".date("d/m/Y",  strtotime($hoy));?></td>
		</tr>
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>

		<tr><td colspan="3" align="center"><h3>Hoja de Referencia y Guía para Transferencias Bancarias</h3></td></tr>		
		
		<tr><td colspan="3">Estimado(a): <b><?php echo $nomclinete; ?></b></td></tr>
		
		<!--tr><td colspan="3"><b>&nbsp;</b></td></tr>
	
		<tr><td colspan="3"><b>¡Felicidades!</b>&nbsp;Ya eres parte de Quiero Casa.</td></tr-->
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		
		<tr>	
			<td colspan="3">				
				A continuación te proporcionamos tu Referencia de Pago. La referencia es únicamente para hacer transferencias electrónicas a <br/>
				<b>HSBC</b> desde HSBC o desde tu banco (SPEI). La referencia <b>no aplica</b> para depósitos en ventanilla ni transferencias TEF.
			</td>
		</tr>
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>

		<tr>			
			<td colspan="3" >				
				Razón Social:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Inmobiliaria Quiero Casa SA de CV</b><br/>
				RFC:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>IQC090714AC6</b>
			</td>			
		</tr>
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
			
		<tr>
			<td width="50%" align="center"><img src="images/logohsbc.jpg" width="110" height="25"></td>
			<td width="25%" class="letraHSBC">				
				RAP:<br/>
				Referencia de depósito:
			</td>
			<td class="letraHSBC">
				<b>4713</b><br/>
				<b><?php echo $referenciahsbc; ?></b>
			</td>
		</tr>

		<tr>
			<td colspan="3" >&nbsp;</td>			
		</tr>

		<tr>
			<td colspan="3" ><h3>Para transferencias electronicas (SPEI) a HSBC desde: </h3></td>
		</tr>
		
		<tr>
			<td width="50%" align="center"><img src="images/logobanamex.jpg" width="130" height="25"></td>
			<td width="25%" class="letraHSBC">
				Tipo de transferencia electrónica:<br/>
				Banco destino:<br/>
				CLABE interbancaria:<br/>
				Número de referencia:<br/>
				Concepto de pago:
			</td>
			<td class="letraHSBC">
				<b>SPEI (no aplica TEF)</b><br/>
				<b>HSBC</b><br/>
				<b>021180550300047138</b><br/>
				<b>5503</b><br/>
				<b><?php echo $referenciahsbc; ?></b>
			</td>
		</tr>

		<tr>
			<td colspan="3" ><hr></td>
		</tr>

		<tr>
			<td width="50%" align="center"><img src="images/logobancomer.jpg" width="130" height="25"></td>
			<td width="25%" class="letraHSBC">
				Tipo de transferencia electrónica:<br/>
				Banco destino:<br/>
				CLABE interbancaria:<br/>
				Referencia numérica:<br/>
				Motivo de pago:
			</td>
			<td class="letraHSBC">
				<b>SPEI (no aplica TEF)</b><br/>
				<b>HSBC</b><br/>
				<b>021180550300047138</b><br/>
				<b>5503</b><br/>
				<b><?php echo $referenciahsbc; ?></b>
			</td>
		</tr>

		<tr>
			<td colspan="3" ><hr></td>
		</tr>

		<tr>
			<td width="50%" align="center"><img src="images/logobanorte.jpg" width="130" height="25"></td>
			<td width="25%" class="letraHSBC">
				Tipo de transferencia electrónica:<br/>
				Banco destino:<br/>
				CLABE interbancaria:<br/>
				Número de referencia:<br/>
				Concepto de pago:
			</td>
			<td class="letraHSBC">
				<b>SPEI (no aplica TEF)</b><br/>
				<b>HSBC</b><br/>
				<b>021180550300047138</b><br/>
				<b>5503</b><br/>
				<b><?php echo $referenciahsbc; ?></b>
			</td>
		</tr>

		<tr>
			<td colspan="3" ><hr></td>
		</tr>

		<tr>
			<td width="50%" align="center"><img src="images/logoscotiabank.jpg" width="110" height="30"></td>
			<td width="25%" class="letraHSBC">
				Tipo de transferencia electrónica:<br/>
				Banco destino: <br/>
				CLABE interbancaria:<br/>
				Referencia numérica:<br/>
				Referencia alfanumérica:
			</td>
			<td class="letraHSBC">
				<b>SPEI (no aplica TEF)</b><br/>
				<b>HSBC</b><br/>
				<b>021180550300047138</b><br/>
				<b>5503</b><br/>
				<b><?php echo $referenciahsbc; ?></b>
			</td>
		</tr>

		<tr>
			<td colspan="3" ><hr></td>
		</tr>

		<tr>
			<td width="50%" align="center"><img src="images/logosvisamaster.jpg" width="110" height="30"></td>
			<td colspan="2" ><font size="2">Pago con tarjetas de crédito o débito en Puntos de Venta Quiero Casa</font></td>
		</tr>

		<tr>
			<td colspan="3" ><hr></td>
		</tr>
		
		<tr>
			<td align="center"><img src="images/logooxxo.jpg"></td>
			<td colspan="2" align="center">
				<?php 
					//Creamos el codigo de barras
					//$referenciaoxxo = "";
					$barcode = new phpCode128($referenciaoxxo, 130, 'barcode/verdana.ttf', 1);
					$barcode->saveBarcode('docs/'.$referenciaoxxo.'-'.$vivienda.'.png');
					$barcode->setEanStyle(false);
					$barcode->setBorderWidth(1);
					//$barcode->setTextSpacing(5);
					echo "<img src='docs/".$referenciaoxxo.'-'.$vivienda.".png'>";					
				?>
			</td>
		</tr>
		<tr>
			<td colspan="3" height="35" align="center" class="letraChica">
				NOTA: Para el pago en OXXO aplica una comisión adicional de $8.00 M.N.
			</td>
		</tr>
		
		<tr>
			<td colspan="3"><hr></td>
		</tr>		
		
		<tr>
			<td colspan="3" align="center">
				<b>Una vez hecho el pago, <u>entrega el comprobante</u> a tu Asesor de Ventas para que obtengas el recibo oficial Quiero Casa.</b>
			</td>
		</tr>
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>		

		<tr>
			<td colspan="3" align="center">Para dudas o aclaraciones puedes escribirnos a <a href="mailto:depositos@quierocasa.com.mx">depositos@quierocasa.com.mx</a> o llamar al teléfono<br>
			 3603 9707, lunes a viernes de 9:00 a 18:00 Hrs.</td>
		</tr>
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>

		<tr>
			<td colspan="3" height="35" class="letraChica" align="center">Este documento carece de validez oficial, está diseñado únicamente como referencia para poder realizar tu pago.</td>
		</tr>	
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>		
				
		<tbody style="display:none">
		
		<tr>
			<td colspan="3" align="left"><b>ESTIMADO CLIENTE: </b></td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="left">Para poder realizar pagos de tu vivienda es necesario dar de alta la cuenta SPEI referenciada de Quiero Casa.  </td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="center"><b><u>ALTA DE CUENTA DE QUIERO CASA DESDE EL PORTAL DE HSBC </u></b></td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="left">Para que des de alta la cuenta de Quiero Casa en el portal de HSBC te pedimos que sigas estos pasos: <br/><br/>
				1.	Ingresa al portal de HSBC – opción Banca Personal por Internet y registra tu usuario. <br/><br/>
				2.	Da clic en los botones Pagos / Buscar servicio RAP. <br/><br/>
				3.	Teclea el nombre de la Razón Social de Quiero Casa o el Número de Servicio:			
			</td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="center"><b>Razón Social: INMOBILIARIA QUIERO CASA SA DE CV <br/>
				Numero de servicio: 4713 </b>
			</td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="left">
				4.	Da clic en el botón Buscar <img src="images/1.jpg" height="15" width="60">.<br/><br/>
				5.	Selecciona la Clave de Servicio 4713 <img src="images/2.jpg" height="20" width="60"> y da clic en el botón Aceptar <img src="images/3.jpg" height="15" width="60">. <br/><br/>
				6.	En el Campo “Referencia1” teclea tu Referencia de Depósito <b><?php echo $referenciahsbc; ?></b> y da clic en el<br/>
				    botón Aceptar <img src="images/3.jpg" height="15" width="60">.
			</td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>				
		
		<tr>
			<td colspan="3" align="center"><b><u>ALTA DE CUENTA DE QUIERO CASA DESDE EL PORTAL DE CUALQUIER BANCO</u></b></td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="left">Para que des de alta la cuenta de Quiero Casa desde el portal de tu banco te pedimos que sigas estos pasos:			 				
			</td>
		</tr>
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="left"> 			
				1. Ingresa al portal de tu banco – opción Banca por Internet y registra tu usuario y tu token.<br/><br/>
				2. Dependiendo de tu banco, debes buscar la opción que te permita dar de alta cuentas de otros bancos (Transferencia <br/>
				   / Traspaso). Este proceso sólo se realiza 1 vez. <br/><br/>
				<b>Nota:</b> Algunos bancos solicitan la generación de 1 clave de acceso.<br/><br/>
				3. Registra los datos del beneficiario:  <br/><br/>
			</td>
		</tr>
		
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">a. Banco: <b>HSBC</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">b. CLABE: <b>021180550300047138</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">c. Nombre: <b>Inmobiliaria Quiero Casa SA de CV</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">d. RFC: <b>IQC090714AC6</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">e. Selecciona la opción <b>Persona moral</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">f. Monto máximo: <b>Cantidad que desees traspasar (mensualidad, pago total, etc.)</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">g. Correo electrónico del beneficiario: <b>recibosdepago@quierocasa.com.mx</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">h. Alias de la cuenta: <b>Nombre para identificar al beneficiario</b></td>			
		</tr>
		
		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
		<tr>
			<td colspan="3" align="left"> 
				<b>Nota1:</b> Dependiendo de tu banco, algunos datos son opcionales.<br/><br/>
				<b>Nota2:</b> Algunos bancos solicitan que esperes un tiempo para activar la nueva cuenta.<br/><br/>
				<b>Nota3:</b> Algunos bancos envían por correo electrónico el código de activación que deberás registrar en el portal para<br/>
				 continuar con el alta. <br/><br/>
				
				5. Una vez dada de alta la cuenta, busca la opción Pago / Transferencia y registra los datos que te pida tu banco <br/>
 				   para realizar el traspaso. <br/><br/>										 
				6. Debes realizar la transferencia con los siguientes datos: <br/>										
			</td>
		</tr>		<tr><td colspan="3"><b>&nbsp;</b></td></tr>
				
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">a. Cuenta Beneficiaria: <b>021180550300047138</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">b. Referencia: <b>5503</b></td>			
		</tr>
		<tr>
			<td width="15%">&nbsp;</td>
			<td width="85%" colspan="2">c. Descripción / concepto de pago: <b><?php echo $referenciahsbc; ?></b></td>			
		</tr>
		</tbody>
				
	</table>

</body>


<?php

	//Convertimos a PDF 
	require_once('pdf/html2pdf.class.php');
	$htmlbuffer=ob_get_contents();
	//ob_end_clean(); 
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($htmlbuffer, isset($_GET['vuehtml']));
        $html2pdf->Output("docs/ReferenciasPagos_GuiaTraspasos-".$ncliente.".pdf",'F');
		
		echo "<table cellpadding='0' cellspacing='0' width='80%' align='center' border='0'>";	
		echo "	<tr>";
		//echo "		<td align='left'><a href='http://localhost:8080/referenciasdepago/docs/ReferenciasPagos_GuiaTraspasos-".$ncliente.".pdf' target='_blank'>Ver PDF</a></td>";
		echo "		<td colspan='3' align='left'><a href='http://quierocasaya.com.mx/referenciasdepago/docs/ReferenciasPagos_GuiaTraspasos-".$ncliente.".pdf' target='_blank'>Ver PDF</a></td>";
		echo "		<td align='right'><a href='index.php'>Ingresar referencia</a></td>";
		echo "	</tr>";
		echo "</table>";
		
		//Enviamos por correo el pdf	
		$my_file = "ReferenciasPagos_GuiaTraspasos-".$ncliente.".pdf"; // puede ser cualquier formato
		$my_path = $_SERVER['DOCUMENT_ROOT']."/referenciasdepago/docs/";
		$my_name = "Quiero casa";
		$my_mail = "contacto@quierocasaya.com.mx";
		$my_replyto = "";
		$my_subject = "¡Felicidades! Ya eres parte de Quiero Casa.";
		$my_message = "Estimado(a): $nomclinete \r\n\r\n Quiero Casa te da la más cordial bienvenida a su familia inmobiliaria en la que encontrarás una excelente calidad de vida. \r\n\r\n Anexo al presente, adjuntamos el archivo PDF que contiene la Referencia de Pago para que realices tus depósitos mensuales. Los pagos pueden ser efectuados a través de: \r\n\r\n - Tiendas OXXO \r\n - Sucursales bancarias \r\n - Transferencias por internet (SPEI) \r\n - Tarjeta de crédito \r\n\r\n Tu número de referencia es: $referenciahsbc \r\n\r\n Te recomendamos imprimir el archivo PDF y utilizarlo en cada pago que hagas para asegurarte un proceso fácil y transparente. Si tuvieras alguna duda, puedes comunicarte a nuestro departamento de Atención a Clientes: \r\n\r\n Teléfonos de Atención a Clientes 3603 9707, lunes a viernes de 9:00 a 18:00 Hrs.  \r\n\r\n ¡Felicidades! Ya eres parte de Quiero Casa. ";
		//echo $my_message;
		mail_attachment($my_file, $my_path, $emailcte, $my_mail, $my_name, $my_replyto, $my_subject, $my_message);		
	}
    catch(HTML2PDF_exception $e) 
	{
        echo $e;
        exit;
    }
}
else
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; url=index.php'>"; 	
?>

</html>