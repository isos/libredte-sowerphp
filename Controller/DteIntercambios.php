<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 */

// namespace del controlador
namespace website\Dte;

/**
 * Controlador para intercambio entre contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-10-08
 */
class Controller_DteIntercambios extends \Controller_App
{

    /**
     * Acción para mostrar la bandeja de intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'intercambios' => $Emisor->getIntercambios(),
        ]);
    }

    /**
     * Acción que muestra la página de un intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function ver($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // asignar variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'DteIntercambio' => $DteIntercambio,
            'EnvioDte' => $DteIntercambio->getEnvioDte(),
            'Documentos' => $DteIntercambio->getDocumentos(),
            'Firma' => $Firma,
        ]);
    }

    /**
     * Acción que muestra el mensaje del email de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-08
     */
    public function html($codigo)
    {
        $Emisor = $this->getContribuyente();
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        $this->layout = null;
        $this->set([
            'html' => $DteIntercambio->mensaje_html ? base64_decode($DteIntercambio->mensaje_html) : 'No hay mensaje HTML',
        ]);
    }

    /**
     * Acción para actualizar la bandeja de intercambio. Guarda los DTEs
     * recibidos por intercambio y guarda los acuses de recibos de DTEs
     * enviados a otros contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-04
     */
    public function actualizar()
    {
        $Emisor = $this->getContribuyente();
        // conectar a la casilla de intercambio por IMAP
        $Imap = $Emisor->getEmailImap();
        if (!$Imap) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible conectar mediante IMAP a '.$Emisor->intercambio_imap.', verificar mailbox, usuario y/o contraseña de correo de intercambio:<br/>'.implode('<br/>', imap_errors()), 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // obtener mensajes sin leer
        $uids = $Imap->search();
        if (!$uids) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay documentos nuevos que procesar'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // procesar cada mensaje sin leer
        $n_EnvioDTE = $n_acuse = $n_EnvioRecibos = $n_RecepcionEnvio = $n_ResultadoDTE = 0;
        foreach ($uids as &$uid) {
            $m = $Imap->getMessage($uid, ['subtype'=>['PLAIN', 'HTML', 'XML'], 'extension'=>['xml']]);
            if ($m and isset($m['attachments'][0])) {
                $datos_email = [
                    'fecha_hora_email' => date('Y-m-d H:i:s', strtotime($m['header']->date)),
                    'asunto' => substr($m['header']->subject, 0, 100),
                    'de' => substr($m['header']->from[0]->mailbox.'@'.$m['header']->from[0]->host, 0, 80),
                    'mensaje' => $m['body']['plain'] ? base64_encode($m['body']['plain']) : null,
                    'mensaje_html' => $m['body']['html'] ? base64_encode($m['body']['html']) : null,
                ];
                if (isset($m['header']->reply_to[0])) {
                    $datos_email['responder_a'] = substr($m['header']->reply_to[0]->mailbox.'@'.$m['header']->reply_to[0]->host, 0, 80);
                }
                $acuseContado = false;
                foreach ($m['attachments'] as $file) {
                    if ($this->procesar_EnvioDTE($Emisor->rut, $datos_email, $file))
                        $n_EnvioDTE++;
                    else if ($this->procesar_EnvioRecibos($Emisor, $datos_email, $file)) {
                        $n_EnvioRecibos++;
                        if (!$acuseContado) {
                            $acuseContado = true;
                            $n_acuse++;
                        }
                    } else if ($this->procesar_RecepcionEnvio($Emisor, $datos_email, $file)) {
                        $n_RecepcionEnvio++;
                        if (!$acuseContado) {
                            $acuseContado = true;
                            $n_acuse++;
                        }
                    } else if ($this->procesar_ResultadoDTE($Emisor, $datos_email, $file)) {
                        $n_ResultadoDTE++;
                        if (!$acuseContado) {
                            $acuseContado = true;
                            $n_acuse++;
                        }
                    }
                }
                // marcar email como leído
                $Imap->setSeen($uid);
            }
        }
        $n_uids = count($uids);
        if ($n_uids>1)
            $encontrados = 'Se encontraron '.num($n_uids).' correos';
        else
            $encontrados = 'Se encontró '.num($n_uids).' correo';
        $omitidos = $n_uids - $n_EnvioDTE - $n_acuse;
        \sowerphp\core\Model_Datasource_Session::message(
            $encontrados.': EnvioDTE='.num($n_EnvioDTE).',  EnvioRecibos='.num($n_EnvioRecibos).', RecepcionEnvio='.num($n_RecepcionEnvio).', ResultadoDTE='.num($n_ResultadoDTE).' y Omitidos='.num($omitidos), 'ok'
        );
        $this->redirect('/dte/dte_intercambios');
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param receptor RUT del receptor sin puntos ni dígito verificador
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-08
     */
    private function procesar_EnvioDTE($receptor, array $datos_email, array $file)
    {
        // preparar datos
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML($file['data']);
        if (!$EnvioDte->getID())
            return null;
        $caratula = $EnvioDte->getCaratula();
        if (!isset($caratula['SubTotDTE'][0]))
            $caratula['SubTotDTE'] = [$caratula['SubTotDTE']];
        $documentos = 0;
        foreach($caratula['SubTotDTE'] as $SubTotDTE) {
            $documentos += $SubTotDTE['NroDTE'];
        }
        $datos_enviodte = [
            'certificacion' => (int)(bool)!$caratula['NroResol'],
            'emisor' => substr($caratula['RutEmisor'], 0, -2),
            'fecha_hora_firma' => date('Y-m-d H:i:s', strtotime($caratula['TmstFirmaEnv'])),
            'documentos' => $documentos,
            'archivo' => $file['name'],
            'archivo_xml' => base64_encode($file['data']),
        ];
        $datos_enviodte['archivo_md5'] = md5($datos_enviodte['archivo_xml']);
        // guardar envío de intercambio
        $DteIntercambio = new Model_DteIntercambio();
        $DteIntercambio->set($datos_email + $datos_enviodte);
        $DteIntercambio->receptor = $receptor;
        return $DteIntercambio->save();
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param Emisor Objeto del emisor del documento que se espera
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    private function procesar_EnvioRecibos($Emisor, array $datos_email, array $file)
    {
        return (new Model_DteIntercambioRecibo())->saveXML($Emisor, $file['data']);
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param Emisor Objeto del emisor del documento que se espera
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    private function procesar_RecepcionEnvio($Emisor, array $datos_email, array $file)
    {
        return (new Model_DteIntercambioRecepcion())->saveXML($Emisor, $file['data']);
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param Emisor Objeto del emisor del documento que se espera
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    private function procesar_ResultadoDTE($Emisor, array $datos_email, array $file)
    {
        return (new Model_DteIntercambioResultado())->saveXML($Emisor, $file['data']);
    }

    /**
     * Acción para mostrar el PDF de un EnvioDTE de un intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function pdf($codigo, $cedible = false)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $data = [
            'xml' => $DteIntercambio->archivo_xml,
            'cedible' => $cedible,
            'compress' => $DteIntercambio->documentos == 1 ? false : true,
        ];
        // si hay un logo para la empresa se usa
        $logo = \sowerphp\core\Configure::read('dte.logos.dir').'/'.$DteIntercambio->emisor.'.png';
        if (is_readable($logo)) {
            $data['logo'] = base64_encode(file_get_contents($logo));
        }
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : $this->token);
        $response = $rest->post($this->request->url.'/api/dte/documentos/generar_pdf', $data);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            return;
        }
        // si dió código 200 se entrega la respuesta del servicio web
        foreach (['Content-Disposition', 'Content-Length', 'Content-Type'] as $header) {
            if (isset($response['header'][$header]))
                header($header.': '.$response['header'][$header]);
        }
        echo $response['body'];
        exit;
    }

    /**
     * Acción que descarga el XML del documento intercambiado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function xml($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // entregar XML
        $xml = base64_decode($DteIntercambio->archivo_xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$DteIntercambio->archivo.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que procesa y responde al intercambio recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-08
     */
    public function responder($codigo)
    {
        $Emisor = $this->getContribuyente();
        // si no se viene por post error
        if (!isset($_POST['submit'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder de forma directa a '.$this->request->request, 'error'
            );
            $this->redirect(str_replace('responder', 'ver', $this->request->request));
        }
        // obtener DTE emitido
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        //
        // construir RecepcionDTE
        //
        $RecepcionDTE = [];
        $n_dtes = count($_POST['TipoDTE']);
        for ($i=0; $i<$n_dtes; $i++) {
            $RecepcionDTE[] = [
                'TipoDTE' => $_POST['TipoDTE'][$i],
                'Folio' => $_POST['Folio'][$i],
                'FchEmis' => $_POST['FchEmis'][$i],
                'RUTEmisor' => $_POST['RUTEmisor'][$i],
                'RUTRecep' => $_POST['RUTRecep'][$i],
                'MntTotal' => $_POST['MntTotal'][$i],
                'EstadoRecepDTE' => $_POST['EstadoRecepDTE'][$i],
                'RecepDTEGlosa' => $_POST['RecepDTEGlosa'][$i],
            ];
        }
        // armar respuesta de envío
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(base64_decode($DteIntercambio->archivo_xml));
        $Caratula = $EnvioDte->getCaratula();
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        $RespuestaEnvio->agregarRespuestaEnvio([
            'NmbEnvio' => $DteIntercambio->archivo,
            'CodEnvio' => $DteIntercambio->codigo,
            'EnvioDTEID' => $EnvioDte->getID(),
            'Digest' => $EnvioDte->getDigest(),
            'RutEmisor' => $EnvioDte->getEmisor(),
            'RutReceptor' => $EnvioDte->getReceptor(),
            'EstadoRecepEnv' => $_POST['EstadoRecepEnv'],
            'RecepEnvGlosa' => $_POST['RecepEnvGlosa'],
            'NroDTE' => count($RecepcionDTE),
            'RecepcionDTE' => $RecepcionDTE,
        ]);
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula([
            'RutResponde' => $Emisor->rut.'-'.$Emisor->dv,
            'RutRecibe' => $Caratula['RutEmisor'],
            'IdRespuesta' => $DteIntercambio->codigo,
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
        ]);
        $RespuestaEnvio->setFirma($Firma);
        // generar y validar XML
        $RecepcionDTE_xml = $RespuestaEnvio->generar();
        if (!$RespuestaEnvio->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar RecepcionDTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('responder', 'ver', $this->request->request));
        }
        //
        // generar EnvioRecibos
        //
        $EnvioRecibos = new \sasco\LibreDTE\Sii\EnvioRecibos();
        $EnvioRecibos->setCaratula([
            'RutResponde' => $Emisor->rut.'-'.$Emisor->dv,
            'RutRecibe' => $Caratula['RutEmisor'],
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
        ]);
        $EnvioRecibos->setFirma($Firma);
        // procesar cada DTE
        $EnvioRecibos_r = [];
        for ($i=0; $i<$n_dtes; $i++) {
            if ($_POST['acuse'][$i]) {
                $EnvioRecibos->agregar([
                    'TipoDoc' => $_POST['TipoDTE'][$i],
                    'Folio' => $_POST['Folio'][$i],
                    'FchEmis' => $_POST['FchEmis'][$i],
                    'RUTEmisor' => $_POST['RUTEmisor'][$i],
                    'RUTRecep' => $_POST['RUTRecep'][$i],
                    'MntTotal' => $_POST['MntTotal'][$i],
                    'Recinto' => $_POST['Recinto'],
                    'RutFirma' => $Firma->getID(),
                ]);
                $EnvioRecibos_r[] = 'T'.$_POST['TipoDTE'][$i].'F'.$_POST['Folio'][$i];
            }
        }
        // generar y validar XML
        if ($EnvioRecibos_r) {
            $EnvioRecibos_xml = $EnvioRecibos->generar();
            if (!$EnvioRecibos->schemaValidate()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible generar EnvioRecibos.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                $this->redirect(str_replace('responder', 'ver', $this->request->request));
            }
        }
        //
        // generar ResultadoDTE
        //
        // objeto para la respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        // procesar cada DTE
        for ($i=0; $i<$n_dtes; $i++) {
            $estado = !$_POST['EstadoRecepDTE'][$i] ? 0 : 2;
            $RespuestaEnvio->agregarRespuestaDocumento([
                'TipoDTE' => $_POST['TipoDTE'][$i],
                'Folio' => $_POST['Folio'][$i],
                'FchEmis' => $_POST['FchEmis'][$i],
                'RUTEmisor' => $_POST['RUTEmisor'][$i],
                'RUTRecep' => $_POST['RUTRecep'][$i],
                'MntTotal' => $_POST['MntTotal'][$i],
                'CodEnvio' => $i+1,
                'EstadoDTE' => $estado,
                'EstadoDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$estado],
            ]);
        }
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula([
            'RutResponde' => $Emisor->rut.'-'.$Emisor->dv,
            'RutRecibe' => $Caratula['RutEmisor'],
            'IdRespuesta' => $DteIntercambio->codigo,
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
        ]);
        $RespuestaEnvio->setFirma($Firma);
        // generar y validar XML
        $ResultadoDTE_xml = $RespuestaEnvio->generar();
        if (!$RespuestaEnvio->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar ResultadoDTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('responder', 'ver', $this->request->request));
        }
        //
        // guardar estado del intercambio y usuario que lo procesó
        //
        $DteIntercambio->estado = (int)$_POST['EstadoRecepEnv'];
        $DteIntercambio->recepcion_xml = base64_encode($RecepcionDTE_xml);
        if (isset($EnvioRecibos_xml))
            $DteIntercambio->recibos_xml = base64_encode($EnvioRecibos_xml);
        $DteIntercambio->resultado_xml = base64_encode($ResultadoDTE_xml);
        $DteIntercambio->fecha_hora_respuesta = date('Y-m-d H:i:s');
        $DteIntercambio->usuario = $this->Auth->User->id;
        $DteIntercambio->save();
        //
        // guardar documentos que han sido aceptados con acuse de recibo
        //
        if (isset($EnvioRecibos_xml)) {
            // actualizar datos del emisor si no tine usuario asociado
            $EmisorIntercambio = $DteIntercambio->getEmisor();
            if (!$EmisorIntercambio->usuario) {
                $emisor = $DteIntercambio->getDocumentos()[0]->getDatos()['Encabezado']['Emisor'];
                $EmisorIntercambio->razon_social = $emisor['RznSoc'];
                if (!empty($emisor['GiroEmis']))
                    $EmisorIntercambio->giro = $emisor['GiroEmis'];
                if (!empty($emisor['CorreoEmisor']))
                    $EmisorIntercambio->email = $emisor['CorreoEmisor'];
                if (!empty($emisor['Acteco'])) {
                    $actividad_economica = $EmisorIntercambio->actividad_economica;
                    $EmisorIntercambio->actividad_economica = $emisor['Acteco'];
                    if (!$EmisorIntercambio->getActividadEconomica()->exists())
                        $EmisorIntercambio->actividad_economica = $actividad_economica;
                }
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($emisor['CmnaOrigen']);
                if ($comuna) {
                    $EmisorIntercambio->direccion = $emisor['DirOrigen'];
                    $EmisorIntercambio->comuna = $comuna;
                }
                if (!empty($emisor['CdgSIISucur']))
                    $EmisorIntercambio->sucursal_sii = (int)$emisor['CdgSIISucur'];
                $EmisorIntercambio->modificado = date('Y-m-d H:i:s');
                $EmisorIntercambio->save();
            }
            // guardar documentos que tienen acuse de recibo como dte recibidos
            $Documentos = $DteIntercambio->getDocumentos();
            foreach ($Documentos as $Dte) {
                if (in_array($Dte->getID(), $EnvioRecibos_r)) {
                    $resumen = $Dte->getResumen();
                    $DteRecibido = new Model_DteRecibido();
                    $DteRecibido->emisor = $DteIntercambio->getEmisor()->rut;
                    $DteRecibido->dte = $resumen['TpoDoc'];
                    $DteRecibido->folio = $resumen['NroDoc'];
                    $DteRecibido->certificacion = (int)$DteIntercambio->certificacion;
                    if (!$DteRecibido->exists()) {
                        $DteRecibido->receptor = $Emisor->rut;
                        $DteRecibido->tasa = (int)$resumen['TasaImp'];
                        $DteRecibido->fecha = $resumen['FchDoc'];
                        $DteRecibido->sucursal_sii = (int)$resumen['CdgSIISucur'];
                        if ($resumen['MntExe'])
                            $DteRecibido->exento = $resumen['MntExe'];
                        if ($resumen['MntNeto'])
                            $DteRecibido->neto = $resumen['MntNeto'];
                        $DteRecibido->iva = (int)$resumen['MntIVA'];
                        $DteRecibido->total = (int)$resumen['MntTotal'];
                        $DteRecibido->usuario = $this->Auth->User->id;
                        $DteRecibido->intercambio = $DteIntercambio->codigo;
                        $DteRecibido->save();
                    }
                }
            }
        }
        //
        // enviar los 3 XML de respuesta por email
        //
        $email = $Emisor->getEmailSmtp();
        $email->to($_POST['responder_a']);
        $email->subject($Emisor->rut.'-'.$Emisor->dv.' - Respuesta intercambio N° '.$DteIntercambio->codigo);
        foreach (['RecepcionDTE', 'EnvioRecibos', 'ResultadoDTE'] as $xml) {
            if (isset(${$xml.'_xml'})) {
                $email->attach([
                    'data' => ${$xml.'_xml'},
                    'name' => $xml.'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteIntercambio->codigo.'.xml',
                    'type' => 'application/xml',
                ]);
            }
        }
        // enviar email
        $status = $email->send('Se adjuntan XMLs de respuesta a intercambio de DTE.');
        if ($status===true) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se procesaron DTEs de intercambio y se envió la respuesta a: '.$_POST['responder_a'], 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se procesaron DTEs de intercambio, pero no fue posible enviar el email, por favor intente nuevamente.<br /><em>'.$status['message'].'</em>', 'error'
            );
        }
        $this->redirect(str_replace('responder', 'ver', $this->request->request));
    }

    /**
     * Acción que entrega los XML del resultado de la revisión del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function resultados_xml($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // si no hay XML error
        if (!$DteIntercambio->recepcion_xml and !$DteIntercambio->recibos_xml and !$DteIntercambio->resultado_xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existen archivos de resultado generados, no se ha procesado aun el intercambio', 'error'
            );
            $this->redirect(str_replace('resultados_xml', 'ver', $this->request->request));
        }
        // agregar a archivo comprimido y entregar
        $dir = TMP.'/resultado_intercambio_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteIntercambio->codigo;
        if (is_dir($dir))
            \sowerphp\general\Utility_File::rmdir($dir);
        if (!mkdir($dir)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el directorio temporal para los XML', 'error'
            );
            $this->redirect(str_replace('resultados_xml', 'ver', $this->request->request));
        }
        if ($DteIntercambio->recepcion_xml)
            file_put_contents($dir.'/RecepcionDTE.xml', base64_decode($DteIntercambio->recepcion_xml));
        if ($DteIntercambio->recibos_xml)
            file_put_contents($dir.'/EnvioRecibos.xml', base64_decode($DteIntercambio->recibos_xml));
        if ($DteIntercambio->resultado_xml)
            file_put_contents($dir.'/ResultadoDTE.xml', base64_decode($DteIntercambio->resultado_xml));
        \sowerphp\general\Utility_File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        exit;
    }

}
