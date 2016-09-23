<?php
global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");
include_once('ClienteWim.php');

$id_curso = $_GET['course'];
$id_assignment = $_GET['assignment'];
$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;
$id_sheet = $_GET['sheet'];


if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (empty($referencia)) {
	error(' Invalid Access! ');
}
else {
	$navlinks[0] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);

	print_header_simple($COURSE->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';

	$client = new ClienteWim();
 	/*$sql = "SELECT id_user FROM {$CFG->prefix}users_wims WHERE id_curso = {$id_curso} LIMIT 1";
	$linhasheets = get_records_sql($sql);
	$id_user_aluno = current($linhasheets)->id_user;*/

	$resMessage = $client->recuperaQuestoesSheet($id_curso,$id_assignment);
	if ($resMessage){
		
     		$questoes = new SimpleXMLElement($resMessage->str);
		echo '<fieldset><legend>Quest&otilde;es</legend>';
		echo '<form action="srd_save_categorias.php" method="post">';
		echo '<input type="hidden" name="folha" value="'.$id_sheet.'">';
		echo '<input type="hidden" name="cursoMoodle" value="'.$id_curso.'">';
		echo '<input type="hidden" name="salaWims" value="'.$referencia.'">';
		echo '<center><table style="width:80%;align:center;">
		<tr style="text-align:center;">
		  <th>Quest&atilde;o</th>
		  <th>T&iacute;tulo</th> 
		  <th>N&iacute;vel</th>
		  <th>Categoria</th>
		</tr>';
	
		global $config;
		$del = "/";
		require_once ('nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
		$wsdl = $config['wsdl_server'];
	 	$cliente = new nusoap_client($wsdl, true);
		$err = $cliente -> getError();
		if ($err) {	echo "Erro no construtor<pre>" . $err . "</pre>";}
		$str_cat = $cliente -> call('Categorias','');
		$str_cat = $str_cat['CategoriasResult']; 
		//print_r($str_cat);

		//$str_cat = '[{"Id":"1","Nome":"Representaçãoo Gráfica"},{"Id":"2","Nome":"Função Inversa"},{"Id":"3","Nome":"Função Quadrática"},{"Id":"4","Nome":"Continuidade de uma Função em um Ponto"},{"Id":"5","Nome":"Limites Infinitos"},{"Id":"6","Nome":"Limites no Infinito"},{"Id":"7","Nome":"Assintotas Horizontais/Assíntotas Verticais"},{"Id":"8","Nome":"Limite de Função Racional"},{"Id":"9","Nome":"Limite de Função Algébrica"}]';
		$categorias = json_decode(utf8_encode($str_cat),true);
		//print_r($categorias);
		$i=1;
		
		//$data = json_decode(@file_get_contents('srd_categ/'.$id_curso.'_'.$referencia),true);
		
		$sqldata = "SELECT * FROM {$CFG->prefix}wim_sheet_categoria WHERE id_curso = {$id_curso} AND id_sheet = {$id_sheet}";
		$dados = get_records_sql($sqldata);
if(!empty($dados)){
		foreach ($dados as $dado){
			//print_object($dado);
			$data['nivel'][$dado->id_questao] = $dado->id_nivel;
			$data['categoria'][$dado->id_questao] = $dado->id_categoria;
		}
}
		//print_r($data['nivel']);
		//$data = //json_decode(@file_get_contents('srd_categ/'.$id_curso.'_'.$referencia),true);
		$i = 1;
		foreach ($questoes as $questao){
		if(!empty($dados)){
			$select_nivel = '<select name="nivel['.$i.']">';
  			if($data['nivel'][$i] == 0) $select_nivel .= '<option value="0" selected>F&aacute;cil</option>'; else $select_nivel .= '<option value="0">F&aacute;cil</option>';
			if($data['nivel'][$i] == 1) $select_nivel .= '<option value="1" selected>M&eacute;dio</option>'; else $select_nivel .= '<option value="1">M&eacute;dio</option>';
			if($data['nivel'][$i] == 2) $select_nivel .= '<option value="2" selected>Dif&iacute;cil</option>'; else $select_nivel .= '<option value="2">Dif&iacute;cil</option>';
			$select_nivel .='</select>';		
		}else {

			$select_nivel = '<select name="nivel['.$i.']">
  				<option value="0">F&aacute;cil</option>
				<option value="1">M&eacute;dio</option>
				<option value="2">Dif&iacute;cil</option>
			</select>';
  		}

		$select_categorias = '<select name="categoria['.$i.']">';
		if(!empty($dados)){	
			//if($data['categoria'][$i] == 0) $select_categorias .= '<option value="0" selected>Selecionar</option>'; else $select_categorias .= '<option value="0">Selecionar</option>';
			foreach($categorias as $categoria){
				if($data['categoria'][$i] == $categoria['Id']) 
				$select_categorias .= '<option value="'.$categoria['Id'].'" selected>' .$categoria['Nome']. '</option>';
				else
				$select_categorias .= '<option value="'.$categoria['Id'].'">' .$categoria['Nome']. '</option>';	
			}
		}else{
			foreach($categorias as $categoria){
				$select_categorias .= '<option value="'.$categoria['Id'].'">' .$categoria['Nome']. '</option>';
			}
		
		}
		$select_categorias .= '</select>';

			echo '<tr style="text-align:center;">';
			echo '<td>'.$i++.'</td>';
			echo '<td>'.$questao.'</td>';
			echo '<td>'.$select_nivel.'</td>';
			echo '<td>'.$select_categorias.'</td>';
			echo '</tr>';
			
		}
		$i++;
		echo '</table><br/><input type="submit" value="Salvar"></form></fieldset>';
	}
	else{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
	}

}
print_footer($COURSE);

?>