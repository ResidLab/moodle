<?php


//metodo responsável pelo acesso simples da pagina wims
function chamaCurl($url){
	$curl_connection =  curl_init($url);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);
	return $result;
}

//download de arquivos
function get_file1($file, $local_path, $newfilename)
{
	// $err_msg = '';
	//echo "<br>Attempting message download for $file<br>";
	$out = fopen($newfilename, 'wb');
	if ($out == FALSE){
		print "File not opened<br>";
		exit;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_USERAGENT,
      "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_FILE, $out);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $file);

	$r = curl_exec($ch);
	//echo $r;
	// echo "<br>Error is : ".curl_error ( $ch);
	//show information regarding the request
	//print_r(curl_getinfo($ch));
	// echo 'ERROR::'. curl_errno($ch) . '-' .curl_error($ch);

	curl_close($ch);
	//fclose($handle);

}//end function

function cadastroAlunoUnico($url, $referenciaturma, $senhaprof, $senha, $login, $nome, $sobrenome){
	//chama metodo de login do supervisor/professor da turma WIMS
	
	$result = loginProfWIMS($url,$referenciaturma,$senhaprof);

	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '"';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);

	//http://10.1.106/wims/wims.cgi?session=CBD5268B27.2&+lang=en&+module=adm%2Fclass%2Fuserlist

	$result = chamaCurl("$url/wims.cgi?session=$session.2&+lang=en&+module=adm%2Fclass%2Fuserlist");

	$result= chamaCurl("$url/wims.cgi?session=$session.3&+lang=en&+module=adm%2Fclass%2Freguser&+step=1");


	
	//TERCEIRA ENTRADA NA P�GINA, COM SUBMISS�O DE FORMUL�RIO
	$post_data['session'] = $session.'.4';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/reguser';
	$post_data['step'] = '2';
	$post_data['lastn'] =  utf8_decode(str_replace(" ", "", $sobrenome));
	$post_data['firstn'] = utf8_decode(str_replace(" ", "", $nome));
	$post_data['login'] = $login;
	$post_data['pass'] = $senha;

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . $value;
	}

	$post_string = implode ('&', $post_items);

	
	
	$curl_connection = curl_init($url.'/wims.cgi');

	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt($curl_connection, CURLOPT_HTTPHEADER, "Content-type: text/html; charset=UTF-8"); 

	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	
	
	
	//4 ENTRADA NA P�GINA, COM SUBMISS�O DE FORMUL�RIO
	$post_data2['session'] = $session.'.5';
	$post_data2['lang'] = 'en';
	$post_data2['cmd'] = 'reply';
	$post_data2['module'] = 'adm/class/reguser';
	$post_data2['step'] = '3';
	$post_data2['pass2'] = $senha;

	foreach ( $post_data2 as $key => $value) {
		$post_items2[] = $key . '=' . $value;
	}

	$post_string2 = implode ('&', $post_items2);

	$curl_connection = curl_init($url.'/wims.cgi');

	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string2);
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);
	
	$messageOk = 'is added to the class.';
	$posicao_encontrada = strpos($result, $messageOk);
	
	if ($posicao_encontrada == NULL)
	{
		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<cadastroSucesso>false</cadastroSucesso>
		
XML;
		$resPayload .= <<<XML
      </ns1:result>
XML;
	}else{
		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<cadastroSucesso>true</cadastroSucesso>
		
XML;
		$resPayload .= <<<XML
      </ns1:result>
XML;
	}
	
	$returnMessage = new WSMessage($resPayload);
	return $resPayload;
}

