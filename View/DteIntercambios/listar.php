<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/actualizar">
            <span class="fa fa-refresh"></span>
            Actualizar bandeja de intercambio
        </a>
    </li>
</ul>

<h1>Bandeja de intercambio entre contribuyentes</h1>
<p>Aquí podrá revisar, aceptar o rechazar aquellos documentos que otros contribuyentes han envíado a <?=$Emisor->razon_social?> de manera electrónica.</p>

<?php
foreach ($intercambios as &$i) {
    $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$i['codigo'].'" title="Ver detalles del intercambio"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$i['codigo'].'" title="Descargar PDF del intercambio"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $i[] = $acciones;
    if (is_numeric($i['emisor'])) {
        $i['emisor'] = \sowerphp\app\Utility_Rut::addDV($i['emisor']);
    }
}
array_unshift($intercambios, ['Código', 'Emisor', 'Firmado', 'Recibido', 'Documentos', 'Estado', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, 100]);
$t->setId('intercambios');
echo $t->generate($intercambios);
?>
<link rel="stylesheet" type="text/css" href="<?=$_base?>/css/jquery.dataTables.css" />
<script type="text/javascript" src="<?=$_base?>/js/jquery.dataTables.js"></script>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#intercambios"); }); </script>
