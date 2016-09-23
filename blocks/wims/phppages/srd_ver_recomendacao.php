

<script src='../js/jquery3.js' type="text/javascript"></script>
<script src='../js/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
<script src='../js/jquery.rating3.js' type="text/javascript" language="javascript"></script>
<link href='../css/jquery.rating.css' type="text/css" rel="stylesheet"/>

<script type="text/javascript" language="javascript">
$(function(){ 
// $('#form1 :radio.star').rating(); 
});
</script>

<script type="text/javascript">
function formsubmit(id){
	jQuery(document).ready(function(){
		jQuery('#form'+id).submit(function(){
		var dados = jQuery( this ).serialize();
 
		jQuery.ajax({
			type: "POST",
			url: "srd_rate.php",
			data: dados,
			success: function( data ){
				alert( "Avaliação enviada com sucesso !" );
				}
			});
			return false;
		});
	});
}
</script>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#ajax_form').submit(function(){
			var dados = jQuery( this ).serialize();

			jQuery.ajax({
				type: "POST",
				url: "srd_salva_recomendacao_sugerida.php",
				data: dados,
				success: function( data )
				{
					alert(" Sua sugestão foi salva e será avaliada pelo seu professor. Obrigado!");
					$("#link").val(''); 
					
				}
			});

			return false;
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
$id_user = $USER->id;

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

		$sqlrecomendacoes = "SELECT * FROM {$CFG->prefix}wim_recomendacoes WHERE id_curso = {$id_curso} AND id_user = {$id_user}";
		$recomendacoes = get_records_sql($sqlrecomendacoes);
if(!empty($recomendacoes)){
		echo '<fieldset><legend>Recomenda&ccedil;&otilde;es</legend>
		            <!--<form action="#" method="post">-->
			<center><table style="width:90%;align:center;">
  			<tr style="text-align:left;">
      			  <th style="width:5%;">Lista</th> 
			      <!--<th style="width:15%;" >Tipo de Interven&ccedil;&atilde;o</th>-->
   			      <th>Categoria</th> 
			      <th>Recomenda&ccedil;&atilde;o</th> 
			      <th>Avalia&ccedil;&atilde;o Aluno</th> 
			      <th><center>Avalia&ccedil;&atilde;o</center></th> 
		    </tr>';

		foreach($recomendacoes as $recomendacao){ 	
$avaliacao = -1;	
			    echo '<tr style="text-align:left;">';
			    echo '<td style="text-align:center;">'.$recomendacao->id_sheet.'</td>';
			    //echo '<td style="text-align:center;">'.utf8_decode($recomendacao->tipo_intervencao).'</td>';
			    echo '<td style="text-align:center;">'.utf8_decode($recomendacao->categoria).'</td>';
  			    echo '<td> <a target="_blank"href="'.$recomendacao->link_recomendacao.'">'.$recomendacao->link_recomendacao.'</a></td>';

			    
			$sqlavals = "SELECT * FROM {$CFG->prefix}wim_avaliacao_recomendacao WHERE id_user = {$USER->id} AND id_recomendacao = {$recomendacao->id_recomendacao}";
			$avals = get_records_sql($sqlavals);
			if(!empty($avals)) $avaliacao = current($avals)->avaliacao;
			   $select_aluno = '<center><form id="form'.$recomendacao->id_recomendacao.'"><input type="hidden" name="id_user" value='.$id_user.'><input type="hidden" name="id_rec" value='.$recomendacao->id_recomendacao.'>';
				 for ($i=1;$i<=10;$i++){

				if( !empty($avaliacao) && $i == $avaliacao)  $select_aluno .= '<input class="star required {split:2}" type="radio" name="avaliacao" value=" '.$i.' " title=" '.$i.' " checked="checked"/>';
				else $select_aluno .= '<input class="star required {split:2}" type="radio" name="avaliacao" value=" '.$i.' " title=" '.$i.' "/>';

				 }
    			
				$select_aluno .=	'<input type="submit" onclick="formsubmit('.$recomendacao->id_recomendacao.');" value="Avaliar" /></form>';

			    echo '<td><center>';
				 if($recomendacao->avaliacao_aluno == -1) echo '-'; 
				 else echo $recomendacao->avaliacao_aluno;
			    echo '</center></td>';
			    echo '<td><center>';
			    echo $select_aluno;
			    echo '</center></td>';
			    echo '</tr>';
		}
		echo '</table><br/><!--<input type="submit" value="Salvar"></form>--></fieldset>';
	
	echo '<fieldset><legend>Envie sua recomendação</legend>';
	echo '<center><form method="post" action="" id="ajax_form">
		<input type="hidden" name="id_aluno" value="'.$id_user.'" />
		<input type="hidden" name="id_curso" value="'.$id_curso.'" />
		<label>Link: <input id="link" type="text" size=100 name="link" value="" /></label>
		<label><input type="submit" name="enviar" value="Enviar" /></label>
	</form></center>';
	echo '</fieldset>';



	
} else echo '<center>Você não possui recomendações !</center>';
}
print_footer($COURSE);
?>