//Fun��o de login Aluno;
//login aluno, retorna um html de uma sessao de entrada na turma
function loginAlunoWIMS($url, $referenciaturma, $login ,$senha){
	//1� acesso; capturar sessao corrente para montar formul�rio POST de login.
	$result = chamaCurl($url);
	
	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '&';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);

	//show information regarding the request
	//print_r(curl_getinfo($curl_connection));
	//echo 'ERROR::'. curl_errno($curl_connection) . '-' .curl_error($curl_connection);
		
	//SEGUNDA ENTRADA NA P�GINA
	$result = chamaCurl($url.'?session='.$session.'.3&+lang=en&+module=adm%2Fclass%2Fclasses&+type=authparticipant&+class='.$referenciaturma);
	
	//TERCEIRA ENTRADA NA P�GINA, COM SUBMISS�O DE FORMUL�RIO
	$post_data['session'] = $session.'.3';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/classes';
	$post_data['auth_user'] = $login;
	$post_data['auth_password'] = $senha;

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . $value;
	}

	$post_string = implode ('&', $post_items);

	$curl_connection = curl_init($url);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	return $result;
}


//Fun�ao de acessar questao WIMS;
function acessarQuestaoAluno($url, $referenciaturma, $login ,$senha,$sheet,$questao){

	$result = loginAlunoWIMS($url,$referenciaturma,$login, $senha);
	$session2_begin = 'session=';
	$posicao_encontrada = strpos($result, $session2_begin);
	$session2 = substr($result, $posicao_encontrada+8);

	$session2_end = '"';
	$posicao = strpos($session2, $session2_end);
	$session2 = substr($session2, 0 ,$posicao-2);

	$url2 = $url.'wims.cgi';
	$curl_connection =  curl_init($url2.'?session='.$session2.'.2&+lang=en&+module=H5%2Fanalysis%2Fgraphfunc.en&+cmd=new&+worksheet='.$sheet.'&+listype='.$questao.'&+repeat=1');
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);
	//se for para retornar direto para a pagina do WIMS
	//return $result;
	$urldaquestao = "$url2.'?session='.$session2.'.2&+lang=en&+module=H5%2Fanalysis%2Fgraphfunc.en&+cmd=new&+worksheet='.$sheet.'&+listype='.$questao.'&+repeat=1'";
	return $result;
}


//Função de encaminhar aluno para uma folha de exercicios especifica RETORNA A URL AUTENTICADA DE UM SHEET ESPEFICIO, NÃO RETORNA O HTML
function acessarSheetAluno($url, $referenciaturma, $login ,$senha,$sheet){

	$result = loginAlunoWIMS($url,$referenciaturma,$login, $senha);
	$session2_begin = 'session=';
	$posicao_encontrada = strpos($result, $session2_begin);
	$session2 = substr($result, $posicao_encontrada+8);

	$session2_end = '"';
	$posicao = strpos($session2, $session2_end);
	$session2 = substr($session2, 0 ,$posicao-2);

	global $urlproxy;

	$url2 = $url.'wims.cgi';

	$urldaquestao = $urlproxy.urlencode("$url2?session=$session2.1&+lang=en&+module=adm%2Fsheet&+sh=$sheet");
	return $urldaquestao;
}


