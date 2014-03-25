<?php

/**
 *  Class FileDuck
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Crsitiano Corrêa Schmidt ( cristiano.correa@prognus.com.br )
 * @version    1.0
 */
class FileDuck
{
    /**
     * @var array
     */
    var $config;
    /**
     * @var array
     */
    var $files = array();
    /**
     * @var array
     */
    var $filesEncoding = array();
    /**
     * @var
     */
    var $filesCompressed;
    /**
     * @var
     */
    var $response;
    /**
     * @var
     */
    var $type;
    /**
     * @var
     */
    var $providerClass;
    /**
     * @var
     */
    var $fileUnified;
    /**
     * @var
     */
    var $request;
    /**
     * @var
     */
    var $fileTranslated;


    /**
     * @param array $pFileDuckConfig
     * @param array $pProviderConfig
     */
    public function __construct( $pFileDuckConfig = array()  , $pProviderConfig = array() )
    {
        ini_set('memory_limit','1024M');
        set_time_limit(300);

        // Carrega a configuração //
        $fileDuckConfig = array();
        require __DIR__ . '/config.php';
        $this->config = array_merge( $fileDuckConfig , $pFileDuckConfig);
        $this->debug( 'FUNCTION: __construct' );
        $this->debug( '     Config: '.var_export($this->config , true) );


        foreach ( $this->config['tokens']  as &$token) //Skip tokens
            $token = '\\'.implode('\\' , str_split($token));

        $className = $this->config['provider'] . 'Provider';

        if(!file_exists( __DIR__ . "/provider/{$this->config['provider']}/{$className}.php"))
            die( "PROVIDER $className NOT EXITS");

        require_once(__DIR__ . "/provider/{$this->config['provider']}/{$className}.php");

        // Carrega a configuração do provedor//
        $providerConfig = array();
        require __DIR__ . "/provider/{$this->config['provider']}/config.php";
        $providerConfig = array_merge( $providerConfig , $pProviderConfig);

        $this->providerClass = new $className( $this->config['lang'] , $providerConfig );

        $this->_checkCacheFolders();
    }

    /**
     *  Adiciona um arquivo para ser compilado
     *
     * @param $file
     * @param string $encode
     * @return $this
     */
    public function add( $file  , $encode = 'UTF-8')
    {
        $this->debug( 'FUNCTION: add' );

        $files =  is_array($file) ? $file : array($file);

        foreach( $files as $file){
            if(realpath($file)){
                $this->debug( '    ADD FILE: '. realpath($file) . ' ENCODE: ' . $encode);
                $this->files[] = realpath($file);
                $this->filesEncoding[] = $encode;
            }else{
                $this->debug( '    FILE NOT FOUND: '. $file);
                die( 'File not found: '. $file );
            }
        }

        return $this;
    }

    /**
     * Retorna o conteudo compilado
     * @return string
     */
    public function renderContent()
    {
        $this->debug( 'FUNCTION: renderContent' );

        if( $this->config['environment'] === 'prod'  ){
            $name  = md5( json_encode( $this->files ) . $this->config['lang'] );
            $compiledFile  = $this->config['cacheFolder'] . '/compiled/' . $name;

            if( file_exists($compiledFile) ){
                $data = file_get_contents( $compiledFile );
            } else {
                $this->_process();
                ob_start();
                require_once( $this->fileTranslated );
                $data = ob_get_clean();
                file_put_contents( $compiledFile , $data );

            }

            $this->debug( '    Render file: ' .$compiledFile);

        } else {
            $this->_process();
            ob_start();
            require_once( $this->fileTranslated );
            $data = ob_get_clean();
            $this->debug( '    Render file: ' .$this->fileTranslated);
        }

        return $data;
    }

