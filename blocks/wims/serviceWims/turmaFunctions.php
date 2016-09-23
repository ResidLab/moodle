<?php

function cadastroTurmaWIMS($url,$instituicao,$nomeclasse,$level,$nomeprofessor,$sobrenomeprofessor,$email,$senhaprofessor,$senhaclasse,$limite,$exp_day,$exp_month,$exp_year)
{

	$curl_connection =  curl_init($url.'/wims.cgi');
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);

	

	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '&';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);

	curl_close($curl_connection);
/*$test = $url."/ ".$instituicao."/ ".$nomeclasse."/ ".$level."/ ".$nomeprofessor."/ ".$sobrenomeprofessor."/ ".$email."/ ".$senhaprofessor."/ ".$senhaclasse."/ ".$limite."/ ".$exp_day."/ ".$exp_month."/ ".$exp_year;
	
	$fp = fopen("debug.txt", "w");
       fwrite($fp, $test);
       fclose($fp);
	
		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<turmaconfirmada>$session</turmaconfirmada>
		
XML;
		$resPayload .= <<<XML
      </ns1:result>
XML;
return $resPayload;
	*/
	
	$curl_connection =  curl_init($url.'/wims.cgi?session='.$session.'.2&+lang=en&+module=adm%2Fclass%2Fregclass');
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	//http://wims.matapp.unimib.it/wims/wims.cgi?session=X8E519F17E.4&+lang=en&+module=adm%2Fclass%2Fregclass&+cmd=reply&+step=0&+cltype=0

	$curl_connection = curl_init($url.'/wims.cgi?session='.$session.'.3&+lang=en&+module=adm%2Fclass%2Fregclass&+cmd=reply&+step=0&+cltype=0');

	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);


	//
	//http://wims.matapp.unimib.it/wims/wims.cgi?session=X8E519F17E.4&+lang=en&+module=adm%2Fclass%2Fregclass&+cmd=reply&+step=0&+cltype=0


	$post_data['session'] = $session.'.5';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/regclass';
	$post_data['step'] = '1';
	$post_data['institution'] = $instituicao;
	$post_data['description'] = $nomeclasse;
	$post_data['level'] = $level;
	$post_data['firstname'] = $nomeprofessor;
	$post_data['lastname'] = $sobrenomeprofessor;
	$post_data['email'] = $email;
	$post_data['passsup'] = $senhaprofessor;
	$post_data['password'] = $senhaclasse;
	$post_data['exp_day'] = $exp_day;
	$post_data['exp_month'] = $exp_month;
	$post_data['exp_year'] = $exp_year;
	$post_data['limit'] = $limite;
	$post_data['secure'] = "all";

	//http://wims.matapp.unimib.it/wims/wims.cgi?session=X8E519F17E.4&+lang=en&+module=adm%2Fclass%2Fregclass&+cmd=reply&+step=0&+cltype=0

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . urlencode($value);
	}

	$post_string = implode ('&', $post_items);
	//return urlencode("session=T7909CB6DE.5&lang=en&cmd=reply&module=adm/class/regclass&step=1&institution=UFC&description=cadastro turma&level=U1&firstname=daniel&lastname=tavares&email=danieldgt@gmail.com&passsup=senha&password=senha&exp_day=5&exp_month=8&exp_year=2011&limit=120&secure=all");

	$curl_connection =  curl_init($url.'/wims.cgi?'.$post_string);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);


	$post_data2['session'] = $session.'.6';
	$post_data2['lang'] = 'en';
	$post_data2['cmd'] = 'reply';
	$post_data2['module'] = 'adm/class/regclass';
	$post_data2['step'] = '2';

	$post_data2['passsup'] = $senhaprofessor;
	$post_data2['password'] = $senhaclasse;


	foreach ( $post_data2 as $key => $value) {
		$post_items2[] = $key . '=' . urlencode($value);
	}

	$post_string2 = implode ('&', $post_items2);

	$curl_connection =  curl_init($url.'/wims.cgi?'.$post_string2);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	$session_begin = 'Sorry';
	$posicao_encontrada = strpos($result, $session_begin);

	if($posicao_encontrada){
		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<turmacadastrada>false</turmacadastrada>
XML;


		$resPayload .= <<<XML
      </ns1:result>
XML;

	}else{

		/*<form action="http://wims.lyc-arsonval-brive.ac-limoges.fr/wims/wims.cgi" method="get" >
		 <input type=hidden name=session value="WDF88BBAB4.7">
		 <input type=hidden name=lang value="en">
		 <input type=hidden name=cmd value="reply">
		 <input type=hidden name=module value="adm/class/regclass">
		 <input type=hidden name=step value=3>

		 <center><label for="typecode">Code of the class</label> <input size=12 name=typecode id="typecode">
		 <p><input type=submit value="Continue"></center>
		 </form>*/

		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<turmacadastrada>true</turmacadastrada>
		<session>$session</session>
XML;
		$resPayload .= <<<XML
      </ns1:result>
XML;

	}
	return $resPayload;
}


