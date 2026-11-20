<?php
require __DIR__.'/lib.php';
try{$pdo=db();$rows=$pdo->query('SELECT id,asunto,solicitante,categoria,prioridad,estado FROM tickets ORDER BY id DESC LIMIT 100')->fetchAll();$error='';}
catch(Throwable $e){$rows=[];$error='Servicio de datos no disponible';}
function h($v): string {return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mesa de ayuda</title><style>
body{font:16px system-ui,Arial,sans-serif;margin:0;background:#f5f7fb;color:#172235}
header{background:#172b4d;color:white;padding:24px 5%}main{max-width:1100px;margin:32px auto;padding:0 18px}
section{background:white;border:1px solid #dbe2ec;border-radius:12px;padding:22px;box-shadow:0 2px 8px #1722350a}
table{width:100%;border-collapse:collapse}th,td{padding:12px;text-align:left;border-bottom:1px solid #e4e9f0}th{background:#f0f4f9}
a{color:#1759a8}.hint{color:#566579}.scroll{overflow:auto}code{background:#e9eef6;padding:3px 6px;border-radius:4px}
</style></head><body><header><h1>Mesa de ayuda</h1><p>Empresa ficticia · Sistema operativo de laboratorio</p></header>
<main><section><h2>Registros actuales</h2><p class="hint">Los agentes modifican estos datos a través de la API REST. Actualiza la página para verificar resultados.</p>
<?php if($error): ?><p><?=h($error)?></p><?php endif;?>
<div class="scroll"><table><thead><tr><th>ID</th><th>Asunto</th><th>Solicitante</th><th>Categoría</th><th>Prioridad</th><th>Estado</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=h($r['id']??'')?></td><td><?=h($r['asunto']??'')?></td><td><?=h($r['solicitante']??'')?></td><td><?=h($r['categoria']??'')?></td><td><?=h($r['prioridad']??'')?></td><td><?=h($r['estado']??'')?></td></tr><?php endforeach;?>
</tbody></table></div></section><p class="hint">API: <a href="/api/tickets">Consultar JSON</a> · <a href="/api/auditoria">Auditoría</a></p></main></body></html>