    /**
     * Imprime o arquivo o arquivo processado
     * @param string $contentType
     */
    public function renderFile( $contentType = 'application/octet-stream' )
    {
        $this->debug( 'FUNCTION: renderFile' );
        header('Content-Type: '.$contentType.'; charset=utf-8');

        switch( $this->config['cacheModel'] ) {
            case 'ETag';
                $this->debug( '    ETag');
                $md5 = md5_file( $this->fileUnified ) . base64_encode($this->config['lang']);
                if (!isset($_SERVER['HTTP_IF_NONE_MATCH']) || trim($_SERVER['HTTP_IF_NONE_MATCH']) !==  $md5 ){
                    $this->debug( '    Return http code: 200 ');
                    header('Etag: '.$md5 , true, 200);
                    $data = $this->renderContent();
                    header('Content-Length: '.mb_strlen($data, '8bit'));
                    echo $data;
                }
                else{
                    $this->debug( '    Return http code: 304 ');
                    header('Etag: '.$md5 , true, 304);
                }
                break;

            case 'LastModified':
                $this->debug( '    LastModified');
                if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) !== filemtime($this->fileUnified))){
                    $this->debug( '    Return http code: 200 ');
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($this->fileUnified)).' GMT', true, 200);
                    $data = $this->renderContent();
                    header('Content-Length: '.mb_strlen($data, '8bit'));
                    echo $data;
                }
                else{
                    $this->debug( '    Return http code: 304 ');
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($this->fileUnified)).' GMT', true, 304);
                }
                break;

            case 'Expires':
                $this->debug( '    Expires');
                $this->debug( '    Return http code: 200 ');
                (int)$time =  isset($this->config['cacheExpireTime']) ? $this->config['cacheExpireTime'] : 1800;

                header("Expires: " . date(DATE_RFC822,strtotime("+{$time} minutes")));
                $data = $this->renderContent();
                header('Content-Length: '.mb_strlen($data, '8bit'));
                echo $data;
                break;

            case 'MaxAge':
                $this->debug( '    MaxAge');
                $this->debug( '    Return http code: 200 ');
                (int)$time =  ( isset($this->config['cacheMaxAgeTime']) ? $this->config['cacheMaxAgeTime'] : 1800 ) * 60;

                header( "Cache-Control: max-age={$time}" );
                $data = $this->renderContent();
                header('Content-Length: '.mb_strlen($data, '8bit'));
                echo $data;
                break;

            default:
                $this->debug( '    No cache');
                $this->debug( '    Return http code: 200 ');
                $data = $this->renderContent();
                header('Content-Length: '.mb_strlen($data, '8bit'));
                echo $data;
                break;
        }
    }

    /**
     * Verifica se as pastas de chache estão criadas e as cria caso nescessario
     */
    private function _checkCacheFolders()
    {
        $this->debug( 'FUNCTION: _checkCacheFolders' );

        if (!file_exists($this->config['cacheFolder'])){
            $this->debug( '     Create Folder: ' . $this->config['cacheFolder'] );
            mkdir($this->config['cacheFolder'], 0777);
        }

        if (!file_exists($this->config['cacheFolder'].'/minify/')){
            $this->debug( '     Create Folder: ' . $this->config['cacheFolder'].'/minify/' );
            mkdir($this->config['cacheFolder'].'/minify', 0777);
        }

        if (!file_exists($this->config['cacheFolder'].'/translate/')){
            $this->debug( '     Create Folder: ' . $this->config['cacheFolder'].'/translate/' );
            mkdir($this->config['cacheFolder'].'/translate', 0777);
        }

        if (!file_exists($this->config['cacheFolder'].'/unify/')){
            $this->debug( '     Create Folder: ' . $this->config['cacheFolder'].'/unify/' );
            mkdir($this->config['cacheFolder'].'/unify', 0777);
        }

        if (!file_exists($this->config['cacheFolder'].'/compiled/')){
            $this->debug( '     Create Folder: ' . $this->config['cacheFolder'].'/compiled/' );
            mkdir($this->config['cacheFolder'].'/compiled', 0777);
        }
    }

    /**
     *
     */
    private function _translate()
    {
        $this->debug( 'FUNCTION: _translate' );

        $t1 = $this->config['tokens'][0];
        $t2 = $this->config['tokens'][1];
        $t3 = $this->config['tokens'][1][0].$this->config['tokens'][1][1];

        $pattern = ( isset($this->config['requireQuotes']) && $this->config['requireQuotes'] === false ) ?
            '/([\\\'\"]?)'.$t1.'(([^'.$t3.']+'.$t3.'?[^'.$t3.']*)*)'.$t2.'([\\\'\"]?)/' :
            '/([\\\'\"])'.$t1.'(([^'.$t3.']+'.$t3.'?[^'.$t3.']*)*)'.$t2.'([\\\'\"])/' ;

        $folder  = $this->config['cacheFolder'] . '/translate/';
        $this->fileTranslated = $folder . md5($this->fileUnified  .  $this->config['lang'] );

        if( !file_exists($this->fileTranslated) || filemtime($this->fileUnified) > filemtime($this->fileTranslated)  ){ //Nescessita atualizar o cache de tradução ?
            $data = '';

            $fp = fopen ( $this->fileUnified , 'r');
            while (!feof ($fp)) {
                $line = fgets($fp, 4096);
                $data .= preg_replace_callback( $pattern , array( &$this , '_inject' ), $line );
            }
            fclose ($fp);

            file_put_contents( $this->fileTranslated ,$data);
            unset($data);
        }
    }

    /**
     * @param $matches
     * @return string
     */
    private function _inject( $matches )
    {
        $this->debug( "    Match FOUND: " .  $matches[2]  );
        $pattern = '/'.$this->config['tokens'][2].'([a-z0-9\_]+)'.$this->config['tokens'][2].'/i';

        if(  $matches[1] !==  $matches[4] ) //Começa com aspas mais não termine com aspas
            $this->debug( "    Match WARRING: " .  $matches[0] .  " Different quotes " );

        if(preg_match( $pattern , $matches[2] )){ //Tem Argumentos ??
            preg_match_all( $pattern , $matches[2] , $vars , PREG_PATTERN_ORDER);

            $this->debug( "        Variables: " .  implode( ',' , $vars[1]) );

            $sVar  = 'array(\'';
            $sVar .= implode( '\',\'' , $vars[1]);
            $sVar .= '\')';

            return '<?php echo $this->trans(\''.preg_replace("/([^\\\])'/" , '$1\\\'' , preg_replace ($pattern, '%s', $matches[2] )).'\' , \''.str_replace('\'' , '\\\'' ,$matches[1]).'\' , \''.str_replace('\'' , '\\\'' ,$matches[4]).'\' , '.$sVar.' ); ?>';
        }
        else
            return '<?php echo $this->trans(\''.preg_replace("/([^\\\])'/" , '$1\\\'' ,$matches[2]).'\' , \''.str_replace('\'' , '\\\'' ,$matches[1]).'\' , \''.str_replace('\'' , '\\\'' ,$matches[4]).'\' ); ?>';
    }


    /**
     * @param $key
     * @param string $quoteStart
     * @param string $quoteEnd
     * @param array $vars
     * @return mixed|string
     */
    private function trans( $key  , $quoteStart = '' , $quoteEnd = ''  , array $vars = array())
    {
        $return = $this->providerClass->trans($key);

        if(!$return)
            $this->debug( "TRANS NOT FOUND: $key"  );

        $data = $return ? $return : $key ;

        if( $quoteStart == '\'' )
            $data = str_replace('\'' , '\\\'' , $data );

        $return = $quoteStart . $data  . $quoteEnd;

        if( !empty($vars) )        {
            $t1 = str_replace( '%' , '$tt$' , $this->config['wrapVarTokens'][0]);  //scape  vsprintf token
            $t2 = str_replace( '%' , '$tt$' , $this->config['wrapVarTokens'][1]);  //scape  vsprintf token

            if( $t1 === '+' || $t1 === '.' && $t1 === $t2){ //se usar operadores de concatenação adicionar as aspas
                $t1 = $quoteStart  . $t1;
                $t2 .= $quoteEnd ? $quoteEnd : $quoteStart;
            }

            $return =  str_replace( '$tt$' , '%' , vsprintf(str_replace( '%s' , $t1.'%s'.$t2 , $return) , $vars));
        }

        return $return;
    }

    /**
     * Processa os arquivos
     */
    private function _process( )
    {
        if( isset($this->config['YUICompressor']) && true == $this->config['YUICompressor'] )
            $this->_compress();

        $this->_unify();
        $this->_translate();

    }

    /**
     * Unifica todos os arquivos em um unico
     */
    private function _unify()
    {
        $this->debug( 'FUNCTION: _unify' );

        $name  = md5( json_encode( $this->files ) );
        $this->fileUnified  = $this->config['cacheFolder'] . '/unify/' .$name;

        $files = empty($this->filesCompressed) ?  $this->files : $this->filesCompressed;
        $needUpdate = true;

        if(file_exists($this->fileUnified)) {
            $needUpdate = false;

            foreach( $files as $i => $file ){
                if( filemtime($file) > filemtime($this->fileUnified) ){
                    $needUpdate = true;
                    $this->debug( "     File {$this->files[$i]} updated" );
                    unlink ( $this->fileUnified );
                    break;
                }
            }
        }

        if($needUpdate){
            foreach( $files as $i => $file ){
                $this->debug( "     File $file added to $this->fileUnified" );
                $data = $this->filesEncoding[$i] === 'UTF-8'  ?  file_get_contents($file) : mb_convert_encoding( file_get_contents($file) , 'UTF-8' , $this->filesEncoding[$i] ) ;
                file_put_contents( $this->fileUnified , $data . "\n" , FILE_APPEND );
            }
        }


        if(!file_exists($this->fileUnified) )
            die( 'ERROR ON CREATE '. $this->fileUnified );

    }

    /**
     * Comprime os arquivos
     */
    private function _compress()
    {
        $this->debug( 'FUNCTION: _compress' );

        $javaVersion = shell_exec("java -version 2>&1");

        if (strpos($javaVersion,'java version') === false){
            $this->debug( '     Java not installed' );
            $this->debug( '     YUICompressor Disabled ' );
            $this->config['YUICompressor'] = false;
            return;
        }

        $uiCompressor = __DIR__.'/lib/yuicompressor.jar';

        if( !file_exists($uiCompressor) ){
            $this->debug( '     Lib yuicompressor.jar not found' );
            $this->debug( '     YUICompressor Disabled ' );
            $this->config['YUICompressor'] = false;
            return;
        }

        $folder  = $this->config['cacheFolder'] . '/minify/';
        foreach( $this->files as $i => $file ){
            $fe = substr( $file , strrpos($file, '.') +1 );

            if ( $fe == 'css' || $fe == 'js'   ){ //Compactar arquivos css e javascript apenas
                $minFile = $folder . md5(str_replace('/','_',$file));

                $this->debug( '     Compress File: ' .$file .' to ' . $minFile );

                if( file_exists($minFile) && filemtime($minFile) > filemtime($file) ){ //A arquivo de cache esta atualizado ?
                    $this->filesCompressed[$i] = $minFile;
                    continue;
                }

                /*
                 * Line-break 2000 : Evita linhas muito grandes para previnir erros de backtracking do PCRE.
                 * disable-optimizations : Evita a remoção de aspas e concatenações pelo compressor.
                 */
                exec("java -jar $uiCompressor $file -o $minFile --charset {$this->filesEncoding[$i]}  --line-break 2000 --disable-optimizations ");
                $this->filesCompressed[$i] = file_exists( $minFile ) ? $minFile : $file;

                if( $this->filesCompressed[$i] !== $minFile )
                    $this->debug( '     The file can not be compressed: ' . $this->filesCompressed[$i]);
            }
            else{
                $this->filesCompressed[$i] = $file;
                $this->debug( '     File: ' .$file .' not compressed. Only .js and .css can be compressed' );
            }
        }
    }

    /**
     * @param $msg
     */
    private function debug( $msg  )
    {
        if( isset($this->config['debug']) && $this->config['debug'] === true && isset($this->config['debugFile']))
            file_put_contents( $this->config['debugFile'] , var_export( $msg , true ) . "\n", FILE_APPEND);
    }

}
