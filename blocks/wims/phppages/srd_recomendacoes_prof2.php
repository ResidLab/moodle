<script src='../js/jquery3.js' type="text/javascript"></script>
<script type="text/javascript">
    	$(function(){
    		$('legend').parent().find('.content').hide();
    		$('legend').click(function(){
    			$(this).parent().find('.content').slideToggle();
    		});
    	});
</script>


<?php
global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");

$id_curso = $_GET['course'];
$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;
$context = get_context_instance(CONTEXT_COURSE, $id_curso);

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (empty($referencia)) {
	error(' Invalid Access! ');
}
else {
	$navlinks[0] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation = build_navigation($navlinks);

	print_header_simple($COURSE->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';
	
	$recomendacoes = get_records('wim_recomendacoes', 'id_curso', $id_curso);
	if($recomendacoes != null){
	foreach($recomendacoes as $recomendacao){
		//dbg($recomendacao, 'html');
		$id_user = $recomendacao->id_user;
		$id_sheet = $recomendacao->id_sheet;
		$tipo_intervencao = $recomendacao->tipo_intervencao;			
		$sqlusers = "SELECT * FROM {$CFG->prefix}user u WHERE u.id = {$id_user}";
		$user = get_records_sql($sqlusers);
		$userid = $recomendacao->id_user;
        $userfirstname = current($user)->firstname;
        $userlastname = current($user)->lastname;

		$dados2[$id_sheet][$id_user] = $id_user;
		$dados[$tipo_intervencao][$id_sheet][$id_user] = ucwords(strtolower($userfirstname)) .' '.ucwords(strtolower($userlastname)) ;
	}
	ksort($dados);
	ksort($dados2);
	
	echo '<center><table style=" text-align: center; width: 50%;">';
	echo '<th>Folha</th>';
	echo '<th></th>';
	foreach($dados2 as $sheet => $d){
		echo '<tr>';
		echo '<td>Folha: '.$sheet.'</td>';
		echo '<td><a target="_blank" href="'.$CFG->wwwroot.'/blocks/wim/phppages/srd_estatisticas.php?sheet='.$sheet.'&ref='.$referencia.'&course='.$id_curso.'">Acessar Gráfico</a></td>';
		echo '</tr>';
	}
	echo '</table></center><br/>';
	
	foreach($dados as $tipo_intervencao => $dado){
		ksort($dado);
		echo '<center><fieldset style=" text-align: center; width: 50%;">';
		echo '<legend><a href="#">Intervenção: '.$tipo_intervencao.'</a></legend>';
		
		foreach($dado as $sheet => $alunos){		
			echo '<fieldset><legend><a href="#"> Lista: '.$sheet.'</a></legend>';
			echo '<div class="content">';
			echo '<table>';
			foreach($alunos as $id_aluno => $nome_aluno){	
				echo '<tr>';
				//echo $nome_aluno.'<a href="srd_ver_recomendacao_prof.php?course='.$id_curso.'&id_aluno='.$id_aluno.'">Acessar</a><br>';
				echo '<td style="width: 250px;">'.$nome_aluno.'</td>';
				echo '<td>'.'<a href="srd_ver_recomendacao_prof.php?course='.$id_curso.'&id_aluno='.$id_aluno.'">Visualizar Intervenções</a><br>'.'</td>';
				echo '</tr>';
			}
			echo '</table>';
			echo '</div>';
			echo '</fieldset>';
			echo '</br>';
		}
		echo '</fieldset></center>';
		echo '</br>';
	}
	
	foreach($dados2 as $sheet => $d){
		echo '<center><fieldset style=" text-align: left; width: 50%;"><legend><a href="#"> Alunos com a lista '.$sheet.' incompleta</a></legend>';
			echo '<div class="content">';
			$in_id = implode(',',$d);
			$sqlusers = "SELECT u.id, u.firstname, u.lastname
	 			FROM {$CFG->prefix}role_assignments r,{$CFG->prefix}user u
				WHERE r.contextid = $context->id
	 			AND u.id = r.userid
	 			AND r.roleid =5
	 			AND u.id NOT IN (".$in_id.") 
				ORDER BY u.id";
			$linhausers = get_records_sql($sqlusers);
			echo '<table>';
			foreach($linhausers as $users_nfl){	
				$userfirstname = $users_nfl->firstname;
				$userlastname = $users_nfl->lastname;
				$userid = $users_nfl->id;

				/*echo $sqlrec = "SELECT tipo_intervencao
	 			FROM {$CFG->prefix}wim_recomendacoes
				WHERE id_curso = $id_curso
	 			AND id_sheet = $sheet
	 			AND id_user = $userid";
				$linharec = get_records_sql($sqlrec);
				//$rec = get_records('wim_recomendacoes', array('id_curso' => $id_curso, 'id_sheet' => $sheet, 'id_user'=> $userid));
				if($USER->id == 65)
				print_object($linharec);*/
				
				echo '<tr>';
				echo '<td style="width: 250px;">'.ucwords(strtolower($userfirstname)) .' '.ucwords(strtolower($userlastname)).'</td>'; ?> 
					<td><form style="display: inline;" onclick="this.target='message<?echo $userid?>'" action="../message/discussion.php" method="get"><input type="hidden" name="id" value="<?echo $userid?>" /><input style="display: inline;" type="submit" value="Enviar mensagem" onclick="return openpopup('/message/discussion.php?id=<?echo $userid?>', 'message_<?echo $userid?>', 'menubar=0,location=0,scrollbars,status,resizable,width=400,height=500', 0);" /></form></td>
				<?
				echo '</tr>';
			}
			echo '</table>';
			echo '</div>';
		echo '</fieldset></center>';
		echo '</br>';
		
	}	
	}	
	//dbg($dados, 'html');
}
print_footer($COURSE);
?>