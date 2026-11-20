<?php
require __DIR__.'/../lib.php';
try {
 $pdo=db();$path=requestPath();$method=$_SERVER['REQUEST_METHOD'];
 if($path==='/health'&&$method==='GET'){$pdo->query('SELECT 1');respond(200,['estado'=>'ok','sistema'=>'academico']);}
 if($path==='/usuarios'&&$method==='GET')respond(200,['usuarios'=>$pdo->query('SELECT id,nombre,correo,cuenta_estado,rol FROM usuarios ORDER BY id')->fetchAll()]);
 if($path==='/servicios'&&$method==='GET')respond(200,['servicios'=>$pdo->query('SELECT * FROM servicios ORDER BY id')->fetchAll()]);
 if($path==='/auditoria'&&$method==='GET')respond(200,['auditoria'=>$pdo->query('SELECT * FROM auditoria ORDER BY id DESC LIMIT 100')->fetchAll()]);
 if(preg_match('#^/usuarios/([1-9][0-9]*)$#',$path,$m)&&$method==='PATCH'){
  authorizeWrite();$d=body();
  if(array_keys($d)!==['cuenta_estado'])respond(422,['error'=>'solo_cuenta_estado']);
  validEnum($d,'cuenta_estado',['activa','bloqueada']);
  $id=(int)$m[1];$pdo->beginTransaction();
  $s=$pdo->prepare('SELECT cuenta_estado FROM usuarios WHERE id=? FOR UPDATE');$s->execute([$id]);$prev=$s->fetch();
  if(!$prev){$pdo->rollBack();respond(404,['error'=>'usuario_no_encontrado']);}
  $s=$pdo->prepare('UPDATE usuarios SET cuenta_estado=? WHERE id=?');$s->execute([$d['cuenta_estado'],$id]);
  audit($pdo,'cambiar_estado_cuenta',$id,['anterior'=>$prev['cuenta_estado'],'nuevo'=>$d['cuenta_estado']]);
  $pdo->commit();respond(200,['id'=>$id,'cuenta_estado'=>$d['cuenta_estado']]);
 }
 respond(404,['error'=>'ruta_no_encontrada']);
} catch(PDOException $e){respond(503,['error'=>'base_de_datos_no_disponible']);}
