<?php
require __DIR__.'/../lib.php';
try {
 $pdo=db(); $path=requestPath(); $method=$_SERVER['REQUEST_METHOD'];
 if($path==='/health' && $method==='GET') { $pdo->query('SELECT 1'); respond(200,['estado'=>'ok','sistema'=>'mesa_ayuda']); }
 if($path==='/tickets' && $method==='GET') respond(200,['tickets'=>$pdo->query('SELECT * FROM tickets ORDER BY id DESC LIMIT 100')->fetchAll()]);
 if(preg_match('#^/tickets/([1-9][0-9]*)$#',$path,$m) && $method==='GET') {
  $s=$pdo->prepare('SELECT * FROM tickets WHERE id=?'); $s->execute([(int)$m[1]]);
  $r=$s->fetch(); if(!$r) respond(404,['error'=>'ticket_no_encontrado']); respond(200,['ticket'=>$r]);
 }
 if($path==='/auditoria' && $method==='GET') respond(200,['auditoria'=>$pdo->query('SELECT * FROM auditoria ORDER BY id DESC LIMIT 100')->fetchAll()]);
 if($path==='/tickets' && $method==='POST') {
  authorizeWrite(); $d=body();
  foreach(['asunto','descripcion','solicitante'] as $f) if(!isset($d[$f])||!is_string($d[$f])||trim($d[$f])==='') respond(422,['error'=>'campo_obligatorio','campo'=>$f]);
  if(strlen($d['asunto'])>180||strlen($d['solicitante'])>120||strlen($d['descripcion'])>5000) respond(422,['error'=>'longitud_excedida']);
  validEnum($d,'categoria',['acceso','conectividad','aplicacion','otro']);
  validEnum($d,'prioridad',['baja','media','alta','critica']);
  $pdo->beginTransaction();
  $s=$pdo->prepare('INSERT INTO tickets(asunto,descripcion,solicitante,categoria,prioridad) VALUES (?,?,?,?,?)');
  $s->execute([$d['asunto'],$d['descripcion'],$d['solicitante'],$d['categoria']??'otro',$d['prioridad']??'media']);
  $id=(int)$pdo->lastInsertId(); audit($pdo,'crear_ticket',$id,['asunto'=>$d['asunto']]); $pdo->commit();
  respond(201,['id'=>$id,'estado'=>'creado']);
 }
 if(preg_match('#^/tickets/([1-9][0-9]*)$#',$path,$m) && $method==='PATCH') {
  authorizeWrite(); $d=body(); $allowed=['estado','prioridad','categoria'];
  if(!$d||array_diff(array_keys($d),$allowed)) respond(422,['error'=>'campos_no_permitidos']);
  validEnum($d,'estado',['abierto','en_proceso','resuelto','cerrado']);
  validEnum($d,'prioridad',['baja','media','alta','critica']);
  validEnum($d,'categoria',['acceso','conectividad','aplicacion','otro']);
  $id=(int)$m[1]; $pdo->beginTransaction();
  $s=$pdo->prepare('SELECT id FROM tickets WHERE id=? FOR UPDATE');$s->execute([$id]);
  if(!$s->fetch()){$pdo->rollBack();respond(404,['error'=>'ticket_no_encontrado']);}
  $set=implode(',',array_map(fn($k)=>"$k=?",array_keys($d)));
  $s=$pdo->prepare("UPDATE tickets SET $set WHERE id=?");$s->execute([...array_values($d),$id]);
  audit($pdo,'actualizar_ticket',$id,$d);$pdo->commit();
  respond(200,['id'=>$id,'estado'=>'actualizado']);
 }
 respond(404,['error'=>'ruta_no_encontrada']);
} catch(PDOException $e) {respond(503,['error'=>'base_de_datos_no_disponible']);}
