<?php
#$url = 'http://200.129.43.182/wims/';
$url = 'http://192.168.250.241/wims/';
//$url = 'http://wims.auto.u-psud.fr/wims/' ;
//$urlproxy = "http://www.lte.deti.ufc.br/serviceWims/pw.php?=_&=";
#$urlproxy = "http://200.129.43.182/serviceWims/glype/upload/browse.php?u=";
$urlproxy = "http://192.168.250.240/serviceWims/glype/upload/browse.php?u=";


include ('turmaFunctions.php');
include ('login.php');


//Função que retorna uma url de um login feito no wims de um professor.

function cadastraAlunoUnicoInterface($inMessage){
	//$url, $referenciaturma, $senhaprof,$senha,$login, $nome, $sobrenome
	global $url;//servidor WIMS

	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referenciaturma = trim($simplexml->referencia);
	$senhaprof = trim($simplexml->senhaprof);
	$senha = trim($simplexml->senha);
	$login = trim($simplexml->login);
	$nome = trim($simplexml->nome);
	$sobrenome = trim($simplexml->sobrenome);
		
	
	$messagexml = cadastroAlunoUnico($url, $referenciaturma, $senhaprof, $senha, $login, $nome, $sobrenome);
	
	return $messagexml;
}



function loginProfessor($inMessage)
{
	global $url;//servidor WIMS

	
	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referenciaturma = trim($simplexml->referencia);
	$senha = trim($simplexml->senha);
	//ACESSAR FUNCAO loginProfWimsInterno, essa funcao retorna uma url autenticada
	$urlautenticada = loginProfWIMSInterno($url,$referenciaturma,$senha);
	$urlautenticada = urlencode($urlautenticada);
	//montar XML para gerar mensagem wso2
	$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

	$resPayload .= <<<XML
	$urlautenticada
XML;

	$resPayload .= <<<XML
      </ns1:result>
XML;
	$returnMessage = new WSMessage($resPayload);//menta mensagem WSO2 para retorno da mensagem ao cliente
	return $returnMessage;
}


//Função que recebe como parametros login, senha, referencia da turma, sheet escolhido, para o aluno poder fazer o acesso a questao.
function acessoSheetAlunoMoodleOLD($inMessage)
{
	global $url;//servidor WIMS

	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referenciaturma = trim($simplexml->referencia);
	$senha = trim($simplexml->senha_aluno);
	$login = trim($simplexml->login_aluno);
	$sheet = trim($simplexml->sheet);
	//ACESSAR FUNCAO acessarSheetAluno, essa funcao retorna uma url autenticada
	$urlautenticada = acessarSheetAluno($url, $referenciaturma, $login ,$senha,$sheet);
	$urlautenticada=urlencode($urlautenticada);
	//montar XML para gerar mensagem wso2
	$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

	$resPayload .= <<<XML
	$urlautenticada
XML;

	$resPayload .= <<<XML
      </ns1:result>
XML;

	$returnMessage = new WSMessage($resPayload);
	return $returnMessage;
}


//Função que recebe como parametros login, senha, referencia da turma, sheet escolhido, para o aluno poder fazer o acesso a questao.
function acessoSheetAlunoMoodle($inMessage)
{
	global $url;//servidor WIMS

	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referenciaturma = trim($simplexml->referencia);
	$senha = trim($simplexml->senha_aluno);
	$login = trim($simplexml->login_aluno);
	$sheet = trim($simplexml->sheet);
	//ACESSAR FUNCAO acessarSheetAluno, essa funcao retorna uma url autenticada
	$result = acessarSheetAlunoLinkNomeSheet($url, $referenciaturma, $login ,$senha,$sheet);
	

	return $result;
}




