<h1>Modificar empresa <?=$Contribuyente->razon_social?></h1>
<p>Aquí podrá modificar los datos de la empresa <?=$Contribuyente->razon_social?> RUT <?=num($Contribuyente->rut).'-'.$Contribuyente->dv?>, para la cual usted es el usuario administrador.</p>

<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend()']);
?>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#logo" aria-controls="logo" role="tab" data-toggle="tab">Logo</a></li>
        <li role="presentation"><a href="#ambientes" aria-controls="ambientes" role="tab" data-toggle="tab">Ambientes: producción y certificación</a></li>
        <li role="presentation"><a href="#emails" aria-controls="emails" role="tab" data-toggle="tab">Emails</a></li>
        <li role="presentation"><a href="#api" aria-controls="api" role="tab" data-toggle="tab">API</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
<?php
echo $f->input([
    'name' => 'razon_social',
    'label' => 'Razón social',
    'value' => $Contribuyente->razon_social,
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]);
echo $f->input([
    'name' => 'giro',
    'label' => 'Giro',
    'value' => $Contribuyente->giro,
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'actividad_economica',
    'label' => 'Actividad económica',
    'value' => $Contribuyente->actividad_economica,
    'help' => 'Indique la actividad económica principal de la empresa',
    'options' => [''=>'Seleccionar una actividad económica'] + $actividades_economicas,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'direccion',
    'label' => 'Dirección',
    'value' => $Contribuyente->direccion,
    'help' => 'Dirección casa matriz',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'comuna',
    'label' => 'Comuna',
    'value' => $Contribuyente->comuna,
    'help' => 'Comuna casa matriz',
    'options' => [''=>'Seleccionar una comuna'] + $comunas,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'telefono',
    'label' => 'Teléfono',
    'value' => $Contribuyente->telefono,
    'placeholder' => 'Ej: +56 9 88776655 ó +56 2 22334455',
    'check' => 'telephone',
    'attr' => 'maxlength="20"',
]);
echo $f->input([
    'name' => 'email',
    'label' => 'Email',
    'value' => $Contribuyente->email,
    'check' => 'email',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'name' => 'web',
    'label' => 'Web',
    'value' => $Contribuyente->web,
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'name' => 'sucursal_sii',
    'label' => 'Sucursal SII',
    'value' => $Contribuyente->sucursal_sii,
    'help' => 'Código sucursal de la empresa entregado por el SII',
    'check' => 'integer',
    'attr' => 'maxlength="9"',
]);
?>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO LOGO -->
<div role="tabpanel" class="tab-pane" id="logo">
    <div class="row">
        <div class="col-md-8">
<?php
echo $f->input([
    'type' => 'file',
    'name' => 'logo',
    'label' => 'Logo',
    'help' => 'Imagen en formato PNG con el logo de la empresa',
    'attr' => 'accept="image/png"',
]);
?>
        </div>
        <div class="col-md-4">
            <img src="../logo/<?=$Contribuyente->rut?>.png" alt="Logo <?=$Contribuyente->razon_social?>" class="responsive thumbnail center" />
        </div>
    </div>
</div>
<!-- FIN LOGO -->

<!-- INICIO AMBIENTES -->
<div role="tabpanel" class="tab-pane" id="ambientes">
<?php
echo $f->input([
    'type' => 'date',
    'name' => 'resolucion_fecha',
    'label' => 'Fecha resolución',
    'value' => $Contribuyente->resolucion_fecha,
    'help' => 'Fecha de la resolución que autoriza la emisión de DTE en ambiente de producción',
    'check' => 'date',
]);
echo $f->input([
    'name' => 'resolucion_numero',
    'label' => 'Número resolución',
    'value' => $Contribuyente->resolucion_numero,
    'help' => 'Número de la resolución que autoriza la emisión de DTE en ambiente de producción',
    'check' => 'integer',
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'certificacion',
    'label' => '¿En certificación?',
    'checked' => $Contribuyente->certificacion,
    'help' => 'Si está seleccionado todo el sistema funcionará en ambiente de certificación',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'certificacion_resolucion',
    'label' => 'Resolución certificación',
    'value' => $Contribuyente->certificacion_resolucion,
    'help' => 'Fecha de la autorización para emisión de DTE en ambiente de certificación',
    'check' => 'notempty date',
]);
?>
</div>
<!-- FIN AMBIENTES -->

<!-- INICIO EMAILS -->
<div role="tabpanel" class="tab-pane" id="emails">
<h2>Email contacto SII</h2>
<?php
echo $f->input([
    'name' => 'sii_smtp',
    'label' => 'Servidor SMTP',
    'value' => $Contribuyente->sii_smtp,
    'help' => 'Ejemplo: ssl://smtp.gmail.com:465',
    'attr' => 'maxlength="50"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'sii_imap',
    'label' => 'Mailbox IMAP',
    'value' => $Contribuyente->sii_imap,
    'help' => 'Ejemplo: {imap.gmail.com:993/imap/ssl}INBOX',
    'attr' => 'maxlength="100"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'sii_user',
    'label' => 'Email',
    'check' => 'email',
    'value' => $Contribuyente->sii_user,
    'attr' => 'maxlength="50"',
    'check' => 'notempty email',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'sii_pass',
    'label' => 'Contraseña',
]);
?>
<h2>Email intercambio</h2>
<?php
echo $f->input([
    'name' => 'intercambio_smtp',
    'label' => 'Servidor SMTP',
    'value' => $Contribuyente->intercambio_smtp,
    'help' => 'Ejemplo: ssl://smtp.gmail.com:465',
    'attr' => 'maxlength="50"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'intercambio_imap',
    'label' => 'Mailbox IMAP',
    'value' => $Contribuyente->intercambio_imap,
    'help' => 'Ejemplo: {imap.gmail.com:993/imap/ssl}INBOX',
    'attr' => 'maxlength="100"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'intercambio_user',
    'label' => 'Email',
    'check' => 'email',
    'value' => $Contribuyente->intercambio_user,
    'attr' => 'maxlength="50"',
    'check' => 'notempty email',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'intercambio_pass',
    'label' => 'Contraseña',
]);
?>
</div>
<!-- FIN EMAILS -->

<!-- INICIO API -->
<div role="tabpanel" class="tab-pane" id="api">
<p>LibreDTE puede comunicarse con la aplicación web de su empresa a través de servicios web. A continuación puede ingresar las URL para diferentes consultas que LibreDTE debería poder hacer a su aplicación.</p>
<?php
echo $f->input([
    'name' => 'api_token',
    'label' => 'Token',
    'value' => \website\Dte\Utility_Data::decrypt($Contribuyente->api_token),
    'help' => 'Token opcional para autenticación a través de <em>HTTP Basic Auth</em>. Se enviará al servicio el token como usuario y una X como contraseña.',
]);
echo $f->input([
    'name' => 'api_items',
    'label' => 'Items',
    'value' => $Contribuyente->api_items,
    'help' => 'URL para consultar por GET los items a través de su código. Ejemplos: https://example.com/api/items/ o https://example.com/api/items?codigo='
]);
?>
</div>
<!-- FIN API -->

    </div>
</div>

<?php
echo $f->end('Modificar empresa');
