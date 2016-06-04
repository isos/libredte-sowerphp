<h1>Configuración de respaldos automáticos en Dropbox</h1>
<?php if (!$Emisor->config_respaldos_dropbox) : ?>
<p>Aquí podrá conectar LibreDTE con Dropbox para que se realicen respalos automáticos del contribuyente <?=$Emisor->razon_social?>.</p>
<div class="row">
    <div class="col-md-6">
        <a class="btn btn-primary btn-lg btn-block" href="https://db.tt/328o5XBy" role="button" target="_blank">
            Crear cuenta en Dropbox
        </a>
    </div>
    <div class="col-md-6">
        <a class="btn btn-success btn-lg btn-block" href="<?=$authorizeUrl?>" role="button">
            Conectar LibreDTE con Dropbox
        </a>
    </div>
</div>
<?php else: ?>
<p>Usted tiene conectado el contribuyente <?=$Emisor->razon_social?> de LibreDTE con su cuenta de Dropbox, esto significa que se realizarán respaldos automáticos de los datos de su empresa.</p>
<div class="row">
    <div class="col-md-3 text-center">
        <span class="fa fa-user" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=$accountInfo['display_name']?></span>
    </div>
    <div class="col-md-3 text-center">
        <span class="fa fa-envelope" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=$accountInfo['email']?></span>
    </div>
    <div class="col-md-3 text-center">
        <span class="fa fa-globe" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=$accountInfo['country']?> / <?=$accountInfo['locale']?></span>
    </div>
    <div class="col-md-3 text-center">
        <span class="fa fa-database" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=num($accountInfo['quota_info']['normal']/1024/1024/1024,1)?> / <?=num($accountInfo['quota_info']['quota']/1024/1024/1024,1)?> GB</span>
    </div>
</div>
<br/>
<?php $uso = round(($accountInfo['quota_info']['normal']/$accountInfo['quota_info']['quota'])*100);?>
<div class="progress">
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?=$uso?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$uso?>%;">
        <?=$uso?>%
    </div>
</div>
<br/>
<a class="btn btn-danger btn-lg btn-block" href="dropbox/desconectar" role="button">
    Desconectar LibreDTE de Dropbox
</a>
<?php endif; ?>