function confirmaCadastroTurmaWIMS($url,$inMessage){
	$simplexml = new SimpleXMLElement($inMessage->str);
	$codigoacesso = trim($simplexml->codigoacesso);
	$session = trim($simplexml->session);

	$post_data['session'] = $session.'.7';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/regclass';
	$post_data['step'] = '3';
	$post_data['typecode'] = $codigoacesso;
	/*
	 <form action="http://wims.lyc-arsonval-brive.ac-limoges.fr/wims/wims.cgi" method="get" >
	 <input type=hidden name=session value="WDF88BBAB4.7">
	 <input type=hidden name=lang value="en">
	 <input type=hidden name=cmd value="reply">
	 <input type=hidden name=module value="adm/class/regclass">
	 <input type=hidden name=step value=3>

	 <center><label for="typecode">Code of the class</label> <input size=12 name=typecode id="typecode">
	 <p><input type=submit value="Continue"></center>
	 </form>
	 */
	//http://wims.matapp.unimib.it/wims/wims.cgi?session=X8E519F17E.4&+lang=en&+module=adm%2Fclass%2Fregclass&+cmd=reply&+step=0&+cltype=0

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . urlencode($value);
	}

	$post_string = implode ('&', $post_items);
	//return urlencode("session=T7909CB6DE.5&lang=en&cmd=reply&module=adm/class/regclass&step=1&institution=UFC&description=cadastro turma&level=U1&firstname=daniel&lastname=tavares&email=danieldgt@gmail.com&passsup=senha&password=senha&exp_day=5&exp_month=8&exp_year=2011&limit=120&secure=all");

	$curl_connection =  curl_init($url.'/wims.cgi?'.$post_string);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);



	$session_begin = "Congratulations, and enjoy!";
	$posicao_encontrada = strpos($result, $session_begin);

	if(!$posicao_encontrada){
		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<turmaconfirmada>false</turmaconfirmada>
XML;


		$resPayload .= <<<XML
      </ns1:result>
XML;

	}else{

		/*<form action="http://wims.lyc-arsonval-brive.ac-limoges.fr/wims/wims.cgi" method="get" >
		 <input type=hidden name=session value="WDF88BBAB4.7">
		 <input type=hidden name=lang value="en">
		 <input type=hidden name=cmd value="reply">
		 <input type=hidden name=module value="adm/class/regclass">
		 <input type=hidden name=step value=3>

		 <center><label for="typecode">Code of the class</label> <input size=12 name=typecode id="typecode">
		 <p><input type=submit value="Continue"></center>
		 </form>*/

		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<turmaconfirmada>true</turmaconfirmada>
		
XML;
		$resPayload .= <<<XML
      </ns1:result>
XML;

	}

	return $resPayload;
}

?>
