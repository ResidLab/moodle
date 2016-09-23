<?php
//include_once('../../../config.php');

function array_meses($string){
	$array = array('Jan' => '01',
					'Feb' => '02',
					'Mar' => '03',
					'Apr' => '04',
					'May' => '05',
					'Jun' => '06',
					'Jul' => '07',
					'Aug' => '08',
					'Set' => '09',
					'Oct' => '10',
					'Nov' => '11',
					'Dec' => '12');
	return $array[$string];
}

function pegar_info_classe_wims($id_curso) {
	global $CFG, $DB;
	$query	= "SELECT * FROM {$CFG->prefix}config_wim WHERE id_curso=$id_curso";
	//$queryresult	= get_records_sql($query);
        $queryresult    = $DB->get_record_sql($query);
	return $queryresult;

}

function pegar_info_aluno($id_curso, $id_aluno){
	global $CFG;
	$query	= "SELECT login_wims,senha_wims FROM {$CFG->prefix}users_wims WHERE id_curso=$id_curso AND id_user=$id_aluno";
	$queryresult	= get_records_sql($query);
	return $queryresult;
}

function gera_senha($n) {
	$CaracteresAceitos = 'abcdxywzABCDZYWZ0123456789';
	$max = strlen($CaracteresAceitos)-1;
	$password = null;
	for($i=0; $i < $n; $i++) {
		$password .= $CaracteresAceitos{mt_rand(0, $max)};
	}
	return $password;
}


function listar_usuarios_curso($context, $param) {
	global $CFG;
	/*$sqlusers = "SELECT $param
	 FROM {$CFG->prefix}user u, {$CFG->prefix}course_display c
	 WHERE c.course = $id_curso
	 AND c.userid = u.id ORDER BY u.id ASC";*/

	$sqlusers = "SELECT $param
	 			FROM {$CFG->prefix}role_assignments r,{$CFG->prefix}user u
				WHERE r.contextid = $context
	 			AND u.id = r.userid
	 			AND r.roleid =5
	 			ORDER BY u.id";

	$linhausers = get_records_sql($sqlusers);
	return $linhausers;
}

function listar_usuarios_cadastrados_wims($id_curso) {
	global $CFG;
	$sqluserswims = "SELECT id_user
				FROM {$CFG->prefix}users_wims
				WHERE id_curso = $id_curso
				ORDER BY id_user ASC";

	$linhausers = get_records_sql($sqluserswims);

	return $linhausers;
}

function objectToArray ($object) {
	$arr = array();
	foreach ($object as $obj){
		$arr[] = get_object_vars($obj);
	}
	return $arr;
}

function recursiveArraySearch($needle,$haystack, $index = null)
{
	$aIt     = new RecursiveArrayIterator($haystack);
	$it    = new RecursiveIteratorIterator($aIt);

	while($it->valid())
	{
		if (((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle)) {
			return $aIt->key();
		}
			
		$it->next();
	}

	return false;
}

//adiciona na tabela de modulos do curso course_modules e na de sess�o do curso module_sections
function add_mod_course( $id_curso, $id_module, $id_module_instance, $section = 1 ){
	global $CFG;

	$novo = new Object();
	$novo->course = $id_curso;
	$novo->module = $id_module;

	$novo->instance  = $id_module_instance;
	//echo '$novo->instance:'. $novo->instance;
	//echo 'section: '.$section.'<br/>';
	$sqlsection = "SELECT id FROM {$CFG->prefix}course_sections WHERE section=$section-1 AND course=$id_curso";
	//echo $sqlsequencia.'</br>';
	$linhasection = get_records_sql($sqlsection);
	$idsection = current($linhasection)->id;

	$novo->section = $idsection;
	$novo->visible  = 1;
	$novo->visibleold = 1;

	$novo->added = time();
	$novo->id = insert_record("course_modules", $novo);

	//decrementa-se a variavel section pois na tabela course_sections ela come�a por 0 onde 0 � a primeira semana
	$sqlsequencia = "SELECT * FROM {$CFG->prefix}course_sections WHERE section=$section-1 AND course=$id_curso";
	//echo $sqlsequencia.'</br>';
	$linhasequencia = get_records_sql($sqlsequencia);
	$sequencia = current($linhasequencia)->sequence;
	$id_sequencia = current($linhasequencia)->id;
	//echo $id_sequencia.'</br>';
	//echo $sequencia.'</br>';
	$sequencia = $sequencia.','.$novo->id;
	//echo $sequencia.'</br>';

	$novaentrada = new object();
	$novaentrada->id = $id_sequencia;
	$novaentrada->sequence = $sequencia;
	update_record('course_sections', $novaentrada);

}

function deletar_turma_moodle ($id_curso){
	if(delete_records('users_wims', 'id_curso' , $id_curso) && delete_records('config_wim', 'id_curso' , $id_curso) && delete_records('assignment_sheetwims', 'id_curso' , $id_curso))
		return true;
	else
		return false;
}



########### WIMS FUNCTIONS ###########

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

?>
