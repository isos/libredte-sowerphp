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
array_unshift($documentos, ['Documento', 'Folio', 'Emisor', 'Fecha', 'Total', 'Intercambio', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, 100]);
echo $t->generate($documentos);
