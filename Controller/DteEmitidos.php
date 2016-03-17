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
 * Controlador de dte emitidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-26
 */
class Controller_DteEmitidos extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function beforeFilter()
    {
        $this->Auth->allow('pdf', 'xml');
        parent::beforeFilter();
    }

    /**
     * Acción que permite mostrar los documentos emitidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-04
     */
    public function listar($pagina = 1)
    {
        if (!is_numeric($pagina)) {
            $this->redirect('/dte/'.$this->request->params['controller'].'/listar');
        }
        $Emisor = $this->getContribuyente();
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search'])?('?search='.$_GET['search']):'';
        try {
            $documentos_total = $Emisor->countDocumentosEmitidos($filtros);
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Error al recuperar los documentos:<br/>'.$e->getMessage(), 'error'
            );
            $documentos_total = 0;
            $documentos = [];
        }
        if (!empty($pagina)) {
            $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
            $filtros['offset'] = ($pagina-1)*$filtros['limit'];
            $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
            if ($pagina != 1 && $pagina > $paginas) {
                $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
            }
        } else $paginas = 1;
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $documentos,
            'documentos_total' => $documentos_total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'search' => $filtros,
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite eliminar un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-17
     */
    public function eliminar($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si el DTE no está rechazado no se puede eliminar
        if ($DteEmitido->getEstado()!='R') {
            \sowerphp\core\Model_Datasource_Session::message(
                'No es posible eliminar el DTE ya que no está rechazado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio);
        }
        // eliminar DTE
        if (!$DteEmitido->delete()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible eliminar el DTE', 'error'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE eliminado', 'ok'
            );
        }
        $this->redirect('/dte/dte_emitidos/listar');
    }

    /**
     * Acción que muestra la página de un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function ver($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // asignar variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'DteEmitido' => $DteEmitido,
            'Receptor' => $DteEmitido->getReceptor(),
            'emails' => $DteEmitido->getEmails(),
            'referencias' => $DteEmitido->getReferencias(),
            'enviar_sii' => !in_array($DteEmitido->dte, [39, 41]),
        ]);
    }

    /**
     * Acción que envía el DTE al SII si este no ha sido envíado (no tiene track_id)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function enviar_sii($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // si es boleta no se puede enviar
        if (in_array($dte, [39, 41])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento de tipo '.$dte.' no se envía al SII', 'warning'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si el dte ya fue enviado error
        if ($DteEmitido->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE ya se encuentra envíado, tiene el Track ID: '.$DteEmitido->track_id, 'warning'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // enviar XML
        $result = \sasco\LibreDTE\Sii::enviar($Firma->getID(), $Emisor->rut.'-'.$Emisor->dv, base64_decode($DteEmitido->xml), $token);
        if ($result===false or $result->STATUS!='0') {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el DTE al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        $DteEmitido->track_id = (int)$result->TRACKID;
        $DteEmitido->save();
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

    /**
     * Acción que solicita se envíe una nueva revisión del DTE al email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function solicitar_revision($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si no tiene track id error
        if (!$DteEmitido->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
        }
        // solicitar envío de nueva revisión
        $estado = \sasco\LibreDTE\Sii::request('wsDTECorreo', 'reenvioCorreo', [$token, $Emisor->rut, $Emisor->dv, $DteEmitido->track_id]);
        if ($estado===false) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible solicitar una nueva revisión del DTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
        } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible solicitar una nueva revisión del DTE: '.$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0], 'error'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se solicitó nueva revisión del DTE, verificar estado en unos segundos', 'ok'
            );
        }
        // redireccionar
        $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
    }

    /**
     * Acción que actualiza el estado del envío del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-30
     */
    public function actualizar_estado($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si no tiene track id error
        if (!$DteEmitido->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // buscar correo con respuesta
        $Imap = $Emisor->getEmailImap('sii');
        if (!$Imap) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible conectar mediante IMAP a '.$Emisor->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()), 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        $asunto = 'Resultado de Revision Envio '.$DteEmitido->track_id.' - '.$Emisor->rut.'-'.$Emisor->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No se encontró respuesta de envío del DTE, espere unos segundos o solicite nueva revisión.'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $estado = $detalle = null;
            $m = $Imap->getMessage($uid);
            if (!$m)
                continue;
            foreach ($m['attachments'] as $file) {
                if ($file['type']!='application/xml')
                    continue;
                $xml = new \SimpleXMLElement($file['data'], LIBXML_COMPACT);
                // obtener estado y detalle
                if (isset($xml->REVISIONENVIO)) {
                    if ($xml->REVISIONENVIO->REVISIONDTE->TIPODTE==$DteEmitido->dte and $xml->REVISIONENVIO->REVISIONDTE->FOLIO==$DteEmitido->folio) {
                        $estado = (string)$xml->REVISIONENVIO->REVISIONDTE->ESTADO;
                        $detalle = (string)$xml->REVISIONENVIO->REVISIONDTE->DETALLE;
                    }
                } else {
                    $estado = (string)$xml->IDENTIFICACION->ESTADO;
                    $detalle = (int)$xml->ESTADISTICA->SUBTOTAL->ACEPTA ? 'DTE aceptado' : 'DTE no aceptado';
                }
            }
            if (isset($estado)) {
                $DteEmitido->revision_estado = $estado;
                $DteEmitido->revision_detalle = $detalle;
                try {
                    $DteEmitido->save();
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Se actualizó el estado del DTE', 'ok'
                    );
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage(), 'error'
                    );
                }
                // marcar email como leído
                $Imap->setSeen($uid);
            }
        }
        // redireccionar
        $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
    }

    /**
     * Acción que descarga el PDF del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function pdf($dte, $folio, $cedible = false, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            $Emisor = $this->getContribuyente();
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                $this->redirect('/dte/documentos/consultar');
            }
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor and ($DteEmitido->fecha!=$fecha or $DteEmitido->total!=$total)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
            );
            $this->redirect('/dte/documentos/consultar');
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $webVerificacion = $this->request->url.'/boletas';
        $data = [
            'xml' => $DteEmitido->xml,
            'cedible' => !in_array($DteEmitido->dte, [39,41]) ? $cedible : false,
            'compress' => false,
            'webVerificacion' => in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false,
        ];
        // si hay un logo para la empresa se usa
        $logo = \sowerphp\core\Configure::read('dte.logos.dir').'/'.$Emisor->rut.'.png';
        if (is_readable($logo)) {
            $data['logo'] = base64_encode(file_get_contents($logo));
        }
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : \sowerphp\core\Configure::read('api.default.token'));
        $response = $rest->post($this->request->url.'/api/dte/documentos/generar_pdf', $data);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            $this->redirect('/dte/dte_emitidos/listar');
        }
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            $this->redirect('/dte/dte_emitidos/listar');
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
     * Acción que descarga el XML del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function xml($dte, $folio, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            $Emisor = $this->getContribuyente();
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                $this->redirect('/dte/documentos/consultar');
            }
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor and ($DteEmitido->fecha!=$fecha or $DteEmitido->total!=$total)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
            );
            $this->redirect('/dte/documentos/consultar');
        }
        // entregar XML
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = base64_decode($DteEmitido->xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que envía por email el PDF y el XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function enviar_email($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // se verifican datos mínimos
        foreach (['emails', 'asunto', 'mensaje'] as $attr) {
            if (empty($_POST[$attr])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe especificar el campo: '.$attr, 'error'
                );
                $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
            }
        }
        // crear email
        $email = $Emisor->getEmailSmtp();
        $email->to($_POST['emails']);
        $email->subject($_POST['asunto']);
        // adjuntar PDF
        $data = [
            'xml' => $DteEmitido->xml,
            'cedible' => isset($_POST['cedible']),
            'compress' => false,
        ];
        $logo = \sowerphp\core\Configure::read('dte.logos.dir').'/'.$Emisor->rut.'.png';
        if (is_readable($logo)) {
            $data['logo'] = base64_encode(file_get_contents($logo));
        }
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->post($this->request->url.'/api/dte/documentos/generar_pdf', $data);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
        }
        $email->attach([
            'data' => $response['body'],
            'name' => 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.pdf',
            'type' => 'application/pdf',
        ]);
        // adjuntar XML
        $email->attach([
            'data' => base64_decode($DteEmitido->xml),
            'name' => 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml',
            'type' => 'application/xml',
        ]);
        // enviar email
        $status = $email->send($_POST['mensaje']);
        if ($status===true) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se envió el DTE a: '.implode(', ', $_POST['emails']), 'ok'
            );
            $this->redirect(str_replace('enviar_email', 'ver', $this->request->request));
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el email, por favor intente nuevamente.<br /><em>'.$status['message'].'</em>', 'error'
            );
            $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
        }
    }

    /**
     * Acción de la API que permite obtener la información de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-24
     */
    public function _api_info_GET($dte, $folio, $contribuyente = null)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor) {
            if (!$contribuyente)
                $this->Api->send('Debe indicar el emisor', 500);
            $Emisor = new Model_Contribuyente($contribuyente);
            if (!$Emisor->exists())
                $this->Api->send('Emisor no existe', 404);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T.'.$dte.'F'.$folio, 404);
        $DteEmitido->xml = false;
        return $DteEmitido;
    }

}
