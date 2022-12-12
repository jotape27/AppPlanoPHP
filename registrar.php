<?php
/*
 * O código abaixo registra um novo usuário.
 * Método do tipo POST.
 */
require_once('conexao_db.php');
// array de resposta
$resposta = array();
// verifica se todos os campos necessários foram enviados ao servidor
if (isset($_POST['novo_nome']) && isset($_POST['novo_sobrenome']) && isset($_POST['novo_login']) && isset($_POST['novo_nascimento']) && isset($_POST['novo_sexo']) && isset($_POST['novo_celular']) && isset($_POST['novo_email']) && isset($_POST['nova_senha'])) {

    // o método trim elimina caracteres especiais/ocultos da string
    $novo_nome = trim($_POST['novo_nome']);
    $novo_sobrenome = trim($_POST['novo_sobrenome']);
    $novo_login = trim($_POST['novo_login']);
    $data = str_replace("/" , "-" , $_POST['novo_nascimento']);
    $novo_nascimento = date('Y-m-d' , strtotime($data));
	$novo_sexo = trim($_POST['novo_sexo']);
    $novo_celular = trim($_POST['novo_celular']);
    $novo_email = trim($_POST['novo_email']);
    // $novo_perfil = trim($_POST['novo_perfil']);
    $nova_senha = trim($_POST['nova_senha']);

    // o bd não armazena diretamente a senha do usuário, mas sim
    // um código hash que é gerado a partir da senha.
    $token = password_hash($nova_senha, PASSWORD_DEFAULT);
    // antes de registrar o novo usuário, verificamos se ele já
    // não existe.
    $consulta_usuario_existe = $db_con->prepare("SELECT cpf FROM usuario WHERE cpf ='$novo_login'");
    $consulta_usuario_existe->execute();
    if ($consulta_usuario_existe->rowCount() > 0) {
        // se já existe um usuario para login
        // indicamos que a operação não teve sucesso e o motivo
        // no campo de erro.
        $resposta["sucesso"] = 0;
        $resposta["erro"] = "usuario ja cadastrado";
    } else {
        // se o usuário ainda não existe, inserimos ele no bd.
        $consulta = $db_con->prepare("INSERT INTO usuario VALUES(DEFAULT,'$novo_nome','$novo_sobrenome','$novo_login','$novo_sexo','$novo_nascimento','$token') RETURNING id");

        if ($consulta->execute()) {
            //
            $linha = $consulta->fetch(PDO::FETCH_ASSOC);
            $id_user = $linha['id'];
            //
            $consulta2 = $db_con->prepare("INSERT INTO usuario_tpcontato VALUES ('$id_user','$novo_email','$novo_celular');");
            //
            if ($consulta2->execute()) {
                // se a consulta deu certo, indicamos sucesso na operação.
                $resposta["sucesso"] = 1;
            } else {
                // se houve erro na consulta, indicamos que não houve sucesso
                // na operação e o motivo no campo de erro.
                $resposta["sucesso"] = 0;
                $resposta["erro"] = "erro BD: " . $consulta2->error;
            }
        } else {
            // se houve erro na consulta, indicamos que não houve sucesso
            // na operação e o motivo no campo de erro.
            $resposta["sucesso"] = 0;
            $resposta["erro"] = "erro BD: " . $consulta->error;
        }
    }
} else {
    // se não foram enviados todos os parâmetros para o servidor,
    // indicamos que não houve sucesso
    // na operação e o motivo no campo de erro.
    $resposta["sucesso"] = 0;
    $resposta["erro"] = "faltam parametros";
}
// A conexão com o bd sempre tem que ser fechada
$db_con = null;

// converte o array de resposta em uma string no formato JSON e
// imprime na tela.
echo json_encode($resposta);
