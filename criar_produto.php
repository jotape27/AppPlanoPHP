<?php

/*
 * O seguinte codigo abre uma conexao com o BD e adiciona um produto nele.
 * As informacoes de um produto sao recebidas atraves de uma requisicao POST.
 */

// conexão com bd
require_once('conexao_db.php');

// autenticação
require_once('autenticacao.php');

// array de resposta
$resposta = array();

// verifica se o usuário conseguiu autenticar
if (autenticar($db_con)) {

    // Primeiro, verifica-se se todos os parametros foram enviados pelo cliente.
    // A criacao de um produto precisa dos seguintes parametros:
    // nome - nome do produto
    // preco - preco do produto
    // descricao - descricao do produto
    // img - imagem do produto
    if (isset($_POST['valor']) && isset($_POST['nome']) && isset($_POST['categoria']) && isset($_POST['data'])) {

        // Aqui sao obtidos os parametros
        $nome = $_POST['nome'];
        $tipo = $_POST['categoria'];
        $valor = $_POST['valor'];
        $data = $_POST['data'];

        // Para a imagem do produto, primeiramente se determina qual o tipo de imagem.
        // Isso e feito atraves da obtencao da extensao do arquivo, localizada na parte
        // final do nome do arquivo (ex. ".jpg")

        /*$imageFileType = strtolower(pathinfo(basename($_FILES["img"]["name"]), PATHINFO_EXTENSION));*/

        // A imagem e convertida de binario para string atraves do metodo de codificacao
        // base64

        /*$image_base64 = base64_encode(file_get_contents($_FILES['img']['tmp_name']));*/

        // No futuro, clientes que pedirem pela imagem armazenada no BD devem ser
        // capazes de converter a string base64 para o formato original binario.
        // Para que isso possa ser feito, contatena-se no inicio da string base64 da
        // imagem o mimetype do arquivo original. O mimetype e um codigo que indica o
        // tipo de arquivo e sua extensao.

        /*$img = 'data:image/' . $imageFileType . ';base64,' . $image_base64;*/

        // A proxima linha insere um novo produto no BD.
        // A variavel consulta indica se a insercao foi feita corretamente ou nao.

        //$data = date('Y-m-d');


        $consulta3 = $db_con->prepare("SELECT id FROM usuario WHERE cpf = $login");
        $consulta3->execute();
        $linha = $consulta3->fetch(PDO::FETCH_ASSOC);
        $id_user = $linha['id'];

        $consulta = $db_con->prepare("INSERT INTO gasto (valor, gasto, data) VALUES('$valor', '$nome', '$data') RETURNING id;");
        if ($consulta->execute()) {

            if ($consulta->rowCount() > 0) {
                $linha = $consulta->fetch(PDO::FETCH_ASSOC);

                

                $id_gasto = $linha['id'];
                $consulta2 = $db_con->prepare("INSERT INTO usuario_tpgasto_tipo_gasto_usuario_gasto_planejamento VALUES ('$tipo','$id_user','$id_gasto');");

                if ($consulta2->execute()) {
                    // Se o produto foi inserido corretamente no servidor, o cliente
                    // recebe a chave "sucesso" com valor 1
                    $resposta["sucesso"] = 1;
                } else {
                    $resposta["sucesso"] = 0;
                    $resposta["erro"] = "Erro ao criar produto no BD: " . $consulta->error;
                }
            } else {
                $resposta["sucesso"] = 0;
                $resposta["erro"] = "Erro ao criar produto no BD: " . $consulta->error;
            }
        } else {
            // Se o produto nao foi inserido corretamente no servidor, o cliente
            // recebe a chave "sucesso" com valor 0. A chave "erro" indica o
            // motivo da falha.
            $resposta["sucesso"] = 0;
            $resposta["erro"] = "Erro ao criar produto no BD: " . $consulta->error;
        }
    } else {
        // Se a requisicao foi feita incorretamente, ou seja, os parametros
        // nao foram enviados corretamente para o servidor, o cliente
        // recebe a chave "sucesso" com valor 0. A chave "erro" indica o
        // motivo da falha.
        $resposta["sucesso"] = 0;
        $resposta["erro"] = "Campo requerido nao preenchido";
    }
} else {
    // senha ou usuario nao confere
    $resposta["sucesso"] = 0;
    $resposta["erro"] = "usuario ou senha não confere";
}

// Fecha a conexao com o BD
$db_con = null;

// Converte a resposta para o formato JSON.
echo json_encode($resposta);
