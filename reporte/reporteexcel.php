<?php
    
	//variables pasadas por metodo POST	
	$fechaIni = "";
	$fechaFin = "";
	$pwd = $_POST["pwd"];
	
	if ($pwd <> "reporte2014")
	{
		echo "<script language='JavaScript'> alert('La clave es incorrecta!!!'); </script>";
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; url=index.php'>"; 
	}
	else
	{		
		$consulta = "SELECT idreferenciapago, CONCAT(' ',numeroreferencia,' ') AS numref, numerocliente, nombrecliente, numerovivienda, email, nombreasesor, numeroaprobacion, monto, CONCAT('http://quierocasaya.com.mx/referenciasdepago/docs/',rutapdf) AS ruta, fecha FROM referenciapagos ";
		
		if (isset($_POST["reporteFecha"]))
		{
			$fechaIni = $_POST["fechaIni"];
			$fechaFin   = $_POST["fechaFin"];		
			$consulta .= " WHERE DATE_FORMAT(fecha,'%d/%m/%Y') BETWEEN '$fechaIni' AND '$fechaFin'";
		}
		
		$conexion = new mysqli('quierocasaya.com.mx','readreferencia', 'lectura1123','DB_referencias',3306);
		if (mysqli_connect_errno()) {
			printf("La conexión con el servidor de base de datos falló: %s\n", mysqli_connect_error());
			exit();
		}
		
		$resultado = $conexion->query($consulta);
		if($resultado->num_rows > 0 ){
							
			date_default_timezone_set('America/Mexico_City');

			if (PHP_SAPI == 'cli')
				die('Este archivo solo se puede ver desde un navegador web');
			
			require_once 'excel/PHPExcel/PHPExcel.php';

			// Se crea el objeto PHPExcel
			$objPHPExcel = new PHPExcel();

			// Se asignan las propiedades del libro
			$objPHPExcel->getProperties()->setCreator("Pke") //Autor
								 ->setLastModifiedBy("Pke") //Ultimo usuario que lo modificó
								 ->setTitle("Reporte referencias de pago")
								 ->setSubject("Reporte")
								 ->setDescription("Reporte")
								 ->setKeywords("")
								 ->setCategory("Reporte");

			$tituloReporte = "Referencias de pago";
			$titulosColumnas = array('ID', 'Referencia numérica CP', 'Número cliente', 'Nombre cliente', 'Clave primaria', 'Email',  'Nombre asesor', 'Número aprobación', 'Monto', 'Archivo PDF', 'Fecha');
							
			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)					
						->setCellValue('A3',  $titulosColumnas[0])
						->setCellValue('B3',  $titulosColumnas[1])
						->setCellValue('C3',  $titulosColumnas[2])
						->setCellValue('D3',  $titulosColumnas[3])
						->setCellValue('E3',  $titulosColumnas[4])
						->setCellValue('F3',  $titulosColumnas[5])
						->setCellValue('G3',  $titulosColumnas[6])
						->setCellValue('H3',  $titulosColumnas[7])
						->setCellValue('I3',  $titulosColumnas[8])
						->setCellValue('J3',  $titulosColumnas[9])
						->setCellValue('K3',  $titulosColumnas[10]);
			
			//Se agregan los datos
			$i = 4;
			while ($fila = $resultado->fetch_array()) {
				$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  $fila[0])
						->setCellValue('B'.$i,  $fila[1])
						->setCellValue('C'.$i,  $fila[2])
						->setCellValue('D'.$i,  utf8_encode($fila[3]))
						->setCellValue('E'.$i,  $fila[4])
						->setCellValue('F'.$i,  $fila[5])
						->setCellValue('G'.$i,  utf8_encode($fila[6]))
						->setCellValue('H'.$i,  $fila[7])
						->setCellValue('I'.$i,  $fila[8])
						->setCellValue('J'.$i,  $fila[9])
						->setCellValue('K'.$i,  $fila[10]);
						$i++;
			}
			
			$estiloTituloReporte = array(
				'font' => array(
					'name'      => 'Calibri',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>12,
						'color'     => array(
							'rgb' => 'FFFFFF'
						)
				),
				'fill' => array(
					'type'	=> PHPExcel_Style_Fill::FILL_SOLID,
					'color'	=> array('argb' => 'FF225492')
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_NONE                    
					)
				), 
				'alignment' =>  array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				)
			);
			 
			$objPHPExcel->getActiveSheet()->getStyle('A3:K3')->applyFromArray($estiloTituloReporte);		
					
			for($i = 'A'; $i <= 'K'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Reporte');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			// Inmovilizar paneles 
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReporteReferenciasPago.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}
		else{
			print_r('No hay resultados para mostrar');
		}
	}
?>