//Função para o professor acessar a parte de manutenção de um sheet
function acessoSheetProfMoodle($inMessage)
{
	global $url;//servidor WIMS

	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referenciaturma = trim($simplexml->referencia);
	$senha = trim($simplexml->senha_prof);
	$sheet = trim($simplexml->sheet);
	//ACESSAR FUNCAO acessarSheetAluno, essa funcao retorna uma url autenticada
	$urlautenticada = acessarSheetProf($url, $referenciaturma,$senha,$sheet);
	$urlautenticada=urlencode($urlautenticada);
	//montar XML para gerar mensagem wso2
	$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

	$resPayload .= <<<XML
	$urlautenticada
XML;

	$resPayload .= <<<XML
      </ns1:result>
XML;

	$returnMessage = new WSMessage($resPayload);
	return $returnMessage;
}


//Função que recebe uma lista de alunos e monta o arquivo .tsv para enviar ao servidor WIMS
function cadastroAlunos($inMessage){

	global $url;//servidor WIMS
	//$url, $referenciaturma, $senha, $arquivoLista
	$simplexml = new SimpleXMLElement($inMessage->str);
	$referenciaturma = trim($simplexml->referenciaturma);
	$senhaturma = trim($simplexml->senha);
	//coletar informações sobre usuarios que vieram na mensagens do cliente.

	$i=0;$j=0;$k=0;$h=0;$t=0;
	while($temp=$simplexml->aluno[$i]->nome){
		$nome[] = trim($temp);
		$i++;
	}

	while($temp=$simplexml->aluno[$j]->sobrenome){
		$sobrenome[] = trim($temp);
		$j++;
	}

	while($temp=$simplexml->aluno[$k]->email){
		$email[] = trim($temp);
		$k++;
	}

	while($temp=$simplexml->aluno[$h]->senha){
		$senha[] = trim($temp);
		$h++;
	}

	while($temp=$simplexml->aluno[$t]->login){
		$login[] = trim($temp);
		$t++;
	}

	//chama metodo de login do supervisor/professor da turma WIMS
	$result = loginProfWIMS($url,$referenciaturma,$senhaturma);
	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);
	$session_end = '"';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);

	//PREPARA O CONTEÚDO A SER GRAVADO no arquivo no qual será enviado ao WIMS
	$conteudoFixoListaAluno = 'login	lastname	firstname	email	password
""	""	""	""	""

';

	$count = count($nome);
	for ($i=0; $i<$count;$i++){
		$criptografada = "*".crypt($senha[$i],"Nv");
		$password = $criptografada;

		/*								 "daniel233"	"alencar233"	"daniel233"	"*Nv8kRNp87GZ5Q"       */
		$conteudoDinamicoListaAluno.= '"'.utf8_decode($login[$i]).'"	"'.utf8_decode($sobrenome[$i]).'"	"'.utf8_decode($login[$i]).'"	"'.utf8_decode($email[$i]).'"	"'.utf8_decode($password).'"
';
	}

	//PREPARA O CONTEÚDO A SER GRAVADO com a listagem de informações sobre alunos que serao cadastrados no WIMS
	$conteudo =$conteudoFixoListaAluno.$conteudoDinamicoListaAluno;
	$arquivo = "/var/www/serviceWims/$session.tsv";

	//TENTA ABRIR O ARQUIVO TXT
	if (!$abrir = fopen($arquivo, "w+")){
		echo "Erro abrindo arquivo ($arquivo)";
		exit;
	}

	//ESCREVE NO ARQUIVO TXT
	if (!fwrite($abrir, $conteudo)) {
		print "Erro escrevendo no arquivo ($arquivo)";
		exit;
	}
	//FECHA O ARQUIVO
	fclose($abrir);

	//acessar WIMS para entrada na lista de alunos.
	$url2 = $url.'/wims.cgi';
	$curl_connection =  curl_init($url2.'?session='.$session.'.1&+lang=en&+module=adm%2Fclass%2Fuserlist');
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
      "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	//acessar WIMS para entrada na pagina de cadastro de alunos via arquivo .tsv
	$url3 = $url;
	$curl_connection =  curl_init($url3.'?session='.$session.'.2&+lang=en&+module=adm%2Fclass%2Fuserlist&+cmd=reply&+job=csv');
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
      "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);
	//manipular formulario vindo do acesso anterios do WIMS para adquirir o numero do form-data referente a cada acesso
	$form_begin = 'form-data';
	$posicao_encontrada = strpos($result, $form_begin);
	$form = substr($result, $posicao_encontrada+9);

	$form_end = '"';
	$posicao = strpos($form, $form_end);
	$form = substr($form, 0 ,$posicao);

	//montagem da mensagem POST para UPLOAD PAGINA
	$postfields = array(
                        'session' => $session.'.3',
                        'lang' => 'en',
                        'cmd' => 'reply',
                        'module' => 'adm/class/userlist',
                        'job' => 'csv',
                        'csvtype' => 'upload',
            'wims_deposit' => "@".$arquivo
	);
	//envio do formulario POST para cadastro de alunos
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url.'/wims.cgi?form-data'.$form );
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_USERAGENT,
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfields );
	$result = curl_exec( $ch );
	curl_close( $ch );
	//deletar arquivo que foi gerado para envio do formulario de cadastro de lista de alunos

	@unlink($arquivo);


	//verificar se arquivo foi recebido e alunos foram gravados com sucesso.
	$session_begin = 'successfully';
	$posicao_encontrada = strpos($result, $session_begin);

	if(!$posicao_encontrada){
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
	return $returnMessage;
}


