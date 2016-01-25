<h1>Registrar nueva empresa</h1>
<p>Aquí podrá registrar una nueva empresa para la cual usted será el usuario administrador de la misma.</p>

<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['id'=>'registrarContribuyente', 'onsubmit'=>'Form.check() && Form.checkSend()']);
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
    'name' => 'rut',
    'label' => 'RUT',
    'check' => 'notempty rut',
    'attr' => 'maxlength="12" onblur="Contribuyente.setDatos(\'registrarContribuyente\')"',
]);
echo $f->input([
    'name' => 'razon_social',
    'label' => 'Razón social',
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]);
echo $f->input([
    'name' => 'giro',
    'label' => 'Giro',
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'actividad_economica',
    'label' => 'Actividad económica',
    'help' => 'Indique la actividad económica principal de la empresa',
    'options' => [''=>'Seleccionar una actividad económica'] + $actividades_economicas,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'direccion',
    'label' => 'Dirección',
    'help' => 'Dirección casa matriz',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'comuna',
    'label' => 'Comuna',
    'help' => 'Comuna casa matriz',
    'options' => [''=>'Seleccionar una comuna'] + $comunas,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'telefono',
    'label' => 'Teléfono',
    'placeholder' => 'Ej: +56 9 88776655 ó +56 2 22334455',
    'check' => 'telephone',
    'attr' => 'maxlength="20"',
]);
echo $f->input([
    'name' => 'email',
    'label' => 'Email',
    'check' => 'email',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'name' => 'web',
    'label' => 'Web',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'name' => 'sucursal_sii',
    'label' => 'Sucursal SII',
    'help' => 'Código sucursal de la empresa entregado por el SII',
    'check' => 'integer',
    'attr' => 'maxlength="9"',
]);
?>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO LOGO -->
<div role="tabpanel" class="tab-pane" id="logo">
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
<!-- FIN LOGO -->

<!-- INICIO AMBIENTES -->
<div role="tabpanel" class="tab-pane" id="ambientes">
<?php
echo $f->input([
    'type' => 'date',
    'name' => 'resolucion_fecha',
    'label' => 'Fecha resolución',
    'help' => 'Fecha de la resolución que autoriza la emisión de DTE en ambiente de producción',
    'check' => 'date',
]);
echo $f->input([
    'name' => 'resolucion_numero',
    'label' => 'Número resolución',
    'help' => 'Número de la resolución que autoriza la emisión de DTE en ambiente de producción',
    'check' => 'integer',
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'certificacion',
    'label' => '¿En certificación?',
    'checked' => true,
    'help' => 'Si está seleccionado el sistema funcionará en ambiente de certificación',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'certificacion_resolucion',
    'label' => 'Resolución certificación',
    'help' => 'Fecha de la autorización para emisión de DTE en ambiente de certificación',
    'check' => 'notempty date',
]);
?>
</div>
<!-- FIN AMBIENTE -->

<!-- INICIO EMAILS -->
<div role="tabpanel" class="tab-pane" id="emails">
<p>Aquí debe configurar las dos casillas de correo para operar con facturación electrónica. Puede revisar la <a href="http://wiki.libredte.cl/doku.php/webapp/config/email">documentación de las casillas de correo</a> para obtener detalles de qué opciones debe usar.</p>
<h2>Email contacto SII</h2>
<?php
echo $f->input([
    'name' => 'sii_smtp',
    'label' => 'Servidor SMTP',
    'help' => 'Ejemplo: ssl://smtp.gmail.com:465',
    'attr' => 'maxlength="50"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'sii_imap',
    'label' => 'Mailbox IMAP',
    'help' => 'Ejemplo: {imap.gmail.com:993/imap/ssl}INBOX',
    'attr' => 'maxlength="100"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'sii_user',
    'label' => 'Email',
    'check' => 'email',
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
    'help' => 'Ejemplo: ssl://smtp.gmail.com:465',
    'attr' => 'maxlength="50"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'intercambio_imap',
    'label' => 'Mailbox IMAP',
    'help' => 'Ejemplo: {imap.gmail.com:993/imap/ssl}INBOX',
    'attr' => 'maxlength="100"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'intercambio_user',
    'label' => 'Email',
    'check' => 'email',
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
<p>LibreDTE puede comunicarse con la aplicación web de su empresa a través de servicios web. A continuación puede ingresar las URL para diferentes consultas que LibreDTE debería poder hacer a su aplicación. Puede revisar la <a href="http://wiki.libredte.cl/doku.php/sowerphp/integracion">documentación de la integración</a> para obtener detalles de las salidas esperadas para cada consulta.</p>
<?php
echo $f->input([
    'name' => 'api_token',
    'label' => 'Token',
    'help' => 'Token opcional para autenticación a través de <em>HTTP Basic Auth</em>. Se enviará al servicio el token como usuario y una X como contraseña.',
    'attr' => 'maxlength="255"',
]);
echo $f->input([
    'name' => 'api_items',
    'label' => 'Items',
    'help' => 'URL para consultar por GET los items a través de su código. Ejemplos: https://example.com/items/ o https://example.com/items?codigo=',
    'attr' => 'maxlength="255"',
]);
?>
</div>
<!-- FIN API -->

    </div>
</div>

<?php
echo $f->end('Registrar empresa');
