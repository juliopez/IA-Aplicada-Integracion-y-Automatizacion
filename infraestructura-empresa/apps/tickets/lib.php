<?php
declare(strict_types=1);
function db(): PDO {
 static $pdo=null;
 if ($pdo === null) {
  $dsn='mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME').';charset=utf8mb4';
  $pdo=new PDO($dsn,(string)getenv('DB_USER'),(string)getenv('DB_PASSWORD'),[
   PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
   PDO::ATTR_EMULATE_PREPARES=>false
  ]);
 }
 return $pdo;
}
function respond(int $status, array $data): never {
 http_response_code($status);
 header('Content-Type: application/json; charset=utf-8');
 header('Cache-Control: no-store');
 echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
 exit;
}
function body(): array {
 $raw=file_get_contents('php://input');
 if(strlen($raw)>10000) respond(413,['error'=>'cuerpo_demasiado_grande']);
 try {$data=json_decode($raw,true,32,JSON_THROW_ON_ERROR);}
 catch(Throwable $e){respond(400,['error'=>'json_invalido']);}
 if(!is_array($data)||array_is_list($data)) respond(400,['error'=>'objeto_json_requerido']);
 return $data;
}
function authorizeWrite(): void {
 $given=(string)($_SERVER['HTTP_X_API_KEY']??'');
 $expected=(string)getenv('API_KEY');
 if($expected===''||$given===''||!hash_equals($expected,$given)) respond(403,['error'=>'token_requerido']);
}
function validEnum(array $data,string $field,array $allowed): void {
 if(isset($data[$field])&&!in_array($data[$field],$allowed,true)) respond(422,['error'=>'valor_invalido','campo'=>$field]);
}
function audit(PDO $pdo,string $op,?int $id,array $detail): void {
 $s=$pdo->prepare('INSERT INTO auditoria(operacion,entidad_id,detalle) VALUES (?,?,?)');
 $s->execute([$op,$id,json_encode($detail,JSON_UNESCAPED_UNICODE)]);
}
function requestPath(): string {
 $uri=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
 return preg_replace('#^/api/?#','/',(string)$uri);
}