function acessarSheetAlunoLinkNomeSheet($url, $referenciaturma, $login ,$senha,$sheet){

	$result = loginAlunoWIMS($url,$referenciaturma,$login, $senha);
	$session2_begin = 'session=';
	$posicao_encontrada = strpos($result, $session2_begin);
	$session2 = substr($result, $posicao_encontrada+8);

	$session2_end = '"';
	$posicao = strpos($session2, $session2_end);
	$session2 = substr($session2, 0 ,$posicao-2);

	global $urlproxy;

	$url2 = $url.'wims.cgi';

	//$urldaquestao = $urlproxy."$url2?session=$session2.1&+lang=en&+module=adm%2Fsheet&+sh=$sheet";

	$arr_exercicios = acessarLinkSheet($url,$session2,$sheet);
	
	//ACESSO A UM LINK COM UMA SESSAO EM QUE A QUESTAO NAO SERÁ CONTABILIZADA	
	$result = loginAlunoWIMS($url,$referenciaturma,$login, $senha);
	$session2_begin = 'session=';
	$posicao_encontrada = strpos($result, $session2_begin);
	$session2 = substr($result, $posicao_encontrada+8);

	$session2_end = '"';
	$posicao = strpos($session2, $session2_end);
	$session2 = substr($session2, 0 ,$posicao-2);

	$url2 = $url.'wims.cgi';

	$urlSheetNoScore = $url."wims.cgi?session=$session2.1&+lang=en&+module=adm%2Fsheet&+sh=$sheet";
	
	$result = chamaCurl($urlSheetNoScore);

	$urlSheetNoScore = $url."wims.cgi?session=$session2.2&+lang=en&+module=adm%2Fsheet&+cmd=resume&+job=suspend";
	
	$result = chamaCurl($urlSheetNoScore);

	$arr_exerciciosNoScore = acessarLinkSheet($url,$session2,$sheet);
	
	
	
	
	
	
	$resPayload = <<<XML
     <ns1:result xmlns:ns1="http://php.axis2.org/samples">
XML;

	for($i=0;$i<count($arr_exercicios);$i++) {
			
		$titulo = $arr_exercicios[$i]["titulo"] ;

		$titulo = utf8_encode($titulo);

		$link = $arr_exercicios[$i]["link"] ;
		
		$linkNoScore = $arr_exerciciosNoScore [$i]["link"];

		$link = urlencode($link);
		
		$linkNoScore = urlencode($linkNoScore);
		$resPayload .= <<<XML
			
<sheet$i>
      <titulo>$titulo</titulo>
      <linkscore>$link</linkscore>
      <linknoscore>$linkNoScore</linknoscore>
</sheet$i>

XML;
			
	}
	$resPayload .= <<<XML
     </ns1:result>
XML;

	$returnMessage = new WSMessage($resPayload);

	return $returnMessage;
}


function acessarLinkSheet($url,$session,$sheet){
	//http://127.0.0.1/wims/wims.cgi?session=HW145581BF.1&+lang=en&+module=adm%2Fsheet&+sh=1
	$urlsheet = $url."wims.cgi?session=$session.1&+lang=en&+module=adm%2Fsheet&+sh=$sheet";
	
	$result = chamaCurl($urlsheet);
	
	$data_begin = '<ul class="wims_sheet_list">';
	$posicao_encontrada = strpos($result, $data_begin);
	$data = substr($result, $posicao_encontrada);

	$data_end = '</ul>';
	$posicao = strpos($data, $data_end );
	$data = substr($data, 0 ,$posicao);

	$linha = explode("<a href=", $data);
	$num_linhas = count($linha);

	global $urlproxy;

	$h=0;
	$tr= "selected";
	for($i=1;$i<($num_linhas);$i++){

		$linha_selected = explode(">", $linha[$i]);
		$linhatmplink = substr($linha_selected[0], 0, -4);
		$linhatmplink = substr($linhatmplink,1);

		$linhatmptitulo = substr($linha_selected[1], 0, -3);

		$linhatr[$h]["link"] = $urlproxy.urlencode($linhatmplink);
		$linhatr[$h]["titulo"] = $linhatmptitulo;
		$h++;

	}
	return $linhatr;

}



//Função de encaminhar PROFESSOR para uma folha de exercicios especifica RETORNA A URL AUTENTICADA DE UM SHEET ESPEFICIO, NÃO RETORNA O HTML
function acessarSheetProf($url, $referenciaturma, $senha,$sheet){

	$result = loginProfWIMS($url,$referenciaturma, $senha);

	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session2 = substr($result, $posicao_encontrada+8);

	$session_end = '"';
	$posicao = strpos($session2, $session_end);
	$session2 = substr($session2, 0 ,$posicao-2);

	global $urlproxy;

	$url2 = $url.'wims.cgi';
//http://10.1.1.100/wims//wims.cgi?session=F9DAACEC35.1&+lang=en&+module=adm%2Fclass%2Fsheet&+sheet=2
//http://10.1.1.100/wims//wims.cgi?session=5BD892EA06.1&+lang=en&+module=adm%2Fclass%2Fsheet&+sheet=1
	$urldaquestao = $urlproxy.urlencode("$url2?session=$session2.1&+lang=en&+module=adm%2Fclass%2Fsheet&+sheet=$sheet");
	return $urldaquestao;
}