//Função de cadastro de Turma, apartir do cliente
function cadastrarTurma($inMessage){

	$simplexml = new SimpleXMLElement($inMessage->str);//acessar mensagem cliente

	global $url;//Servidor WIMS
	//informações sobre turma a ser cadastrada
	$instituicao = utf8_decode(trim($simplexml->instituicao));
	$nomeclasse = utf8_decode(trim($simplexml->nomeclasse));
	$level = utf8_decode(trim($simplexml->level));
	$nomeprofessor  = utf8_decode(trim($simplexml->nomeprofessor));
	$sobrenomeprofessor = utf8_decode(trim($simplexml->sobrenomeprofessor));
	$email = utf8_decode(trim($simplexml->email));
	$senhaprofessor = utf8_decode(trim($simplexml->senhaprofessor));
	$senhaclasse = utf8_decode(trim($simplexml->senhaclasse));
	$limite = utf8_decode(trim($simplexml->limite));
	$dataexpiracao = utf8_decode(trim($simplexml->dataexp));//20100518
	$exp_year = utf8_decode(substr($dataexpiracao, 0, 4));
	$exp_month = utf8_decode(substr($dataexpiracao, 4, 2));
	$exp_day = utf8_decode(substr($dataexpiracao, 6, 2));
	
	//acessar função de cadastro de turm em login.php
	$resPayload = cadastroTurmaWIMS($url,$instituicao,$nomeclasse,$level,$nomeprofessor,$sobrenomeprofessor,$email,$senhaprofessor,$senhaclasse,$limite,$exp_day,$exp_month,$exp_year);

	$returnMessage = new WSMessage($resPayload);
	return $returnMessage;
}


//Função de cadastro de Turma, verifica se pagina de retorno do WIMS é da turma cadastrada com sucesso.
function confirmaCadastroTurma($inMessage){
	global $url;

	$resPayload = confirmaCadastroTurmaWIMS($url,$inMessage);
	return $resPayload;
}


