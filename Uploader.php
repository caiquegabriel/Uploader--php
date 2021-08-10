<?php

    if(!defined('MDS_START')) die('');

    class Uploader{

        /* 
            @var (array)
        */
        protected $_extensions              = [];

        /* 
            @var (array)
        */
        protected $_file                    = [];

        /* 
            @var (string)
        */
        protected $_file_extension          = null;

        /* 
            @var (string)
        */
        protected $_dir                     = null;

        /* 
            @var (string)
        */
        protected $_file_directory          = null;

        /*
            Tamanho do arquivo
            @var (float)
        */
        protected $_file_size               = 0; 

        /* 
            @var (array)
        */
        protected $_errors                  = [];

        /* 
            @var (array)
        */
		protected $_extensions_blocked      = ['exe', 'php', 'php1', 'php2', 'php3', 'php4', 'php3', 'py', 'exe', 'bat', 'cmd', 'app', 'apk', 'bin'];
        
        protected $_images_extensions = ['png',
        'jpe',
        'jpeg',
        'jpg',
        'gif',
        'bmp',
        'ico',
        'tiff',
        'tif',
        'svg',
        'svgz'
        ] ;

        /* 
            @var (array)
        */
        protected $_mime_types              = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html'=> 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js'  => 'application/javascript',
            'json'=> 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt'  => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai'  => 'application/postscript',
            'eps' => 'application/postscript',
            'ps'  => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'xlsx'=> [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/octet-stream',
                'application/zip'
            ],

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );


        /*
            Localização da página de upload
        */
        protected const _upload_root        = "uploads/";


        public function __construct(){ }

        /*
            @Params (string) $error
        */
        protected function _set_error( $error ){
            if( is_string( $error ) ){
                $this -> _errors[] = $error;
            }
        }

        /*
            @Returns (array) $errors
        */
        public function get_errors(){
            return $this -> _errors;
        }

        /*
            Tamanho do arquivo
            @Returns (float) $size
        */
        public function get_file_size(){
            $size = $this -> _file_size;
            if(!is_numeric($size) || $size <= 0)
                return 0;

            $size = $size/1024; 

            return $size;
        }
        
        /*
            Inicialmente a função que chama este objeto setará as extensões possíveis.
            @Params (array) $extensions
        */
        public function set_extensions( $extensions ){
            if(is_array($extensions)){
                $this -> _extensions = $extensions;
            }
        }

        /*
            O arquivo
            @Params (array) $file
        */
        public function set_file( $file ){
            if( is_array( $file ) && count( $file ) > 0 ){
                $this -> _file = $file;
            }
        } 

        /*
            @Returns (string)
        */
        public function get_file_extension(){
            return $this -> _file_extension;
        }

        /*
            Setará o diretório
            @Params (string) $dir
        */
        public function set_dir( $dir ){
            if( is_string( $dir ) && !empty($dir) ){
                $this -> _dir = $dir;
            }
        }

        /*
            @Returns (string)
        */
        public function get_file_type(){   
            return $this -> _file_extension; 
        }

		/*
			@Returns (string) 
		*/
        public function generate_token(){  
            return md5(uniqid(mt_rand(), true)); 
        }

        /*

            Vamos começar.

            @Returns (string) Endereço do arquivo.
        */
        public function read(){

            $file = $this -> _file;
            $dir  = $this -> _dir; 

            $file_name      =  ( isset($file['name'])     && is_string($file['name']))     ? $file['name']       : null;
            $file_tmp_name  =  ( isset($file['tmp_name']) && is_string($file['tmp_name'])) ? $file['tmp_name']   : null;
            $file_extension =  ( isset($file['type'])     && is_string($file['type']))     ? $file['type']       : null;
            $file_size      =  ( isset($file['size'])     && is_numeric($file['size']))     ? $file['size']       : null;

            $dir            =  ( !empty( $this -> _dir )) ? $this -> _dir : null;
            $dir            =  ( substr($dir, -1) === '/' ) ? substr($dir, 0, -1) : $dir;

             
            if(  is_null( $file_name ) || is_null( $file_tmp_name ) || is_null( $file_extension ) || is_null( $file_size ) ){
                $this -> _set_error ('O arquivo não possui todos os dados necessários');
                return;
            } 

            if( $file_size > ( $max_size = 1024 * 1024 * UPLOAD_MAX_SIZE ) ){
                $this -> _set_error('O arquivo não pode ter mais '.UPLOAD_MAX_SIZE.'MB de tamanho!');
                return;
            }

            /*
                Vamos verificar o mime do arquivo
            */
            $file_mimetype =   $this -> verify_mimetype( $file ); 
            
            $file_name = strtotime(date('Y-m-d H:i:s')).rand(0, 1000).'.'.$file_mimetype;
               
            
            if( empty($file_mimetype) )
                return; 

            if( in_array( $file_mimetype , $this -> _extensions_blocked ) ){
                $this -> _set_error('A seguinte extensão <b>' . $file_mimetype . '</b> não é permitida. As seguintes extensões estão banidas : ' . implode(', ', $this -> _extensions_blocked));
                return;
            }

            $this -> _file_extension = $file_mimetype;

            /*
                Vamos gerar o nome do arquivo
            */
            $max = 0;
            do{
                $token = $this->generate_token();

                $new_name = $token.'-'.$file_name;

                $file_directory = $dir.'/'.$new_name;

                if( strlen( $new_name ) >= 100 ){
                    $this -> _set_error(' Este arquivo possui um nome muito grande');
                    return;
                }

                if( $max > 25){
                    $this -> _set_error(' Falha ao criar nome do arquivo após 25 tentativas de verificação do nome');
                    return;
                }
                $max++;
            }while(\Uploader::file_exists( $file_directory ) === TRUE );

            /*
                Vamos concluir o upload.
            */
            return $this -> _upload( $file_tmp_name , self::_upload_root . $file_directory );
        }

        /*
            Enfim, essa função irá mover o arquivo temporário para o diretório desejado.

            @Params  (string) $file_tmp_name  : Nome termporário do arquivo
            @Params  (string) $file_directory : Diretório o qual deseja salvar o arquivo
            @Returns (string) $file_directory : Localização final do arquivo
        */
        protected function _upload( $file_tmp_name , $file_directory ){
            if (move_uploaded_file( $file_tmp_name, $file_directory )) {
				/*
					Se der certo, o retorno é a STRING do nome do arquivo
				*/
                $this -> _file_directory = $file_directory;

                $this -> _file_size = filesize($file_directory);

				return $file_directory;
			}
            $this -> _set_error(' Houve uma falha ao mover arquivo ');
        }

        /*
            Verificará se o arquivo existe
            @Params  (string) $file_url  : Endereço do arquivo
            @Returns (bool)
        */
        public static function file_exists( $file_url ){

            if(!is_string($file_url) OR empty($file_url)){
                return;
            } 

            $file_url = urldecode( $file_url );

            if( is_file( self::_upload_root . $file_url ) && !is_dir( self::_upload_root . $file_url ) ){
                return TRUE;
            }
            return FALSE;
        }


        /*
            Esta função removerá o arquivo

            @Params  (string) $file_directory : Endereço da imagem

            @Returns (bool)
        */
        public function remove_file( $file_directory = '' ){  
            if($file_directory === '' ){
                if( $this -> _file_directory !== null && !is_dir( $this -> _file_directory ) && is_file( $this -> _file_directory )){
                    return unlink( $this -> _file_directory );
                }
            }else if( $file_directory !== null && !is_dir( $file_directory ) && is_file( $file_directory )){
                return unlink( $file_directory );
            }else if( self::_upload_root . $file_directory !== null && !is_dir( self::_upload_root . $file_directory ) && is_file( self::_upload_root .  $file_directory ) ){
                return unlink( self::_upload_root . $file_directory );
            }
            return FALSE;
        }


        /*
            Esta função verificará o mimetype

            @Params  (string) $file : O arquivo
            @Returns (string) $file_extension;
        */
        public function verify_mimetype( $file ){

            if( !is_array( $file ) ){
                return;
            }

            $file_tmp = isset($file['tmp_name']) && is_string($file['tmp_name']) ? $file['tmp_name'] : '' ;

            if( empty( $file_tmp ) ){
                $this -> _set_error('Nenhum arquivo inserido');
                return;
            }

    		if( !is_file( $file_tmp ) ){
    			return;
    		}

            $file_mime_type = mime_content_type( $file_tmp );
            
            foreach( $this -> _mime_types as $extension => $mime ){ 
                if(is_string($mime)){
                    if( $mime === $file_mime_type ){  
                        if( in_array( $extension , $this -> _extensions ) ){  
                            return $extension;
                        } 
                    }
                }else if(is_array( $mime ) ){
                    foreach( $mime as $mime_value ){ 
                        if( $mime_value === $file_mime_type ){ 
                            if( in_array( $extension , $this -> _extensions ) ){ 
                                return $extension;
                            } 
                        }
                    }
                }
            } 

            $this -> _set_error('O arquivo selecionado não bate com as extensões definidas');
            
            return;
        }
    }

?>
