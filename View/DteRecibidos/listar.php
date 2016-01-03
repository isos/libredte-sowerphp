<h1>DTE recibidos</h1>
<p>Aquí podrá consultar todos los documentos recibidos por la empresa <?=$Emisor->razon_social?>.</p>

<div class="text-right">
    <a href="dte_recibidos/agregar" class="btn btn-default">
        <span class="fa fa-plus"></span>
        Agregar DTE recibido
    </a>
    <br/><br/>
</div>

<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="dte_recibidos/modificar/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Modificar DTE" class="btn btn-default'.($d['intercambio']?' disabled':'').'"><span class="fa fa-edit"></span></a>';
    $acciones .= ' <a href="dte_intercambios/pdf/'.$d['intercambio'].'" title="Descargar PDF del DTE" class="btn btn-default'.(!$d['intercambio']?' disabled':'').'" role="button"><span class="fa fa-file-pdf-o"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    unset($d['emisor'], $d['dte']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos'] + $tipos_dte, 'value'=>(isset($search['dte'])?$search['dte']:'')]),
    $f->input(['name'=>'folio', 'value'=>(isset($search['folio'])?$search['folio']:''), 'check'=>'integer']),
    $f->input(['name'=>'emisor', 'value'=>(isset($search['emisor'])?$search['emisor']:''), 'check'=>'integer', 'placeholder'=>'RUT sin dv']),
    $f->input(['type'=>'date', 'name'=>'fecha', 'value'=>(isset($search['fecha'])?$search['fecha']:''), 'check'=>'date']),
    $f->input(['name'=>'total', 'value'=>(isset($search['total'])?$search['total']:''), 'check'=>'integer']),
    $f->input(['name'=>'intercambio', 'value'=>(isset($search['intercambio'])?$search['intercambio']:''), 'check'=>'integer']),
    $f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos'] + $usuarios, 'value'=>(isset($search['usuario'])?$search['usuario']:'')]),
    '<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Emisor', 'Fecha', 'Total', 'Intercambio', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_recibidos',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_recibidos_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, null, 100]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