//Função de envio de notas de uma turma ao cliente Moodle
function acessoNota($inMessage){
	//parametro iniciais do método
	global $url;
	//$url, $referenciaturma, $senha, $arquivoLista
	$simplexml = new SimpleXMLElement($inMessage->str);
	//obter senha e referencia da turma, a partir de $inMessage
	$referencia = trim($simplexml->referenciaturma);
	$senha = trim($simplexml->senha);
	//Inicio de manipulação do html WIMS, login professor da turma referencia
	$result = loginProfWIMS($url,$referencia, $senha);
	//obter sessao do login do professor.
	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '"';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2); // numero da sessao do login professor
	//segunda chamada de navegação pela pagina Wims, listagem de alunos
	$result = chamaCurl($url.'wims.cgi?session='.$session.'.1&+lang=en&+module=adm%2Fclass%2Fuserlist');
	//terceira chamada, pagina de download e upload de informações sobre a turma
	$result = chamaCurl($url.'wims.cgi?session='.$session.'.2&+lang=en&+module=adm%2Fclass%2Fuserlist&+cmd=reply&+job=csv');

	//TERCEIRA ENTRADA NA P�GINA, COM SUBMISS�O DE FORMUL�RIO PARA TER LINK DE ARQUIVO PARA DOWNLOAD
	//montagem do formulario para submissao dos parametros via post
	$post_data['session'] = $session.'.3';
	$post_data['lang'] = 'en';
	$post_data['cmd'] = 'reply';
	$post_data['module'] = 'adm/class/userlist';
	$post_data['job'] = 'csv';
	$post_data['csvtype'] = 'download';
	$post_data['csvformat'] = 'tsv';
	$post_data['csvdownload'] = 'login,sheets';

	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . $value;
	}

	$post_string = implode ('&', $post_items);
	//envio do formulario ao wims
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

	//buscar link do arquivo baixa ser baixado com a informações requeridas sobre a turma
	$busca='Here is the file';
	$posicao_encontrada = strpos($result, $busca);
	$link_arquivo = substr($result, $posicao_encontrada+8);

	$busca = 'that you can download and open with your favorite spreadsheet program.';
	$posicao = strpos($link_arquivo, $busca);
	$link_arquivo = substr($link_arquivo, 0 ,$posicao-2);

	$link_arquivo = explode("href=", $link_arquivo);
	$link_arquivo = explode('>', $link_arquivo[1]);
	$link_arquivo = explode('"',$link_arquivo[0]);
	$link = $link_arquivo[1];//LINK DO ARQUIVO .TSV PARA DOWNLOAD

	//BAIXAR ARQUIVO COM LOGINS E NOTAS DOS SHEETS
	$file = $link;

	$local_path = "/var/www/serviceWims/";//localpath que será guardado o arquivo, mesmo canto do serviceWims

	$newfilename = "$session.tsv";//nome do arquivos que será gravado

	@get_file1($file, $local_path, $newfilename); // chamada do método de download do arquivo

	$arr = file($newfilename); //abrir o arquivo que foi baixado e montar um array
	unlink($newfilename); // deletar arquivo baixado

	for($i = 0; $i<count($arr);$i++){
		$dados[$i] = explode("	",$arr[$i]);
	}

	//MONTAR ARRAY COM IDs DE SHEETS
	for($i = 0; $i<count($arr[0]);$i++){
		$dados["sheet"] = explode("	",$arr[0]);
	}
	@array_shift($dados["sheet"]);
	@array_shift($dados);
	@array_shift($dados);
	@array_shift($dados);

	$sheet = $dados['sheet'];
	array_pop($dados);

	//MONTAR MENGASEM WSO2 PARA ENVIO
	$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

	for($h=0;$h<count($dados);$h++) {

		$login = $dados[$h][0];
		$nota = NULL;
		$resPayload .= <<<XML

  <alunos>
      <login>$login</login>
      <notas>
XML;


		for($i=0;$i<count($sheet);$i++){
			//.= $sheet[$i]."-->".$dados[$h][$i+1].",";
			$tmp = $sheet[$i];
			$nota = $dados[$h][$i+1];
			$resPayload .= <<<XML
      <$tmp>$nota</$tmp>
XML;
		}



		$resPayload .= <<<XML
		</notas>
	</alunos>
XML;
	}

	$resPayload .= <<<XML
      </ns1:result>
XML;
	$returnMessage = new WSMessage($resPayload);

/*
	$simplexml = new SimpleXMLElement($returnMessage->str);
	$i=0;$j=0;
	while($temp=$simplexml->aluno[$i]->login){
		$login2[] = trim($temp);
		$i++;
	}

	while($temp=$simplexml->aluno[$j]->nota){
		$nota2[] = trim($temp);
		$j++;
	}
	print_r( $nota2);
*/
	return $returnMessage;



}

