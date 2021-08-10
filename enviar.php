<?php 

	define('MDS_START', true);      //Uso essa variável pra classe saber que está sendo chamada localmente pela minha aplicação.
	define('UPLOAD_MAX_SIZE', 15); //Tamanho em MB. Dediquei 15MB.
	
	
	require_once('Uploader.php');
	
	/*
		O nome do arquivo pode ter mais de 100 caracteres, dedique pelo menos 250 no banco de dados por segurança.
	*/
	
	/*
		LEMBRE-SE! 
		--- Na pasta de uploads MANTENHA o arquivo index.php, pois sem ele TODOS OS ARQUIVOS ficarão listados.
	*/
	
	$attachment = $_FILES['attachment'];
	
	$Uploader = new Uploader();
	$Uploader -> set_file( $attachment ); 						  //Arquivo
	$Uploader -> set_extensions( ['png', 'jpg', 'html', 'pdf'] ); //Extensões
	
	$attachment_directory = $Uploader -> read();
	
	
	if( empty( $attachment_directory ) ){
		//Houve erro 
		$errors = $Uploader -> get_errors();
		var_dump($errors);
	}else{
		//Ele vai retornar uploads//$file 
		
		$attachment_directory  = explode('/', $attachment_directory);
		$attachment_name 	= end( $attachment_directory );
		
		//Deu certo 
		echo '<p>Arquivo pode ser acessado em : uploads/' . $attachment_name . ' </p>' ;
	}
	
	


?>