//Fun��o de acessar questao via Proxy

//QUESTAO VIA PROXY
function acessarQuestaoViaProxy($url,$referenciaturma, $login ,$senha,$sheet,$questao){

	$acesso = acessarSheetAluno($url, $referenciaturma, $login ,$senha,$sheet,$questao);

	global $urlproxy;

	$curl_connection =  curl_init($urlproxy."".urlencode($acesso));
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	return $result;
}


//Fun��o de login Professor RETORNA O HTML
function loginProfWIMS($url, $referenciaturma, $senha){
	//PRIMEIRA ENTRADA NA P�GINA

	$curl_connection =  curl_init($url);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '&';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);

	//show information regarding the request
	//print_r(curl_getinfo($curl_connection));
	//echo 'ERROR::'. curl_errno($curl_connection) . '-' .curl_error($curl_connection);

	//SEGUNDA ENTRADA NA P�GINA
	$curl_connection =  curl_init($url.'/wims.cgi?session='.$session.'.2&+lang=en&+module=adm%2Fclass%2Fclasses&+type=authsupervisor&+class='.$referenciaturma);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	//TERCEIRA ENTRADA NA P�GINA, COM SUBMISS�O DE FORMUL�RIO
	$post_data['session'] = $session.'.3';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/classes';
	$post_data['auth_user'] = 'supervisor';
	$post_data['auth_password'] = $senha;

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . $value;
	}

	$post_string = implode ('&', $post_items);

	$curl_connection = curl_init($url.'/wims.cgi');

	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);
	
	return $result;
}


//Fun��o de login Professor RETORNA A URL COM UMA SESSAO VALIDA
function loginProfWIMSInterno($url, $referenciaturma, $senha){
	
	//PRIMEIRA ENTRADA NA P�GINA
	$curl_connection =  curl_init($url);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);
	
	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '&';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);
	//show information regarding the request
	//print_r(curl_getinfo($curl_connection));
	//echo 'ERROR::'. curl_errno($curl_connection) . '-' .curl_error($curl_connection);

	//SEGUNDA ENTRADA NA P�GINA
	$curl_connection =  curl_init($url.'/wims.cgi?session='.$session.'.2&+lang=en&+module=adm%2Fclass%2Fclasses&+type=authsupervisor&+class='.$referenciaturma);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	//TERCEIRA ENTRADA NA P�GINA, COM SUBMISS�O DE FORMUL�RIO
	$post_data['session'] = $session.'.3';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/classes';
	$post_data['auth_user'] = 'supervisor';
	$post_data['auth_password'] = $senha;

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . $value;
	}

	$post_string = implode ('&', $post_items);
	
	$curl_connection = curl_init($url.'/wims.cgi');

	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);


	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '"';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);

	$urlsessao = $url.'?lang=en&+session='.$session.'.1';

	return $urlsessao;
}


/*

//TESTE DE ARQUIVO PHP LOGIN.PHP
//4405758     4140134 5034366

$url = "http://192.168.0.197/wims/";
$referenciaturma = "8628844";
$senha = "senha";
$login = "daniel1'";
$sheet = "1";

//http://localhost/wims

$sitewims = acessarSheetAlunoLinkNomeSheet($url, $referenciaturma, $login ,$senha,$sheet);
//$sitewims = loginProfWIMS($url, $referenciaturma, $senha);
echo"<pre>";
print_r($sitewims);
echo"</pre>";
*/

?>