//funçao para encontrar lista de informações sobre sheets ativos
//Função que retorna uma lista de com todas as folhas de exercícios de uma turma especifica
function retornaSheet($inMessage){

	global $url;//servidor WIMS

	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referencia = trim($simplexml->referencia);
	$senha = trim($simplexml->senha);

	$result = loginProfWIMS($url,$referencia, $senha);

	$session_begin = 'session=';
	$posicao_encontrada = strpos($result, $session_begin);
	$session = substr($result, $posicao_encontrada+8);

	$session_end = '"';
	$posicao = strpos($session, $session_end);
	$session = substr($session, 0 ,$posicao-2);


	$session_begin = 'Your class has no resources yet.';
	$posicao_encontrada = strpos($result, $session_begin);

	if($posicao_encontrada == NULL)
	{
		//PERGAR TABELA DE SHEET, NAO TRATA
		$sheet_begin = 'Status';
		$posicao_encontrada = strpos($result, $sheet_begin);
		$sheet = substr($result, $posicao_encontrada+8);

		$sheet_end = 'Add a';
		$posicao = strpos($sheet, $sheet_end );
		$sheet = substr($sheet, 0 ,$posicao-5);


		$arr = explode("\n", $sheet);
		$num_linhas = count($arr);


		$h=0;
		$tr= "<tr class";
		for($i=0;$i<($num_linhas);$i++){
			//encontra toda as linhas que tem, '<tr class'
			if(strpos($arr[$i], $tr))
			{
				$linhastr[$h] = $i;
				$h++;
			}
		}
		$num_linha_tr = count($linhastr);
		//transformar linhas do arquivo de texto da lista de questoes em array '$questao'.
		for($j=0;$j<($num_linha_tr);$j++)
		{
			if($j<($num_linha_tr-1))
			{
				$t=0;
				$iniciotr = $linhastr[$j];
				$fimtr = $linhastr[$j+1];
				while($iniciotr<$fimtr){
					$questao[$j][$t] = $arr[$iniciotr];
					$iniciotr++;
					$t++;
				}
			}else{
				$t=0;
				$iniciotr =$linhastr[$j];
				while($iniciotr<$num_linhas){
					$questao[$j][$t] = $arr[$iniciotr];
					$iniciotr++;
					$t++;
				}
			}
		}

		//tratar linhas dos arrays.
		$tamanhoLista = count($questao);

		for($i=0;$i<($tamanhoLista);$i++){
			$numLinhaQuestao = count($questao[$i]);
			for($j=0;$j<($numLinhaQuestao);$j++)
			{
				$nome = $questao[$i][0];
				$form_begin = '<td align=center>';
				$posicao_encontrada = strpos($nome, $form_begin);
				$form = substr($nome, $posicao_encontrada+17);
				$form_end = '</td>';
				$posicao = strpos($form, $form_end);
				$nome = substr($form, 0 ,$posicao);
				$lista[$i]["nome"] = $nome;

				$descricao = $questao[$i][2];
				$descricao = explode(">",$descricao);
				$descricao = $descricao[1];
				$descricao = substr($descricao,0,-3);
				$lista[$i]["descricao"]=$descricao;

				$ativo = $questao[$i][3];
				$form_under = 'Under preparation';
				$form_active = 'Active';
				$posicao_under = strpos($ativo, $form_under);
				$posicao_active = strpos($ativo, $form_active);

				if($posicao_under){
					$lista[$i]["ativo"] = 'Under preparation';
				}else if($posicao_active){
					$lista[$i]["ativo"] = 'Active';
					$nomesheet = explode(" ",$lista[$i]["nome"]);
					if($nomesheet[0] == "Sheet"){
						$lista[$i]["dataexp"] = acessarDataExpSheet($url,$session,$nomesheet[1]);
					}
					if($nomesheet[0] == "Exam"){
						$lista[$i]["dataexp"] = acessarDataExpExam($url,$session,$nomesheet[1]);
					}
				}else{
					$lista[$i]["ativo"] = '0';
				}

				$lista[$i]["nome"] = substr($nome,-1);
			}
		}

		$resPayload = <<<XML
     <ns1:result xmlns:ns1="http://php.axis2.org/samples">
XML;

		for($i=0;$i<count($lista);$i++) {
			if($lista[$i]["ativo"]==Active){
				$nome = $lista[$i]["nome"] ;
				$nome =	utf8_encode($nome);
				$descricao = $lista[$i]["descricao"] ;
				$descricao = utf8_encode($descricao);
				$id = $lista[$i]["ativo"] ;

				@$data = implode("/",$lista[$i]["dataexp"]);

				$resPayload .= <<<XML
			
<sheet>
      <id>$nome</id>
      <title>$descricao</title>
      <ativo>$id</ativo>
      <dataexp>$data</dataexp>
</sheet>

XML;
			}
		}
		$resPayload .= <<<XML
     </ns1:result>
XML;


		$returnMessage = new WSMessage($resPayload);

	}else{

		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
        <retornaSheet>false</retornaSheet>
XML;


		$resPayload .= <<<XML
      </ns1:result>
XML;
		$returnMessage = new WSMessage($resPayload);
	}

/*
	

		$conteudo  = $resPayload;
	
	
	$arquivo = "/var/www/serviceWims/testerecebimentoASDASDv";


	//TENTA ABRIR O ARQUIVO TXT
	if (!$abrir = fopen($arquivo, "w+")) {
		echo "Erro abrindo arquivo ($arquivo)";
		exit;
	}

	//ESCREVE NO ARQUIVO TXT
	if (!fwrite($abrir, $conteudo)) {
		print "Erro escrevendo no arquivo ($arquivo)";
	}
*/
	return $returnMessage;
}



