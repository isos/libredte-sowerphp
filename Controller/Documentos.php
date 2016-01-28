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
 * Clase para todas las acciones asociadas a documentos (incluyendo API)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-12-25
 */
class Controller_Documentos extends \Controller_App
{

    private $IndTraslado = [
        1 => 'Operación constituye venta',
        2 => 'Ventas por efectuar',
        3 => 'Consignaciones',
        4 => ' Entrega gratuita',
        5 => 'Traslados internos',
        6 => 'Otros traslados no venta',
        7 => 'Guía de devolución',
        8 => 'Traslado para exportación. (no venta)',
        9 => 'Venta para exportación',
    ]; ///< tipos de traslado

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function beforeFilter()
    {
        $this->Auth->allow('consultar');
        parent::beforeFilter();
    }

    /**
     * Acción que permite buscar y consultar un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function consultar($dte = null)
    {
        // asignar variables para el formulario
        $this->set([
            'dtes' => (new \website\Dte\Admin\Model_DteTipos())->getList(),
            'dte' => isset($_POST['dte']) ? $_POST['dte'] : $dte,
        ]);
        // si se solicitó un documento se busca
        if (isset($_POST['submit'])) {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($_POST['emisor']);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                return;
            }
            // buscar si existe el DTE en el ambiente que el emisor esté usando
            $DteEmitido = new Model_DteEmitido($Emisor->rut, $_POST['dte'], $_POST['folio'], (int)$Emisor->config_ambiente_en_certificacion);
            if (!$DteEmitido->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $Emisor->razon_social.' no tiene emitido el DTE solicitado en el ambiente de '.$Emisor->getAmbiente(), 'error'
                );
                return;
            }
            // verificar que coincida fecha de emisión y monto total del DTE
            if ($DteEmitido->fecha!=$_POST['fecha'] or $DteEmitido->total!=$_POST['total']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
                );
                return;
            }
            // asignar DTE a la vista
            $this->set('DteEmitido', $DteEmitido);
        }
    }

    /**
     * Acción para mostrar página de emisión de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-02
     */
    public function emitir($referencia_dte = null, $referencia_folio = null)
    {
        $Emisor = $this->getContribuyente();
        if ($referencia_dte and $referencia_folio) {
            $DteEmitido = new Model_DteEmitido($Emisor->rut, $referencia_dte, $referencia_folio, (int)$Emisor->config_ambiente_en_certificacion);
            if (!$DteEmitido->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Documento T'.$referencia_dte.'F'.$referencia_folio.' no existe, no se puede referenciar', 'error'
                );
                $this->redirect('/dte/dte_emitidos');
            }
            $DteEmisor = $DteEmitido->getDatos()['Encabezado']['Emisor'];
            $DteReceptor = $DteEmitido->getDatos()['Encabezado']['Receptor'];
            $Comunas = new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas();
            $DteEmisor['CmnaOrigen'] = $Comunas->getComunaByName($DteEmisor['CmnaOrigen']);
            $DteReceptor['CmnaRecep'] = $Comunas->getComunaByName($DteReceptor['CmnaRecep']);
            $this->set([
                'DteEmitido' => $DteEmitido,
                'DteEmisor' => $DteEmisor,
                'DteReceptor' => $DteReceptor,
            ]);
        }
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js'], 'css'=>['/dte/css/dte.css']],
            'Emisor' => $Emisor,
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'tipos_referencia' => (new \website\Dte\Admin\Model_DteReferenciaTipos())->getList(),
            'IndTraslado' => $this->IndTraslado,

        ]);
    }

    /**
     * Acción para generar y mostrar previsualización de emisión de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-02
     */
    public function previsualizacion()
    {
        $Emisor = $this->getContribuyente();
        // si no se viene por POST redirigir
        if (!isset($_POST['submit'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder de forma directa a la previsualización', 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // si no está autorizado a emitir el tipo de documento redirigir
        if (!$Emisor->documentoAutorizado($_POST['TpoDoc'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No está autorizado a emitir el tipo de documento '.$_POST['TpoDoc'], 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // revisar datos mínimos
        $datos_minimos = ['TpoDoc', 'FchEmis', 'GiroEmis', 'Acteco', 'DirOrigen', 'CmnaOrigen', 'RUTRecep', 'RznSocRecep', 'GiroRecep', 'DirRecep', 'CmnaRecep', 'NmbItem'];
        foreach ($datos_minimos as $attr) {
            if (empty($_POST[$attr])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Error al recibir campos mínimos, falta: '.$attr
                );
                $this->redirect('/dte/documentos/emitir');
            }
        }
        // copiar datos del emisor
        $Emisor->giro = $_POST['GiroEmis'];
        $Emisor->actividad_economica = $_POST['Acteco'];
        $Emisor->direccion = $_POST['DirOrigen'];
        $Emisor->comuna = $_POST['CmnaOrigen'];
        // crear receptor
        list($rut, $dv) = explode('-', str_replace('.', '', $_POST['RUTRecep']));
        $Receptor = new Model_Contribuyente($rut);
        $Receptor->dv = $dv;
        $Receptor->razon_social = $_POST['RznSocRecep'];
        $Receptor->giro = substr($_POST['GiroRecep'], 0, 40);
        $Receptor->telefono = $_POST['Contacto'];
        $Receptor->email = $_POST['CorreoRecep'];
        $Receptor->direccion = $_POST['DirRecep'];
        $Receptor->comuna = $_POST['CmnaRecep'];
        // guardar receptor si no tiene usuario asociado
        if (!$Receptor->usuario) {
            $Receptor->modificado = date('Y-m-d H:i:s');
            try {
                $Receptor->save();
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible guardar el receptor: '.$e->getMessage()
                );
                $this->redirect('/dte/documentos/emitir');
            }
        }
        // generar datos del encabezado para el dte
        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $_POST['TpoDoc'],
                    'Folio' => 0, // en previsualización no se asigna folio
                    'FchEmis' => $_POST['FchEmis'],
                ],
                'Emisor' => [
                    'RUTEmisor' => $Emisor->rut.'-'.$Emisor->dv,
                    'RznSoc' => $Emisor->razon_social,
                    'GiroEmis' => $Emisor->giro,
                    'Telefono' => $Emisor->telefono ? $Emisor->telefono : false,
                    'CorreoEmisor' => $Emisor->email ? $Emisor->email : false,
                    'Acteco' => $Emisor->actividad_economica,
                    'DirOrigen' => $Emisor->direccion,
                    'CmnaOrigen' => $Emisor->getComuna()->comuna,
                ],
                'Receptor' => [
                    'RUTRecep' => $Receptor->rut.'-'.$Receptor->dv,
                    'RznSocRecep' => $Receptor->razon_social,
                    'GiroRecep' => $Receptor->giro,
                    'Contacto' => $Receptor->telefono ? $Receptor->telefono : false,
                    'CorreoRecep' => $Receptor->email ? $Receptor->email : false,
                    'DirRecep' => $Receptor->direccion,
                    'CmnaRecep' => $Receptor->getComuna()->comuna,
                ],
            ],
        ];
        // agregar datos de traslado si es guía de despacho
        if ($dte['Encabezado']['IdDoc']['TipoDTE']==52) {
            $dte['Encabezado']['IdDoc']['IndTraslado'] = $_POST['IndTraslado'];
            if (!empty($_POST['Patente']) or !empty($_POST['RUTTrans']) or (!empty($_POST['RUTChofer']) and !empty($_POST['NombreChofer'])) or !empty($_POST['DirDest']) or !empty($_POST['CmnaDest'])) {
                $dte['Encabezado']['Transporte'] = [
                    'Patente' => !empty($_POST['Patente']) ? $_POST['Patente'] : false,
                    'RUTTrans' => !empty($_POST['RUTTrans']) ? str_replace('.', '', $_POST['RUTTrans']) : false,
                    'Chofer' => (!empty($_POST['RUTChofer']) and !empty($_POST['NombreChofer'])) ? [
                        'RUTChofer' => str_replace('.', '', $_POST['RUTChofer']),
                        'NombreChofer' => $_POST['NombreChofer'],
                    ] : false,
                    'DirDest' => !empty($_POST['DirDest']) ? $_POST['DirDest'] : false,
                    'CmnaDest' => !empty($_POST['CmnaDest']) ? (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna($_POST['CmnaDest']))->comuna : false,
                ];
            }
        }
        // agregar detalle a los datos
        $n_detalles = count($_POST['NmbItem']);
        $dte['Detalle'] = [];
        $n_itemAfecto = 0;
        $n_itemExento = 0;
        for ($i=0; $i<$n_detalles; $i++) {
            $detalle = [];
            // código del item
            if (!empty($_POST['VlrCodigo'][$i])) {
                $detalle['CdgItem'] = [
                    'TpoCodigo' => !empty($_POST['TpoCodigo'][$i]) ? $_POST['TpoCodigo'][$i] : 'INT1',
                    'VlrCodigo' => $_POST['VlrCodigo'][$i],
                ];
            }
            // otros datos
            $datos = ['IndExe', 'NmbItem', 'DscItem', 'QtyItem', 'UnmdItem', 'PrcItem'];
            foreach ($datos as $d) {
                if (!empty($_POST[$d][$i])) {
                    $detalle[$d] = $_POST[$d][$i];
                }
            }
            // descuento
            if (!empty($_POST['ValorDR'][$i]) and !empty($_POST['TpoValor'][$i])) {
                if ($_POST['TpoValor'][$i]=='%')
                    $detalle['DescuentoPct'] = $_POST['ValorDR'][$i];
                else
                    $detalle['DescuentoMonto'] = $_POST['ValorDR'][$i];
            }
            // agregar detalle al listado
            $dte['Detalle'][] = $detalle;
            // contabilizar item afecto o exento
            if (empty($detalle['IndExe'])) $n_itemAfecto++;
            else $n_itemExento++;
        }
        // agregar descuento globales
        if (!empty($_POST['ValorDR_global']) and !empty($_POST['TpoValor_global'])) {
            $dte['DscRcgGlobal'] = [];
            if ($n_itemAfecto) {
                $dte['DscRcgGlobal'][] = [
                    'TpoMov' => 'D',
                    'TpoValor' => $_POST['TpoValor_global'],
                    'ValorDR' => $_POST['ValorDR_global'],
                ];
            }
            if ($n_itemExento) {
                $dte['DscRcgGlobal'][] = [
                    'TpoMov' => 'D',
                    'TpoValor' => $_POST['TpoValor_global'],
                    'ValorDR' => $_POST['ValorDR_global'],
                    'IndExeDR' => 1,
                ];
            }
        }
        // agregar referencias
        if (isset($_POST['TpoDocRef'][0])) {
            $n_referencias = count($_POST['TpoDocRef']);
            $dte['Referencia'] = [];
            for ($i=0; $i<$n_referencias; $i++) {
                $dte['Referencia'][] = [
                    'TpoDocRef' => $_POST['TpoDocRef'][$i],
                    'FolioRef' => $_POST['FolioRef'][$i],
                    'FchRef' => $_POST['FchRef'][$i],
                    'CodRef' => !empty($_POST['CodRef'][$i]) ? $_POST['CodRef'][$i] : false,
                    'RazonRef' => !empty($_POST['RazonRef'][$i]) ? $_POST['RazonRef'][$i] : false,
                ];
            }
        }
        // crear objeto Dte y asignar variables para la vista
        $Dte = new \sasco\LibreDTE\Sii\Dte($dte);
        $resumen = $Dte->getResumen();
        $DteTmp = new Model_DteTmp();
        $DteTmp->datos = json_encode($Dte->getDatos());
        $DteTmp->emisor = $Emisor->rut;
        $DteTmp->receptor = $Receptor->rut;
        $DteTmp->dte = $resumen['TpoDoc'];
        $DteTmp->codigo = md5($DteTmp->datos);
        $DteTmp->fecha = $resumen['FchDoc'];
        $DteTmp->total = $resumen['MntTotal'];
        try {
            $DteTmp->save();
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible guardar el DTE temporal: '.$e->getMessage()
            );
            $this->redirect('/dte/documentos/emitir');
        }
        $this->set([
            'resumen' => $resumen,
            'DteTmp' => $DteTmp,
        ]);
    }

    /**
     * Método que genera la el XML del DTE temporal con Folio y Firma y lo envía
     * al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-02
     */
    public function generar($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // obtener firma electrónica
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // solicitar folio
        $FolioInfo = $Emisor->getFolio($DteTmp->dte);
        if (!$FolioInfo) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible obtener un folio para el DTE de tipo '.$DteTmp->dte, 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // si quedan pocos folios y se debe alertar al usuario admnistrador de la empresa se hace
        if ($FolioInfo->DteFolio->disponibles<=$FolioInfo->DteFolio->alerta and !$FolioInfo->DteFolio->alertado) {
            $msg = 'Se ha alcanzado el límite de folios del tipo de DTE '.$FolioInfo->DteFolio->dte.' para el contribuyente '.$Emisor->razon_social.', quedan '.$FolioInfo->DteFolio->disponibles.'. Por favor, solicite un nuevo archivo CAF y súbalo en: '."\n\n".$this->request->url.'/dte/admin/dte_folios';
            if ($this->Notify->send(null, $Emisor->getUsuario()->id, $msg, 'email')) {
                $FolioInfo->DteFolio->alertado = 1;
                $FolioInfo->DteFolio->save();
            }
        }
        // armar xml a partir de json del DTE temporal
        $EnvioDte = $DteTmp->getEnvioDte($FolioInfo->folio, $FolioInfo->Caf, $Firma);
        $xml = $EnvioDte->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el XML del EnvioDTE. Folio '.$FolioInfo->folio.' quedará sin usar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // guardar DTE
        $r = $EnvioDte->getDocumentos()[0]->getResumen();
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $r['TpoDoc'], $r['NroDoc'], (int)$Emisor->config_ambiente_en_certificacion);
        if ($DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Ya existe un DTE del tipo '.$r['TpoDoc'].' y folio '.$r['NroDoc'].' emitido', 'error'
            );
            $this->redirect('/dte/dte_emitidos/ver/'.$r['TpoDoc'].'/'.$r['NroDoc'].'/'.(int)$Emisor->config_ambiente_en_certificacion);
        }
        $cols = ['tasa'=>'TasaImp', 'fecha'=>'FchDoc', 'receptor'=>'RUTDoc', 'exento'=>'MntExe', 'neto'=>'MntNeto', 'iva'=>'MntIVA', 'total'=>'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col]!==false)
                $DteEmitido->$attr = $r[$col];
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = base64_encode($xml);
        $DteEmitido->usuario = $this->Auth->User->id;
        // guardar referencias si existen
        $datos = json_decode($DteTmp->datos, true);
        if (isset($datos['Referencia'])) {
            foreach ($datos['Referencia'] as $referencia) {
                $DteReferencia = new Model_DteReferencia();
                $DteReferencia->emisor = $DteEmitido->emisor;
                $DteReferencia->dte = $DteEmitido->dte;
                $DteReferencia->folio = $DteEmitido->folio;
                $DteReferencia->certificacion = $DteEmitido->certificacion;
                $DteReferencia->referencia_dte = $referencia['TpoDocRef'];
                $DteReferencia->referencia_folio = $referencia['FolioRef'];
                $DteReferencia->codigo = !empty($referencia['CodRef']) ? $referencia['CodRef'] : null;
                $DteReferencia->razon = !empty($referencia['RazonRef']) ? $referencia['RazonRef'] : null;
                $DteReferencia->save();
            }
        }
        // eliminar DTE temporal
        $DteTmp->delete();
        // enviar DTE al SII y redireccionar a página del DTE
        $track_id = $EnvioDte->enviar();
        if ($track_id) {
            $DteEmitido->track_id = $track_id;
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento emitido y envíado al SII, ahora debe verificar estado del envío. TrackID: '.$track_id, 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento emitido, pero no pudo ser envíado al SII, debe reenviar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'warning'
            );
        }
        $DteEmitido->save();
        $this->redirect('/dte/dte_emitidos/ver/'.$r['TpoDoc'].'/'.$r['NroDoc'].'/'.(int)$Emisor->config_ambiente_en_certificacion);
    }

    /**
     * Recurso de la API que genera el XML de los DTEs solicitados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-11-21
     */
    public function _api_generar_xml_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar que se hayan pasado los índices básicos
        foreach (['Emisor', 'Receptor', 'documentos', 'folios', 'firma'] as $key) {
            if (!isset($this->Api->data[$key]))
                $this->Api->send('Falta índice/variable '.$key.' por POST', 500);
        }
        // recuperar folios y definir ambiente
        $folios = [];
        $certificacion = false;
        foreach ($this->Api->data['folios'] as $folio) {
            $Folios = new \sasco\LibreDTE\Sii\Folios(base64_decode($folio));
            $folios[$Folios->getTipo()] = $Folios;
            if ($Folios->getCertificacion())
                $certificacion = true;
        }
        // normalizar datos emisor
        $this->Api->data['Emisor']['RUTEmisor'] = str_replace('.', '', $this->Api->data['Emisor']['RUTEmisor']);
        // normalizar datos receptor
        $this->Api->data['Receptor']['RUTRecep'] = str_replace('.', '', $this->Api->data['Receptor']['RUTRecep']);
        // objeto de la firma
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data'=>base64_decode($this->Api->data['firma']['data']),
                'pass'=>$this->Api->data['firma']['pass']
            ]);
        } catch (\Exception $e) {
            $this->Api->send('No fue posible abrir la firma digital, quizás contraseña incorrecta', 500);
        }
        // normalizar dte?
        $normalizar_dte = isset($this->Api->data['normalizar_dte']) ? $this->Api->data['normalizar_dte'] : true;
        // armar documentos y guardar en un arreglo
        $Documentos = [];
        foreach ($this->Api->data['documentos'] as $d) {
            // crear documento
            $d['Encabezado']['Emisor'] = $this->Api->data['Emisor'];
            $d['Encabezado']['Receptor'] = $this->Api->data['Receptor'];
            $DTE = new \sasco\LibreDTE\Sii\Dte($d, $normalizar_dte);
            // timbrar, firmar y validar el documento
            if (!isset($folios[$DTE->getTipo()])) {
                return $this->Api->send('Falta el CAF para el tipo de DTE '.$DTE->getTipo(), 500);
            }
            if (!$DTE->timbrar($folios[$DTE->getTipo()]) or !$DTE->firmar($Firma) or !$DTE->schemaValidate()) {
                return $this->Api->send(implode("\n", \sasco\LibreDTE\Log::readAll()), 500);
            }
            // agregar el DTE al listado
            $Documentos[] = $DTE;
        }
        // armar EnvioDTE si se pasó fecha de resolución y número de resolución
        if (isset($this->Api->data['resolucion']) and !empty($this->Api->data['resolucion']['FchResol']) and isset($this->Api->data['resolucion']['NroResol'])) {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            foreach ($Documentos as $DTE) {
                $EnvioDte->agregar($DTE);
            }
            $EnvioDte->setCaratula([
                'RutEnvia' => $Firma->getID(),
                'RutReceptor' => $certificacion ? '60803000-K' : $this->Api->data['Receptor']['RUTRecep'],
                'FchResol' => $this->Api->data['resolucion']['FchResol'],
                'NroResol' => (int)$this->Api->data['resolucion']['NroResol'],
            ]);
            $EnvioDte->setFirma($Firma);
            // generar
            $xml = $EnvioDte->generar();
            // validar schema del DTE
            if (!$EnvioDte->schemaValidate()) {
                return $this->Api->send(implode("\n", \sasco\LibreDTE\Log::readAll()), 500);
            }
            $dir = sys_get_temp_dir().'/EnvioDTE_'.$this->Api->data['Emisor']['RUTEmisor'].'_'.$this->Api->data['Receptor']['RUTRecep'].'_'.date('U').'.xml';
            file_put_contents($dir, $xml);
        }
        // entregar DTEs comprimidos y en archivos sueltos
        else {
            // directorio temporal para guardar los XML
            $dir = sys_get_temp_dir().'/DTE_'.$this->Api->data['Emisor']['RUTEmisor'].'_'.$this->Api->data['Receptor']['RUTRecep'].'_'.date('U');
            if (is_dir($dir))
                \sasco\LibreDTE\File::rmdir($dir);
            if (!mkdir($dir))
                $this->Api->send('No fue posible crear directorio temporal para DTEs', 500);
            // procesar cada DTEs e ir agregándolo al directorio que se comprimirá
            foreach ($Documentos as $DTE) {
                // guardar XML
                file_put_contents($dir.'/dte_'.$this->Api->data['Emisor']['RUTEmisor'].'_'.$DTE->getID().'.xml', $DTE->saveXML());
            }
        }
        // guardar datos de emisor, receptor y estadísticas
        if (isset($this->Api->data['resolucion'])) {
            $resolucion = [];
            if (!empty($this->Api->data['resolucion']['FchResol']))
                $resolucion['FchResol'] = $this->Api->data['resolucion']['FchResol'];
            if (isset($this->Api->data['resolucion']['NroResol']))
                $resolucion['NroResol'] = $this->Api->data['resolucion']['NroResol'];
            $this->guardarEmisor($this->Api->data['Emisor'], $resolucion);
        } else {
            $this->guardarEmisor($this->Api->data['Emisor']);
        }
        $this->guardarReceptor($this->Api->data['Receptor']);
        list($emisor, $dv) = explode('-', $this->Api->data['Emisor']['RUTEmisor']);
        list($receptor, $dv) = explode('-', $this->Api->data['Receptor']['RUTRecep']);
        // entregar archivo comprimido que incluirá cada uno de los DTEs
        \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
    }

    /**
     * Recurso de la API que genera el PDF de los DTEs contenidos en un EnvioDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-15
     */
    public function _api_generar_pdf_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // si hubo problemas al subir el archivo error
        if (!isset($this->Api->data['xml']) and (!isset($_FILES['xml']) or $_FILES['xml']['error'])) {
            $this->Api->send('Hubo algún problema al recibir el archivo XML con el EnvioDTE', 500);
        }
        // recuperar contenido del archivo xml
        if (isset($this->Api->data['xml'])) {
            $xml = base64_decode($this->Api->data['xml']);
        } else {
            $xml = file_get_contents($_FILES['xml']['tmp_name']);
        }
        // recuperar contenido del logo (si existe)
        if (isset($this->Api->data['logo'])) {
            $logo = base64_decode($this->Api->data['logo']);
        } else if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
            $logo = file_get_contents($_FILES['logo']['tmp_name']);
        }
        // crear flag cedible
        $cedible = !empty($this->Api->data['cedible']) ? $this->Api->data['cedible'] : false;
        // crear flag papel continuo
        $papelContinuo = !empty($this->Api->data['papelContinuo']) ? $this->Api->data['papelContinuo'] : false;
        // crear opción para web de verificación
        $webVerificacion = !empty($this->Api->data['webVerificacion']) ? $this->Api->data['webVerificacion'] : false;
        // sin límite de tiempo para generar documentos
        set_time_limit(0);
        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML($xml);
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();
        // directorio temporal para guardar los PDF
        $dir = sys_get_temp_dir().'/dte_'.$Caratula['RutEmisor'].'_'.$Caratula['RutReceptor'].'_'.str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
        if (is_dir($dir))
            \sasco\LibreDTE\File::rmdir($dir);
        if (!mkdir($dir))
            $this->Api->send('No fue posible crear directorio temporal para DTEs', 500);
        // procesar cada DTEs e ir agregándolo al PDF
        foreach ($Documentos as $DTE) {
            if (!$DTE->getDatos())
                $this->Api->send('No se pudieron obtener los datos de un DTE', 500);
            // generar PDF
            $pdf = new \sasco\LibreDTE\Sii\PDF\Dte($papelContinuo);
            $pdf->setFooterText();
            if (isset($logo))
                $pdf->setLogo('@'.$logo);
            $pdf->setResolucion(['FchResol'=>$Caratula['FchResol'], 'NroResol'=>$Caratula['NroResol']]);
            if ($webVerificacion)
                $pdf->setWebVerificacion($webVerificacion);
            $pdf->agregar($DTE->getDatos(), $DTE->getTED());
            if ($cedible and $DTE->esCedible()) {
                $pdf->setCedible(true);
                $pdf->agregar($DTE->getDatos(), $DTE->getTED());
            }
            $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'.pdf';
            $pdf->Output($file, 'F');
        }
        // si solo es un archivo y se pidió no comprimir se entrega directamente
        if (isset($this->Api->data['compress']) and !$this->Api->data['compress'] and !isset($Documentos[1])) {
            $this->response->sendFile($file, ['disposition'=>'attachement', 'exit'=>false]);
            \sowerphp\general\Utility_File::rmdir($dir);
            exit(0);
        }
        // entregar archivo comprimido que incluirá cada uno de los DTEs
        else {
            \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        }
    }

    /**
     * Recurso de la API que permite validar el TED (timbre electrónico)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-19
     */
    public function _api_verificar_ted_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener TED
        $TED = base64_decode($this->Api->data);
        if (strpos($TED, '<?xml')!==0)
            $TED = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n".$TED;
        // crear xml con el ted y obtener datos en arreglo
        $xml = new \sasco\LibreDTE\XML();
        $xml->loadXML($TED);
        $datos = $xml->toArray();
        // verificar firma del ted
        $DD = $xml->getFlattened('/TED/DD');
        $FRMT = $datos['TED']['FRMT'];
        $pub_key = \sasco\LibreDTE\FirmaElectronica::getFromModulusExponent(
            $datos['TED']['DD']['CAF']['DA']['RSAPK']['M'],
            $datos['TED']['DD']['CAF']['DA']['RSAPK']['E']
        );
        if (openssl_verify($DD, base64_decode($FRMT), $pub_key, OPENSSL_ALGO_SHA1)!==1) {
            $this->Api->send('Firma del timbre incorrecta', 500);
        }
        // verificar que datos del timbre correspondan con datos del CAF
        if ($datos['TED']['DD']['RE']!=$datos['TED']['DD']['CAF']['DA']['RE']) {
            $this->Api->send('RUT del timbre no corresponde con RUT del CAF', 500);
        }
        if ($datos['TED']['DD']['TD']!=$datos['TED']['DD']['CAF']['DA']['TD']) {
            $this->Api->send('Tipo de DTE del timbre no corresponde con tipo de DTE del CAF', 500);
        }
        if ($datos['TED']['DD']['F']<$datos['TED']['DD']['CAF']['DA']['RNG']['D'] or $datos['TED']['DD']['F']>$datos['TED']['DD']['CAF']['DA']['RNG']['H']) {
            $this->Api->send('Folio del DTE del timbre fuera del rango del CAF', 500);
        }
        // definir si se consultará en certificación o producción
        define('_LibreDTE_CERTIFICACION_', $datos['TED']['DD']['CAF']['DA']['IDK']==100);
        // crear objeto firma
        $Firma = new \sasco\LibreDTE\FirmaElectronica();
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            return $this->Api->send(\sasco\LibreDTE\Log::readAll(), 500);
        }
        // verificar estado del DTE con el SII
        list($RutConsultante, $DvConsultante) = explode('-', $Firma->getID());
        list($RutCompania, $DvCompania) = explode('-', $datos['TED']['DD']['RE']);
        list($RutReceptor, $DvReceptor) = explode('-', $datos['TED']['DD']['RR']);
        list($a, $m, $d) = explode('-', $datos['TED']['DD']['FE']);
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => $RutConsultante,
            'DvConsultante'     => $DvConsultante,
            'RutCompania'       => $RutCompania,
            'DvCompania'        => $DvCompania,
            'RutReceptor'       => $RutReceptor,
            'DvReceptor'        => $DvReceptor,
            'TipoDte'           => $datos['TED']['DD']['TD'],
            'FolioDte'          => $datos['TED']['DD']['F'],
            'FechaEmisionDte'   => $d.$m.$a,
            'MontoDte'          => $datos['TED']['DD']['MNT'],
            'token'             => $token,
        ]);
        if ($xml===false) {
            return $this->Api->send(\sasco\LibreDTE\Log::readAll(), 500);
        }
        return (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
    }

    /**
     * Recurso de la API que entrega el contenido del TED a partir de un archivo
     * con el timbre como imagen
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-13
     */
    public function _api_get_ted_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener TED
        $data = base64_decode($this->Api->data);
        $archivo = TMP.'/ted_'.md5($data);
        $pbm = $archivo.'.pbm';
        file_put_contents($archivo, $data);
        exec('convert '.$archivo.' '.$pbm.' 2>&1', $output, $rc);
        unlink($archivo);
        if ($rc) {
            $this->Api->send(implode("\n", $output), 500);
        }
        $ted = exec(DIR_PROJECT.'/app/pdf417decode/pdf417decode '.$pbm.' && echo "" 2>&1', $output, $rc);
        unlink($pbm);
        if ($rc) {
            $this->Api->send(implode("\n", $output), 500);
        }
        return base64_encode($ted);
    }

    /**
     * Método que guarda un Emisor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    private function guardarEmisor($datos, $resolucion = [])
    {
        list($emisor, $dv) = explode('-', $datos['RUTEmisor']);
        $Emisor = new Model_Contribuyente($emisor);
        if ($Emisor->usuario)
            return null;
        $Emisor->dv = $dv;
        $Emisor->razon_social = substr($datos['RznSoc'], 0, 100);
        if (!empty($datos['GiroEmis']))
            $Emisor->giro = substr($datos['GiroEmis'], 0, 80);
        if (!empty($datos['Telefono']))
            $Emisor->telefono = substr($datos['Telefono'], 0, 20);
        if (!empty($datos['CorreoEmisor']))
            $Emisor->email = substr($datos['CorreoEmisor'], 0, 80);
        $Emisor->actividad_economica = (int)$datos['Acteco'];
        if (!empty($datos['DirOrigen']))
            $Emisor->direccion = substr($datos['DirOrigen'], 0, 70);
        if (is_numeric($datos['CmnaOrigen'])) {
            $Emisor->comuna = $datos['CmnaOrigen'];
        } else {
            $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaOrigen']);
            if (!$comuna)
                return false;
            $Emisor->comuna = $comuna;
        }
        if (!empty($resolucion['FchResol']))
            $Emisor->resolucion_fecha = $resolucion['FchResol'];
        if (isset($resolucion['NroResol']))
            $Emisor->resolucion_numero = $resolucion['NroResol'];
        $Emisor->modificado = date('Y-m-d H:i:s');
        try {
            $Emisor->save();
            return true;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

    /**
     * Método que guarda un Receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    private function guardarReceptor($datos)
    {
        list($receptor, $dv) = explode('-', $datos['RUTRecep']);
        $Receptor = new Model_Contribuyente($receptor);
        if ($Receptor->usuario)
            return null;
        $Receptor->dv = $dv;
        $Receptor->razon_social = substr($datos['RznSocRecep'], 0, 100);
        if (!empty($datos['GiroRecep']))
            $Receptor->giro = substr($datos['GiroRecep'], 0, 80);
        if (!empty($datos['Contacto']))
            $Receptor->telefono = substr($datos['Contacto'], 0, 20);
        if (!empty($datos['CorreoRecep']))
            $Receptor->email = substr($datos['CorreoRecep'], 0, 80);
        if (!empty($datos['DirRecep']))
            $Receptor->direccion = substr($datos['DirRecep'], 0, 70);
        if (!empty($datos['CmnaRecep'])) {
            if (is_numeric($datos['CmnaRecep'])) {
                $Receptor->comuna = $datos['CmnaRecep'];
            } else {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaRecep']);
                if (!$comuna)
                    return false;
                $Receptor->comuna = $comuna;
            }
        }
        $Receptor->modificado = date('Y-m-d H:i:s');
        try {
            $Receptor->save();
            return true;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

}
