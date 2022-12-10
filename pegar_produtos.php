<?php
 
/*
 * O seguinte codigo retorna para o cliente a lista de produtos 
 * armazenados no servidor. Essa e uma requisicao do tipo GET. 
 * Devem ser enviados os parâmetro de limit e offset para 
 * realização da paginação de dados no cliente.
 * A resposta e no formato JSON.
 */

// conexão com bd
require_once('conexao_db.php');

// autenticação
require_once('autenticacao.php');

// array for JSON resposta
$resposta = array();
$calendario = array(
	1 => "Janeiro",
    2 => "Fevereiro",
    3 => "Março",
	4 => "Abril",
	5=> "Maio",
	6 => "Junho",
	7 => "Julho",
	8 => "Agosto",
	9 = "Setembro",
	10 => "Outubro",
	11 = > "Novembro",
	12 => "Dezembro"
);

// verifica se o usuário conseguiu autenticar
if(autenticar($db_con)) {
	
	// Primeiro, verifica-se se todos os parametros foram enviados pelo cliente.
	// limit - quantidade de produtos a ser entregues
	// offset - indica a partir de qual produto começa a lista
	if (isset($_GET['limit']) && isset($_GET['offset'])&& isset($_GET['mes'])) {
	 
		$limit = $_GET['limit'];
		$offset = $_GET['offset'];
		$mes = $_GET['mes'];
		$mesAtual = date('m');
		$mesPesquisado = $mesAtual - $mes; 
 
		// Realiza uma consulta ao BD e obtem todos os produtos.
		$consulta = $db_con->prepare("SELECT gasto.id , gasto.valor , gasto.gasto, gasto.data FROM gasto 
join usuario_tpgasto_tipo_gasto_usuario_gasto_planejamento td on (gasto.id = td.fk_gasto_id)
join usuario on (usuario.id = td.fk_usuario_id)
where usuario.cpf = '" . $login . "' and EXTRACT(MONTH FROM gasto.data) =" .$mesPesquisado. " LIMIT " . $limit . " OFFSET " . $offset);
		if($consulta->execute()) {
			// Caso existam produtos no BD, eles sao armazenados na 
			// chave "produtos". O valor dessa chave e formado por um 
			// array onde cada elemento e um produto.
			$resposta["gastos"] = array();
			$resposta["mesPesquisado"] = $calendario[$mesPesquisado];
			$resposta["sucesso"] = 1;

			if ($consulta->rowCount() > 0) {
				while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
					// Para cada produto, sao retornados somente o 
					// pid (id do produto), o nome do produto e o preço. Nao ha necessidade 
					// de retornar nesse momento todos os campos dos produtos 
					// pois a app cliente, inicialmente, so precisa do nome e preço do mesmo para 
					// exibir na lista de produtos. O campo id e usado pela app cliente 
					// para buscar os detalhes de um produto especifico quando o usuario 
					// o seleciona. Esse tipo de estrategia poupa banda de rede, uma vez 
					// os detalhes de um produto somente serao transferidos ao cliente 
					// em caso de real interesse.
					$gasto = array();
					$gasto["id"] = $linha["id"];
					$gasto["valor"] = $linha["valor"];
					$gasto["gasto"] = $linha["gasto"];
					$gasto["data"] = $linha["data"];
			 
					// Adiciona o produto no array de produtos.
					array_push($resposta["gastos"], $gasto);
				}
			}
		}
		else {
			// Caso ocorra falha no BD, o cliente 
			// recebe a chave "sucesso" com valor 0. A chave "erro" indica o 
			// motivo da falha.
			$resposta["sucesso"] = 0;
			$resposta["erro"] = "Erro no BD: " . $consulta->error;
		}
	}
	else {
		// Se a requisicao foi feita incorretamente, ou seja, os parametros 
		// nao foram enviados corretamente para o servidor, o cliente 
		// recebe a chave "sucesso" com valor 0. A chave "erro" indica o 
		// motivo da falha.
		$resposta["sucesso"] = 0;
		$resposta["erro"] = "Campo requerido não preenchido";
	}
}
else {
	// senha ou usuario nao confere
	$resposta["sucesso"] = 0;
	$resposta["erro"] = "usuario ou senha não confere";
}

// fecha conexão com o bd
$db_con = null;

// Converte a resposta para o formato JSON.
echo json_encode($resposta);
?>