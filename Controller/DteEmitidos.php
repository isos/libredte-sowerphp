<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
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
     * @version 2016-05-25
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
            if (!empty($pagina)) {
                $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
                $filtros['offset'] = ($pagina-1)*$filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
                }
            } else $paginas = 1;
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Error al recuperar los documentos:<br/>'.$e->getMessage(), 'error'
            );
            $documentos_total = 0;
            $documentos = [];
        }
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
     * @version 2016-03-19
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
        if ($DteEmitido->track_id and $DteEmitido->getEstado()!='R') {
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
            'enviar_sii' => !(in_array($DteEmitido->dte, [39, 41]) or $DteEmitido->track_id == -1),
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
     * @version 2016-04-10
     */
    public function actualizar_estado($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/actualizar_estado/'.$dte.'/'.$folio.'/'.$Emisor->rut);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se actualizó el estado del DTE', 'ok');
        }
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
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
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
     * Acción que descarga el JSON del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-19
     */
    public function json($dte, $folio)
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
        // entregar JSON
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.json';
        $xml = base64_decode($DteEmitido->xml);
        $json = '';

        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML($xml);
        $DTE = $EnvioDte->getDocumentos()[0]->getDatos();
        unset($DTE['@attributes'], $DTE['TED'], $DTE['TmstFirma']);
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Length: '.strlen($json));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        echo json_encode($DTE, JSON_PRETTY_PRINT);
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
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
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
     * Acción que permite cargar un archivo XML como DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-04-12
     */
    public function cargar_xml()
    {
        if (isset($_POST['submit']) and !$_FILES['xml']['error']) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/dte_emitidos/cargar_xml', json_encode(base64_encode(file_get_contents($_FILES['xml']['tmp_name']))));
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                $dte = $response['body'];
                \sowerphp\core\Model_Datasource_Session::message('XML del DTE T'.$dte['dte'].'F'.$dte['folio'].' fue cargado correctamente', 'ok');
            }
        }
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-28
     */
    public function buscar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
        ]);
        if (isset($_POST['submit'])) {
            $xml = [];
            if (!empty($_POST['xml_nodo'])) {
                $n_xml = count($_POST['xml_nodo']);
                for ($i=0; $i<$n_xml; $i++) {
                    if (!empty($_POST['xml_nodo'][$i]) and !empty($_POST['xml_valor'][$i])) {
                        $xml[$_POST['xml_nodo'][$i]] = $_POST['xml_valor'][$i];
                    }
                }
            }
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/dte_emitidos/buscar/'.$Emisor->rut, json_encode([
                'dte' => $_POST['dte'],
                'receptor' => $_POST['receptor'],
                'fecha_desde' => $_POST['fecha_desde'],
                'fecha_hasta' => $_POST['fecha_hasta'],
                'total_desde' => $_POST['total_desde'],
                'total_hasta' => $_POST['total_hasta'],
                'xml' => $xml,
            ]));
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                $this->set([
                    'Emisor' => $Emisor,
                    'documentos' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite obtener la información de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-12
     */
    public function _api_info_GET($dte, $folio, $contribuyente = null)
    {
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = $this->getContribuyente();
        if (!$Emisor) {
            if (!$contribuyente)
                $this->Api->send('Debe indicar el emisor', 500);
            $Emisor = new Model_Contribuyente($contribuyente);
            if (!$Emisor->exists())
                $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User)) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 401);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T.'.$dte.'F'.$folio, 404);
        $DteEmitido->xml = false;
        $this->Api->send($DteEmitido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite obtener el XML de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-12
     */
    public function _api_xml_GET($dte, $folio, $contribuyente = null)
    {
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = $this->getContribuyente();
        if (!$Emisor) {
            if (!$contribuyente)
                $this->Api->send('Debe indicar el emisor', 500);
            $Emisor = new Model_Contribuyente($contribuyente);
            if (!$Emisor->exists())
                $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User)) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 401);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T.'.$dte.'F'.$folio, 404);
        return $DteEmitido->xml;
    }

    /**
     * Acción de la API que permite actualizar el estado de envio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-06
     */
    public function _api_actualizar_estado_GET($dte, $folio, $contribuyente = null)
    {
        // verificar permisos y crear DteEmitido
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = $this->getContribuyente();
        if (!$Emisor) {
            if (!$contribuyente)
                $this->Api->send('Debe indicar el emisor', 500);
            $Emisor = new Model_Contribuyente($contribuyente);
            if (!$Emisor->exists())
                $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User)) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 401);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T.'.$dte.'F'.$folio, 404);
        // si no tiene track id error
        if (!$DteEmitido->track_id) {
            $this->Api->send('DTE no tiene Track ID, primero debe enviarlo al SII', 500);
        }
        // buscar correo con respuesta
        $Imap = $Emisor->getEmailImap('sii');
        if (!$Imap) {
            $this->Api->send('No fue posible conectar mediante IMAP a '.$Emisor->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()), 500);
        }
        $asunto = 'Resultado de Revision Envio '.$DteEmitido->track_id.' - '.$Emisor->rut.'-'.$Emisor->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            $this->Api->send('No se encontró respuesta de envío del DTE, espere unos segundos o solicite nueva revisión.', 404);
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
                    $Imap->setSeen($uid);
                    $this->Api->send([
                        'track_id' => $DteEmitido->track_id,
                        'revision_estado' => $estado,
                        'revision_detalle' => $detalle
                    ], 200, JSON_PRETTY_PRINT);
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    $this->Api->send('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage(), 500);
                }
            }
        }
    }

    /**
     * Acción de la API que permite cargar el XML de un DTE como documento
     * emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-12
     */
    public function _api_cargar_xml_POST()
    {
        // verificar usuario autenticado
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        // cargar XML
        $xml = base64_decode(json_decode($this->Api->data));
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML($xml);
        $Documentos = $EnvioDte->getDocumentos();
        if (count($Documentos)!=1) {
            $this->Api->send('Sólo puede cargar XML que contengan un DTE', 500);
        }
        $Caratula = $EnvioDte->getCaratula();
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($Caratula['RutEmisor']);
        $certificacion = !(bool)$Caratula['NroResol'];
        if (!$Emisor->exists())
            $this->Api->send('Emisor no existe', 404);
        if (!$Emisor->usuarioAutorizado($User)) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 401);
        }
        // crear Objeto del DteEmitido y verificar si ya existe
        $Dte = $Documentos[0];
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $Dte->getTipo(), $Dte->getFolio(), (int)$certificacion);
        if ($DteEmitido->exists()) {
            $this->Api->send('XML enviado ya está registrado', 500);
        }
        // guardar DteEmitido
        $r = $Dte->getResumen();
        $cols = ['tasa'=>'TasaImp', 'fecha'=>'FchDoc', 'receptor'=>'RUTDoc', 'exento'=>'MntExe', 'neto'=>'MntNeto', 'iva'=>'MntIVA', 'total'=>'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col]!==false)
                $DteEmitido->$attr = $r[$col];
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = base64_encode($xml);
        $DteEmitido->usuario = $User->id;
        $DteEmitido->track_id = -1;
        $DteEmitido->save();
        $DteEmitido->xml = null;
        $this->Api->send($DteEmitido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTEs emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-28
     */
    public function _api_buscar_POST($emisor)
    {
        // verificar usuario autenticado
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists())
            $this->Api->send('Emisor no existe', 404);
        if (!$Emisor->usuarioAutorizado($User)) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 401);
        }
        // buscar documentos
        $this->Api->send($Emisor->getDocumentosEmitidos(json_decode($this->Api->data, true)), 200, JSON_PRETTY_PRINT);
    }

}