function acessarDataExpSheet($url,$session,$sheet){

	$urlsheet = $url."wims.cgi?session=$session.2&+lang=en&+module=adm%2Fclass%2Fsheet&+sheet=$sheet";
	//http://10.1.1.100/wims/wims.cgi?session=1907868E47.2&+lang=en&+module=adm%2Fclass%2Fexam&+exam=1
	$curl_connection =  curl_init($urlsheet);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	 	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	$data_begin = '<select  name="expday">';
	$posicao_encontrada = strpos($result, $data_begin);
	$data = substr($result, $posicao_encontrada);

	$data_end = '</select>   </td>';
	$posicao = strpos($data, $data_end );
	$data = substr($data, 0 ,$posicao);

	$linha = explode("\n", $data);
	$num_linhas = count($linha);

	$h=0;
	$tr= "selected";
	for($i=0;$i<($num_linhas);$i++){
		//encontra toda as linhas que tem, '<tr class'
		if(strpos($linha[$i], $tr))
		{//>
			$linha_selected = explode(">", $linha[$i]);
			$linhastr[$h] = $linha_selected[1];
			$h++;
		}
	}

	return $linhastr;
}


function acessarDataExpExam($url,$session,$exam){

	$urlsheet = $url."wims.cgi?session=$session.2&+lang=en&+module=adm%2Fclass%2Fexam&+exam=$exam";
	//http://10.1.1.100/wims/wims.cgi?session=1907868E47.2&+lang=en&+module=adm%2Fclass%2Fexam&+exam=1
	$curl_connection =  curl_init($urlsheet);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT,
	 	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	$data_begin = '<select  name="expday">';
	$posicao_encontrada = strpos($result, $data_begin);
	$data = substr($result, $posicao_encontrada);

	$data_end = '</select>   </td>';
	$posicao = strpos($data, $data_end );
	$data = substr($data, 0 ,$posicao);

	$linha = explode("\n", $data);
	$num_linhas = count($linha);

	$h=0;
	$tr= "selected";
	for($i=0;$i<($num_linhas);$i++){
		//encontra toda as linhas que tem, '<tr class'
		if(strpos($linha[$i], $tr))
		{//>
			$linha_selected = explode(">", $linha[$i]);
			$linhastr[$h] = $linha_selected[1];
			$h++;
		}
	}

	return $linhastr;
}




