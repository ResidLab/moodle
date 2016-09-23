<?php

/**
 * Block wim class definition.
 *
 * This block can be added to a course page to control WIMS class
 * and worksheets from Moodle.
 *
 * @package    block_wims
 * @copyright  2016 ReSiD-UFC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//use block_wims\wims_client;

class block_wims extends block_list {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_wims');
        //$this->version = 2007101509;
    }

    /**
     * Used to generate the content for the block.
     * @return string
     */
    public function get_content() {

        /*global $CFG, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return $this->content;
        }

        $context = context_module::instance($COURSE->id);

        // Are you a teacher?
        if (has_capability('moodle/course:manageactivities', $context)) {
        // Yes, I am.
            $this->content = new stdClass;
            $this->content->items = array();
            $this->content->icons = array();
            $this->content->footer = '<a title="ReSiD-UFC" href="http://www.'
                                    .'deti.ufc.br" target="_blank">resid-ufc'
                                    .'</a>';

            $this->content->items[] = '<a title="Export Users" href=""><img src="'.$CFG->wwwroot.'/blocks/wims/icons/export_users.png"/></a>';
        } else {
        // No, I'm a student.
            $this->content = '';
        }

        return $this->content;*/
       // has_capability('mod/folder:managefiles', $context)
        //$currentcontext = $this->page->context

        //if (empty($currentcontext)) {
        //    $this->content = '';
        //    return $this->content;
        //}

		include_once("phppages/ClienteWim.php");
		//include_once('../../mod/assignment/lib.php');
		//include_once('../../config.php');
	  	    include_once("scripts/functions.php");
//        return $this->content;

		global $CFG, $USER, $SITE, $COURSE;

		$CFG->{'assignment_hide_wims'} = true;
		if ($this->content !== NULL) {
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';

		if (empty($this->instance)) {
			return $this->content = '';
		} //else if ($this->instance->pageid == SITEID) {
			// return $this->content = '';
		//}

		if (!empty($this->instance->pageid)) {
			$context = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
			if ($COURSE->id == $this->instance->pageid) {
				$course = $COURSE;
			} else {
				$course = get_record('course', 'id', $this->instance->pageid);
			}
		} else {
			$context = get_context_instance(CONTEXT_SYSTEM);
			$course = $SITE;
		}

		if (!has_capability('moodle/course:view', $context)) {  // Just return
			return $this->content;
		}

		if (empty($CFG->loginhttps)) {
			$securewwwroot = $CFG->wwwroot;
		} else {
			$securewwwroot = str_replace('http:','https:',$CFG->wwwroot);
		}

		$path = '#';

		//if( isadmin($USER->id) || isteacher($COURSE->id, $USER->id ) || !isstudent( $COURSE->id, $USER->id ) ) {if(isteacherinanycourse($USER->id)){

		$tb = '<ul class="list"><li class="r0"><table>';

		$info_turma = pegar_info_classe_wims($COURSE->id);

                if ($info_turma != null) {
		    $referencia = current($info_turma)->ref_classe_wims;
                    $ativa_sr = current($info_turma)->ativa_sr;
                    $data_atualizacao_recomendacao = current($info_turma)->atualizado;
                } else {
                    unset($referencia);
                    unset($ativa_sr);
                    unset($data_atualizacao_recomendacao);
                }

		$numero_usuarios = 0;
		$numero_sheets = 0;
		if(!empty($referencia)) {
			$sql_relacao_sheet	= "SELECT id_assignment FROM {$CFG->prefix}assignment_sheetwims WHERE id_curso = $COURSE->id";
			$linharelacao	= get_records_sql($sql_relacao_sheet);

			foreach($linharelacao as $sheet){
				$sql_relacao_tarefas_wims	= "SELECT COUNT(id) AS total FROM {$CFG->prefix}assignment WHERE assignmenttype = 'wims' AND id = $sheet->id_assignment";
				$linhatarefa	= get_records_sql($sql_relacao_tarefas_wims);
				$total		= current($linhatarefa)->total;
				if(empty($total)){
					delete_records('assignment_sheetwims', 'id_assignment' , $sheet->id_assignment);
				}
			}

			$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

			$linhauserscurso = objectToArray(listar_usuarios_curso($context->id,'u.id'));
			$linhauserswims = objectToArray(listar_usuarios_cadastrados_wims($COURSE->id));

			foreach($linhauserscurso as $vetorlinhausers){
				if(recursiveArraySearch($vetorlinhausers['id'],$linhauserswims) !== false){

				}else{
					$numero_usuarios++;
				}
			}

			$client = new ClienteWim();
			$resMessage = $client->importarSheets($COURSE->id);
			if ($resMessage)
			{
				$simplexml = new SimpleXMLElement($resMessage->str);
				if($simplexml->retornaSheet != 'false'){
					foreach ($simplexml->sheet as $sheet){
						$sqlassignment_existente	= "SELECT id FROM {$CFG->prefix}assignment_sheetwims  WHERE id_sheet = $sheet->id AND id_curso = $COURSE->id";
						$linhassignment	= get_records_sql($sqlassignment_existente);
						$assignment = current($linhassignment)->id;
						if(empty($assignment)){
							$numero_sheets++;
						}
					}
				}
			}

		}
                //if(isteacher($COURSE->id, $USER->id , true)) {
                if (has_capability('moodle/course:manageactivities', $context)) {
			if(!empty($referencia)) {
				$tb = $tb.'<tr>';
				if($numero_usuarios > 0){
					$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/export_users.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/export_users.png"/></a></td>';
				}
				$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/acessar_turma.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/teacher.png"/></a></td>';
				$tb = $tb.'</tr>';
			}
			if(!empty($referencia)) {
				$tb = $tb.'<tr>';
				if($numero_usuarios > 0){
					$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/export_users.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao2','block_wims').'('.$numero_usuarios.')'.'</a></td>';
				}
				$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/acessar_turma.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao3','block_wims').'</a></td>';
				$tb = $tb.'</tr>';
			}

			if(!empty($referencia)) {
				/*$tb = $tb.'<tr>';
				$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wim/phppages/exportar_aluno.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/students.png"/></a></td>';
				$tb = $tb.'</tr>';*/
			}

			else{
				$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/cadastro_turma.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/config.png"/></a></td>';
			}

		
			if(!empty($referencia)) {
				/*$tb = $tb.'<tr>';
				$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wim/phppages/exportar_aluno.php?course='.$COURSE->id.'">Adicionar Aluno</a></td>';
				$tb = $tb.'</tr>';*/
			}

			if(!empty($referencia)) {
				$tb = $tb.'<tr>';
				if($numero_sheets > 0){
					$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/importar_sheet.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/importar_sheet.png"/></a></td>';
				}
				if($numero_usuarios == 0 && $numero_sheets == 0){
					$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/importar_notas.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/relatorio_notas.png"/></a></td>';
				}
				$tb = $tb.'</tr>';
			}

			if(!empty($referencia)) {
				$tb = $tb.'<tr>';
				if($numero_sheets > 0){
					$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/importar_sheet.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao4','block_wims').'('.$numero_sheets.')'.'</a></td>';
				}
				if($numero_usuarios == 0 && $numero_sheets == 0){
					$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/importar_notas.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao5','block_wims').'</a></td>';
				}
				$tb = $tb.'</tr>';
			}


			else{
				$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/cadastro_turma.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao1','block_wims').'</a></td>';
			}

			$tb = $tb.'</tr>';
		}

		//if(isadmin( $USER->id )) {
                if (has_capability('moodle/course:update', $context)) {
			if(!empty($referencia)) {
				/*$tb = $tb.'<tr>';
				$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wim/phppages/deletar_turma_wims.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/deletar_turma.png"/></a></td>';
				$tb = $tb.'</tr>';
				$tb = $tb.'<tr>';
				$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wim/phppages/deletar_turma_wims.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao6','block_wim').'</a></td>';
				$tb = $tb.'</tr>';*/
			}
		      }

		   /*if(isstudent( $COURSE->id, $USER->id ) && ($COURSE->id == 29 || $COURSE->id == 25 || $COURSE->id == 24 || $COURSE->id == 23 || $COURSE->id == 14 || $COURSE->id == 13 || $COURSE->id == 12 || $COURSE->id == 40 || $COURSE->id == 49 || $COURSE->id == 69)) {
			if(!empty($referencia)) {
				 $rec = get_records('wim_recomendacoes', 'id_curso', $COURSE->id, id_user, $USER->id);
				 $recomendacoes['total']  = count($rec);
echo $recomendacoes['total'];
				 if($recomendacoes['total']  > 0){			     
				        	$tb = $tb.'<tr>';
				        	$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wim/phppages/srd_ver_recomendacao.php?course='.$COURSE->id.'&id_user='.$USER->id.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/recomend.png"/></a></td>';
				        	$tb = $tb.'</tr>';
				        	$tb = $tb.'<tr>';
				        	$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wim/phppages/srd_ver_recomendacao.php?course='.$COURSE->id.'&id_user='.$USER->id.'">'.'Interven&ccedil;&otilde;es'.'</a></td>';
				        	$tb = $tb.'</tr>';
				  }

			}
		   }*/


		//      if(isteacher($COURSE->id, $USER->id , true) && ($COURSE->id == 29 || $COURSE->id == 25 || $COURSE->id == 24 || $COURSE->id == 23 || $COURSE->id == 14 || $COURSE->id == 13 || $COURSE->id == 12 || $COURSE->id == 40 || $COURSE->id == 49 || $COURSE->id == 69)) {
                if (has_capability('moodle/course:manageactivities', $context)) {
			if(!empty($referencia)) {
				        $tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_config.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/sync.png"/></a></td>';
				        $tb = $tb.'</tr>';
				        $tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_config.php?course='.$COURSE->id.'">'.'SRD'.'</a></td>';
				        $tb = $tb.'</tr>';

				if($ativa_sr){
				        
				       $sql = "SELECT mas.id_sheet, mas.id_assignment, ma.name FROM {$CFG->prefix}assignment_sheetwims AS mas, {$CFG->prefix}assignment as ma WHERE mas.id_assignment = ma.id AND mas.id_curso = {$COURSE->id}";
				       $linhasheets = get_records_sql($sql);
				       foreach($linhasheets as $sheet){
				       	$sql2 = "SELECT COUNT(id_curso) as N FROM  {$CFG->prefix}wim_sheet_categoria WHERE id_sheet ={$sheet->id_sheet} AND id_curso = {$COURSE->id}";
				       	$linha2 = get_records_sql($sql2);
			   	       	$n = current($linha2)->N; 
					$categorizar = FALSE;
					if($n == 0){
						$categorizar = TRUE;
						break;
					}
				       }

				        if(!$categorizar){
				        	$now = strtotime(date("Y-m-d H:i:s")); 
				        	if( ($now - $data_atualizacao_recomendacao) > 3600){
 				        	        $recomendacoes = $this->recebe_recomendacoes($COURSE->id, $referencia);
				        	}
				        	else{
						 $rec = get_records('wim_recomendacoes', 'id_curso', $COURSE->id);
						 $recomendacoes['total']  = count($rec);
					
				        	}
				          if($recomendacoes['total']  > 0){			     
				        	$tb = $tb.'<tr>';
				        	$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_recomendacoes_prof2.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/recomend.png"/></a></td>';
				        	$tb = $tb.'</tr>';
				        	$tb = $tb.'<tr>';
				        	$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_recomendacoes_prof2.php?course='.$COURSE->id.'">'.'Relat&oacute;rio'.'</a></td>';
				        	$tb = $tb.'</tr>';
				          }
				        }
				        /*$tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wim/phppages/srd_recomendacoes.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/recomend.png"/></a></td>';
				        $tb = $tb.'</tr>';
				        $tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wim/phppages/srd_recomendacoes.php.php?course='.$COURSE->id.'">'.'Recomenda&ccedil;&otilde;es Aluno ('.$recomendacoes['total'].')</a></td>';
				        $tb = $tb.'</tr>';*/

					
				        $tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_categorizacao.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/category.png"/></a></td>';
				        $tb = $tb.'</tr>';
				        $tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_categorizacao.php.php?course='.$COURSE->id.'">'.'Categoriza&ccedil;&atilde;o'.'</a></td>';
				        $tb = $tb.'</tr>';

					$tb = $tb.'<tr>';
					$faltacategorizarrecomendacao = $this->falta_categorizar_recomendacao();
					//print_r($faltacategorizarrecomendacao);
				        if($faltacategorizarrecomendacao['FaltaCategorizacaoResult']=='true') $tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_sr.php?course='.$COURSE->id.'&a=1"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/alert_sr.png"/></a></td>';
					else $tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_sr.php?course='.$COURSE->id.'&a=0"><img src="'.$CFG->wwwroot.'/blocks/wims/icons/config_sr.png"/></a></td>';
				        $tb = $tb.'</tr>';
				        $tb = $tb.'<tr>';
				        $tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wims/phppages/srd_sr.php">'.'Configura&ccedil;&atilde;o do SR'.'</a></td>';
				        $tb = $tb.'</tr>';

				}

			}
		        }
//$this->content->footer = 'resid'.$COURSE->id;
//return $this->content;
/*		if(isstudent( $COURSE->id, $USER->id )) {
echo "Aki";

			if(!empty($referencia)) {
				/*$tb = $tb.'<tr>';
				 $tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$CFG->wwwroot.'/blocks/wim/phppages/relatorio_notas.php?course='.$COURSE->id.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/notas.png"/></a></td>';
				 $tb = $tb.'</tr>';
				 $tb = $tb.'<tr>';
				 $tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$CFG->wwwroot.'/blocks/wim/phppages/relatorio_notas.php?course='.$COURSE->id.'">'.get_string('menuconfiguracao3','block_wim').'</a></td>';
				 $tb = $tb.'</tr>';*/
/*			}

		}*/
		/*


		$tb = $tb.'<tr>';
		$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$path.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/teacher.png"/></a></td>';
		$tb = $tb.'<td style="text-align: center; border: none;"><a href="'.$path.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/login.png"/></a></td>';
		$tb = $tb.'</tr>';

		$tb = $tb.'<tr>';
		$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"><a href="'.$path.'">Relat&oacute;rio Detalhado</a></td>';
		$tb = $tb.'<td style="text-align: center; font-size: 10px !important; border: none;"<a href="'.$path.'">Acesse as Atividades</a></td>';
		$tb = $tb.'</tr>';

		*/
		$tb = $tb.'</table></li><ul><br/>';
		$tb = $tb .'<a href="'.$path.'">'.'<img style="width : 32px; height : 32px;" src="'.$CFG->wwwroot.'/blocks/wims/banners/wims.gif" alt="WIMS" title="WIMS" align = "right" />'.'</a>';
		$tb = $tb .'<span style="float : right; text-align: right; font-size: 0.6em; font-family: sans-serif">Vers&atilde;o 0.1</span><br/><br/>';

		$this->content->items[]= $tb;

		return $this->content;
	}

	/*function applicable_formats() {
		return array('course' => true);   // Not needed on site
	}*/

	function falta_categorizar_recomendacao(){
		$del = "/";
        include_once ("phppages/srd_lib.php");
		require_once ('phppages/nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
		$wsdl = "http://200.129.43.179/servicosr/Servicos.asmx?wsdl";//$config_srd['wsdl_server'];
		$clientesrv = new nusoap_client($wsdl, true);
		$err = $clientesrv -> getError();
		if ($err) {
			echo "Erro no construtor<pre>" . $err . "</pre>";
		}
		$result = $clientesrv -> call('FaltaCategorizacao');
		if ($clientesrv -> fault) {
			echo "Falha<pre>" . print_r($result) . "</pre>";
		} else {
			$err = $clientesrv -> getError();
			if ($err) {
				echo "Erro... <pre>" . $err . " ... </pre>";
			} else{
				return $result;
			}
		}
	}

	function recebe_recomendacoes($id_curso, $referencia){
	global $USER;
	ini_set('memory_limit','-1');
	        $info_turma = pegar_info_classe_wims($id_curso);
	        //$recomendacoes = get_records('wim_recomendacoes', 'id_curso', $id_curso );
	        //if(count($recomendacoes) > 0 )
	        include_once ("phppages/srd_lib.php");
		    $del = "/";
		    require_once ('phppages/nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
            $wsdl = $config_srd['wsdl'];
	 	    $cliente = new nusoap_client($wsdl, true);
		    $err = $cliente -> getError();
		    if ($err) {
			echo "Erro no construtor<pre>" . $err . "</pre>";
		    }

		delete_records('wim_recomendacoes', 'id_curso' , $id_curso);

		$recomendacoes = $cliente -> call('envia_recomendacoes', array($id_curso, $referencia));

		if ($cliente -> fault) {
			echo "Falha<pre>" . print_r($result) . "</pre>";
		} else {

			$err = $cliente -> getError();
			if ($err) {
				echo "Erro<pre>" . $err . " ... </pre>";
			} 
		}


  		$array_recomendacoes = json_decode(utf8_encode($recomendacoes),true);
		
		/*if($USER->id == 65){
		 file_put_contents('/var/www/lero.txt',$recomendacoes);
		 print_object($array_recomendacoes);
		}*/
		
		$contagem_recomendacao_usuario['total'] =0;
		if(!empty( $array_recomendacoes)){
		foreach ($array_recomendacoes as $recomendacao) {
			$id_usuario = $recomendacao['Aluno'];
			
			foreach ($recomendacao['Folhas'] as $folha) {
			$id_sheet = $folha['Numero'];
			$tipo_intervencao = $folha['Intervencao'];
			$observacao = $folha['Observacao'];
			$links = $folha['Link'];
			if(empty($links)){

				$links[0]['Endereco'] = "";
				$links[0]['Id'] = "0";
				$links[0]['Nota'] = "0";

			}
			$contagem_recomendacao_usuario[$id_usuario] = 0;
	 		foreach ($links as $key => $link) {	
				if($link['Id'] != 0){	
						if($tipo_intervencao == 'Alta'){
				 			$contagem_recomendacao_usuario[$id_usuario]++;
							$contagem_recomendacao_usuario['total']++;
						}
				 		$link_recomendacao = utf8_encode($link['Endereco']);
				 		$categoria = utf8_encode($link['Categoria']);
				 		$id_recomendacao = $link['Id'];			
				 		$nota = $link['Nota'];	
				 		$a = new Object();
				 		$a->id_curso = $id_curso;
				 		$a->id_sheet = $id_sheet;
				 		$a->categoria = $categoria;
				 		$a->id_user = $id_usuario;
				 		$a->tipo_intervencao = $tipo_intervencao;
				 		$a->id_recomendacao = $id_recomendacao;
				 		$a->observacao = utf8_encode($observacao);
				 		$a->link_recomendacao = $link_recomendacao;
						$a->avaliacao_aluno = $nota;
				 		if (insert_record("wim_recomendacoes" ,$a)) {
							//echo '<a href="'.$link.'">'.$id_usuario.' <span class="badge">'.$link.'</span></a>'; 
							//echo '<br><br>';
						}else echo 'Erro ao receber recomendações';}
}
		}
		}
}
		//}

		$now = strtotime(date("Y-m-d H:i:s")); 
		$a = new Object();
		//print_r($info_turma);
		$a->id = current($info_turma)->id;
		$a->atualizado = $now;
		//print_object($a);
		update_record("config_wim" ,$a);
		return $contagem_recomendacao_usuario;
	}

}

?>
