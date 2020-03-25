<?php
// Hook por Joel Emanoel- https://webnity.com.br
use WHMCS\Database\Capsule;
function validacao_cpf_cnpj($vars){
	/* EDITAR A PARTIR DAQUI */
	$customfield_cpf_cnpj = 75;
	$mensagem_erro_invalido = "O CPF/CNPJ informado não é válido.";
	$mensagem_erro_duplicado = 'Já existe uma conta cadastrada com esse CPF/CPNJ. Por favor <a href="login.php">faça login</a>.';
	/* NÃO EDITAR A PARTIR DAQUI */
	$pdo = Capsule::connection()->getPdo();
	$cpf_cnpj = preg_replace("/[^0-9]/", "", $vars["customfield"][$customfield_cpf_cnpj]);
	if($cpf_cnpj!="1" AND !$_SESSION['adminid']){
		if(!validaCPF($cpf_cnpj) && !validaCNPJ($cpf_cnpj)){
			$return[] = $mensagem_erro_invalido;
		}elseif(!$_SESSION["uid"] AND !$_SESSION['adminid']){
			$sql = $pdo->query("SELECT * FROM tblcustomfieldsvalues WHERE fieldid = '{$customfield_cpf_cnpj}'");
			while($exibir = $sql->fetch()){
				if(preg_replace("/[^0-9]/", "", $exibir["value"]) == $cpf_cnpj){
					$return[] = $mensagem_erro_duplicado;
				}
			}
		}elseif($_SESSION["uid"] ){
			$id = $_SESSION["uid"];
			$sql = $pdo->query("SELECT * FROM tblcustomfieldsvalues WHERE fieldid = '{$customfield_cpf_cnpj}' AND relid != '{$id}'");
			while($exibir = $sql->fetch()){
				if(preg_replace("/[^0-9]/", "", $exibir["value"]) == $cpf_cnpj){
					$return[] = $mensagem_erro_duplicado;
				}
			}
		}
	}
	return $return;
}
if(!function_exists("validaCPF")){
	function validaCPF($cpf = null){
		if(empty($cpf)){
			return false;
		}
		$cpf = preg_replace("/[^0-9]/", "", $cpf);
		$cpf = str_pad($cpf, 11, "0", STR_PAD_LEFT);
		if(strlen($cpf) != 11){
			return false;
		}elseif($cpf == "00000000000" || 
			$cpf == "11111111111" || 
			$cpf == "22222222222" || 
			$cpf == "33333333333" || 
			$cpf == "44444444444" || 
			$cpf == "55555555555" || 
			$cpf == "66666666666" || 
			$cpf == "77777777777" || 
			$cpf == "88888888888" || 
			$cpf == "99999999999") {
			return false;
		 }else{   
			for($t = 9; $t < 11; $t++){
				for($d = 0, $c = 0; $c < $t; $c++){
					$d += $cpf{$c} * (($t + 1) - $c);
				}
				$d = ((10 * $d) % 11) % 10;
				if($cpf{$c} != $d){
					return false;
				}
			}
			return true;
		}
	}
}
if(!function_exists("validaCNPJ")){
	function validaCNPJ($cnpj = null){
		if(empty($cnpj)){
			return false;
		}
		$cnpj = preg_replace("/[^0-9]/", "", $cnpj);
		$cnpj = str_pad($cnpj, 14, "0", STR_PAD_LEFT);
		if(strlen($cnpj) != 14){
			return false;
		}elseif($cnpj == "00000000000000" || 
			$cnpj == "11111111111111" || 
			$cnpj == "22222222222222" || 
			$cnpj == "33333333333333" || 
			$cnpj == "44444444444444" || 
			$cnpj == "55555555555555" || 
			$cnpj == "66666666666666" || 
			$cnpj == "77777777777777" || 
			$cnpj == "88888888888888" || 
			$cnpj == "99999999999999") {
			return false;
		}else{   
			$j = 5;
			$k = 6;
			$soma1 = "";
			$soma2 = "";
			for ($i = 0; $i < 13; $i++) {
				$j = $j == 1 ? 9 : $j;
				$k = $k == 1 ? 9 : $k;
				$soma2 += ($cnpj{$i} * $k);
				if($i < 12){
					$soma1 += ($cnpj{$i} * $j);
				}
				$k--;
				$j--;
			}
			$digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
			$digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
			return (($cnpj{12} == $digito1) and ($cnpj{13} == $digito2));
		}
	}
}
add_hook("ClientDetailsValidation", 1, "validacao_cpf_cnpj");
