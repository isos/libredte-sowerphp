<h1>Bandeja de intercambio entre contribuyentes</h1>
<p>Aquí podrá revisar, aceptar o rechazar aquellos documentos que otros contribuyentes han envíado a <?=$Emisor->razon_social?> de manera electrónica.</p>

<div class="text-right">
    <a href="dte_intercambios/actualizar" class="btn btn-default">
        <span class="fa fa-refresh"></span>
        Actualizar bandeja de intercambio: <?=$Emisor->intercambio_user?>
    </a>
    <br/><br/>
</div>

<?php
foreach ($intercambios as &$i) {
    $acciones = '<a href="dte_intercambios/ver/'.$i['codigo'].'" title="Ver detalles del intercambio"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_intercambios/pdf/'.$i['codigo'].'" title="Descargar PDF del intercambio"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $i[] = $acciones;
    if (is_numeric($i['emisor']))
        $i['emisor'] = \sowerphp\app\Utility_Rut::addDV($i['emisor']);
}
array_unshift($intercambios, ['Código', 'Recibido', 'Email', 'Emisor', 'Documentos', 'Estado', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, 100]);
echo $t->generate($intercambios);
