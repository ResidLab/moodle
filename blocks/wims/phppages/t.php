<?php

		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		$url = "http://200.129.43.165/serviceWims/glype/upload/browse.php?u=http%3A%2F%2F200.129.43.165%2Fwims%2Fwims.cgi%3Fsession%3D3H244DA589.1%26%2Blang%3Den%26%2Bmodule%3Dadm%252Fclass%252Fsheet%26%2Bsheet%3D1";
		#$url = "http://192.168.250.241/serviceWims/glype/upload/browse.php?u=http%3A%2F%2F192.168.250.241%2Fwims%2Fwims.cgi%3Fsession%3D3H244DA589.1%26%2Blang%3Den%26%2Bmodule%3Dadm%252Fclass%252Fsheet%26%2Bsheet%3D1";
		$curl_connection =  curl_init($url);
		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_connection, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

		$result = curl_exec($curl_connection);
		curl_close($curl_connection);
		
		$data_begin = '<th>Comments</th>';
		$posicao_encontrada = strpos($result, $data_begin);
		$data = substr($result, $posicao_encontrada);

		$data_end = '<!-- begin of tail.phtml -->';
		$posicao = strpos($data, $data_end );
		$data = substr($data, 0 ,$posicao);

		$linha = explode("<a href=", $data);
		$num_linhas = count($linha);

		$urlproxy = 'localhost/';

		$h=0;
		for($i=1;$i<($num_linhas);$i++){
			$linha_selected = explode(">", $linha[$i]);
			//print_r($linha_selected);
			$linhatmptitulo = substr($linha_selected[1], 0, -3);
			$linhatr[$h]["titulo"] = $linhatmptitulo;
			$h++;

		}
		
		foreach($linhatr as $questao){
			echo $questao['titulo'].'<br>';
		}

		//print_r($linhatr);
		exit;
		return $linhatr;


?>