function acessoQuestoesSheetProfMoodle($inMessage)
{
	global $url;//servidor WIMS
	file_put_contents('/var/www/serviceWims/sebug.txt', 'oi');exit
	$simplexml = new SimpleXMLElement($inMessage->str);//acessar informaçoes da mensagem do cliente
	$referenciaturma = trim($simplexml->referencia);
	$senha = trim($simplexml->senha_prof);
	$sheet = trim($simplexml->sheet);
	//ACESSAR FUNCAO acessarSheetAluno, essa funcao retorna uma url autenticada
	$urlautenticada = acessarSheetProf($url, $referenciaturma,$senha,$sheet);
	$urlautenticada=urlencode($urlautenticada);
	$curl_connection =  curl_init($urlautenticada);
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
		/*echo '<pre>';
		print_r($linhatr);
		echo '</pre>';
		return $linhatr;*/

		$resPayload = <<<XML
      <ns1:result xmlns:ns1="http://wso2.org/wsfphp/samples">
XML;

		$resPayload .= <<<XML
		<cadastroSucesso>true</cadastroSucesso>
		
XML;
		$resPayload .= <<<XML
      </ns1:result>
XML;


	$returnMessage = new WSMessage($resPayload);
	return $returnMessage;

}

//publicação dos serviços do ServiceWIMS
$pub_key = ws_get_cert_from_file("keys/alice_cert.cert");

$pvt_key = ws_get_key_from_file("keys/bob_key.pem");

$operations = array("cadastrarTurma"=>"cadastrarTurma",
            "confirmaCadastroTurma"=>"confirmaCadastroTurma",
            "cadastroAlunos"=>"cadastroAlunos",
            "loginProfessor"=>"loginProfessor",
            "acessoNota"=>"acessoNota",
            "retornaSheet"=>"retornaSheet",
	    "acessoSheetAlunoMoodle"=>"acessoSheetAlunoMoodle",
	    "acessoSheetProfMoodle"=>"acessoSheetProfMoodle",
	    "cadastraAlunoUnicoInterface"=>"cadastraAlunoUnicoInterface",
	"acessoQuestoesSheetProfMoodle" => "acessoQuestoesSheetProfMoodle");



$actions = array("http://wso2.org/wsfphp/samples/cadastrarTurma" => "cadastrarTurma",
         "http://wso2.org/wsfphp/samples/confirmaCadastroTurma" => "confirmaCadastroTurma",
         "http://wso2.org/wsfphp/samples/cadastroAlunos" => "cadastroAlunos",
         "http://wso2.org/wsfphp/samples/loginProfessor" => "loginProfessor",
         "http://wso2.org/wsfphp/samples/acessoNota" => "acessoNota",
         "http://wso2.org/wsfphp/samples/retornaSheet" => "retornaSheet",
	 "http://wso2.org/wsfphp/samples/acessoSheetAlunoMoodle" => "acessoSheetAlunoMoodle",
 	 "http://wso2.org/wsfphp/samples/acessoSheetProfMoodle" => "acessoSheetProfMoodle",
	 "http://wso2.org/wsfphp/samples/cadastraAlunoUnicoInterface" => "cadastraAlunoUnicoInterface",
          "http://wso2.org/wsfphp/samples/acessoQuestoesSheetProfMoodle" => "acessoQuestoesSheetProfMoodle");

$policy_xml = file_get_contents("keys/policy.xml");

$policy = new WSPolicy($policy_xml);

$sec_token = new WSSecurityToken(array("privateKey" => $pvt_key,
                                       "receiverCertificate" => $pub_key));

$svr = new WSService(array("operations" => $operations,
                           "actions" => $actions,
                           "policy" => $policy,
                           "securityToken" => $sec_token));

@$svr->reply();

?>
