	<?php 
	
	
	
	$conteudo  = $referenciaturma ." / ". $senha ." / ". $senhaprof  ." / ". $login ." / ". $nome ." / ". $sobrenome;
	
	
	$arquivo = "/var/www/serviceWims/testerecebimentoASDASDv";


//TENTA ABRIR O ARQUIVO TXT
if (!$abrir = fopen($arquivo, "w+")) {
	echo "Erro abrindo arquivo ($arquivo)";
	exit;
}

//ESCREVE NO ARQUIVO TXT
if (!fwrite($abrir, $conteudo)) {
	print "Erro escrevendo no arquivo ($arquivo)";
	exit;
}

//echo "Arquivo gravado com Sucesso !!";

//FECHA O ARQUIVO
fclose($abrir);
	
	?>
	
	
	
	
	