<h1>DTE emitidos</h1>
<p>Aquí podrá consultar todos los documentos emitidos por la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver DTE"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="pdf/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del DTE"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    unset($d['dte']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos'] + $tipos_dte, 'value'=>(isset($search['dte'])?$search['dte']:'')]),
    $f->input(['name'=>'folio', 'value'=>(isset($search['folio'])?$search['folio']:''), 'check'=>'integer']),
    $f->input(['name'=>'receptor', 'value'=>(isset($search['receptor'])?$search['receptor']:''), 'check'=>'integer', 'placeholder'=>'RUT sin dv']),
    $f->input(['type'=>'date', 'name'=>'fecha', 'value'=>(isset($search['fecha'])?$search['fecha']:''), 'check'=>'date']),
    $f->input(['name'=>'total', 'value'=>(isset($search['total'])?$search['total']:''), 'check'=>'integer']),
    '',
    '',
    $f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos'] + $usuarios, 'value'=>(isset($search['usuario'])?$search['usuario']:'')]),
    '<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Intercambio', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_emitidos',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_emitidos_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, null, null, 100]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
