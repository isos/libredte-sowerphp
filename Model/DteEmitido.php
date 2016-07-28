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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_emitido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_emitido
 * @author SowerPHP Code Generator
 * @version 2015-09-23 11:44:17
 */
class Model_DteEmitido extends Model_Base_Envio
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_emitido'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $tasa; ///< smallint(16) NOT NULL DEFAULT '0'
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $exento; ///< integer(32) NULL DEFAULT ''
    public $neto; ///< integer(32) NULL DEFAULT ''
    public $iva; ///< integer(32) NOT NULL DEFAULT '0'
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< character text() NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'dte' => array(
            'name'      => 'Dte',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_tipo', 'column' => 'codigo')
        ),
        'folio' => array(
            'name'      => 'Folio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'tasa' => array(
            'name'      => 'Tasa',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii' => array(
            'name'      => 'Sucursal Sii',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'receptor' => array(
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'exento' => array(
            'name'      => 'Exento',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'neto' => array(
            'name'      => 'Neto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva' => array(
            'name'      => 'Iva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total' => array(
            'name'      => 'Total',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'xml' => array(
            'name'      => 'Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'track_id' => array(
            'name'      => 'Track Id',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_estado' => array(
            'name'      => 'Revision Estado',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Revision Detalle',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_DteTipo' => 'website\Dte\Admin\Mantenedores',
        'Model_Contribuyente' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    private $Dte; ///< Objeto con el DTE
    private $datos; ///< Arreglo con los datos del XML del DTE

    /**
     * Método que entrega el objeto del tipo del dte
     * @return \website\Dte\Admin\Mantenedores\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del Dte
     * @return \sasco\LibreDTE\Sii\Dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function getDte()
    {
        if (!$this->Dte) {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDte->loadXML(base64_decode($this->xml));
            $this->Dte = $EnvioDte->getDocumentos()[0];
        }
        return $this->Dte;
    }

    /**
     * Método que entrega el arreglo con los datos que se usaron para generar el
     * XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function getDatos()
    {
        if (!$this->datos) {
            $this->datos = $this->getDte()->getDatos();
        }
        return $this->datos;
    }

    /**
     * Método que entrega el listado de correos a los que se debería enviar el
     * DTE (correo receptor, correo intercambio y correo del dte)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-24
     */
    public function getEmails()
    {
        $aux = [
            'Email receptor' => $this->getReceptor()->email,
            'Email intercambio' => $this->getReceptor()->config_email_intercambio_user,
            'DTE T'.$this->dte.'F'.$this->folio => isset($this->getDatos()['Encabezado']['Receptor']['CorreoRecep']) ? $this->getDatos()['Encabezado']['Receptor']['CorreoRecep'] : false,
        ];
        $emails = [];
        foreach ($aux as $k => $e) {
            if (!empty($e) and !in_array($e, $emails)) {
                $emails[$k] = $e;
            }
        }
        return $emails ? $emails : false;
    }

    /**
     * Método que entrega las referencias que existen a este DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-26
     */
    public function getReferencias()
    {
        return $this->db->getTable('
            SELECT t.tipo AS documento_tipo, r.folio, d.fecha, rt.tipo AS referencia_tipo, r.razon, r.dte
            FROM
                dte_referencia AS r
                JOIN dte_tipo AS t ON r.dte = t.codigo
                JOIN dte_emitido AS d ON d.emisor= r.emisor AND d.certificacion = r.certificacion AND d.dte = r.dte AND d.folio = r.folio
                LEFT JOIN dte_referencia_tipo AS rt ON r.codigo = rt.codigo
            WHERE
                r.emisor = :rut
                AND r.certificacion = :certificacion
                AND r.referencia_dte = :dte
                AND r.referencia_folio = :folio
            ORDER BY fecha DESC, t.tipo ASC, r.folio DESC
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega del intercambio el objeto del Recibo del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecibo()
    {
        $Recibo = new Model_DteIntercambioReciboDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recibo->exists() ? $Recibo : false;
    }

    /**
     * Método que entrega del intercambio el objeto de la Recepcion del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecepcion()
    {
        $Recepcion = new Model_DteIntercambioRecepcionDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recepcion->exists() ? $Recepcion : false;
    }

    /**
     * Método que entrega del intercambio el objeto del Resultado del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioResultado()
    {
        $Resultado = new Model_DteIntercambioResultadoDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Resultado->exists() ? $Resultado : false;
    }

    /**
     * Método que entrega los pagos programados del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-28
     */
    public function getPagosProgramados()
    {
        $MntPagos = [];
        if (isset($this->getDatos()['Encabezado']['IdDoc']['MntPagos']) and is_array($this->getDatos()['Encabezado']['IdDoc']['MntPagos'])) {
            $MntPagos = $this->getDatos()['Encabezado']['IdDoc']['MntPagos'];
            if (!isset($MntPagos[0]))
                $MntPagos = [$MntPagos];
            $MntPago = 0;
            foreach ($MntPagos as $pago)
                $MntPago += $pago['MntPago'];
            if ($MntPago!=$this->total)
                $MntPagos = [];
        }
        return $MntPagos;
    }

    /**
     * Método que entrega los datos de cobranza de los pagos programados del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-28
     */
    public function getCobranza()
    {
        return $this->db->getTable('
            SELECT c.fecha, c.monto, c.glosa, c.pagado, c.observacion, u.usuario, c.modificado
            FROM cobranza AS c LEFT JOIN usuario AS u ON c.usuario = u.id
            WHERE
                c.emisor = :rut
                AND c.dte = :dte
                AND c.folio = :folio
                AND c.certificacion = :certificacion
            ORDER BY fecha
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el estado del envío del DTE al SII
     * @return R: si es RSC, RCT, RCH, =null otros casos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-17
     */
    public function getEstado()
    {
        $espacio = strpos($this->revision_estado, ' ');
        $estado = $espacio ? substr($this->revision_estado, 0, $espacio) : $this->revision_estado;
        return in_array($estado, ['RSC', 'RCT', 'RCH']) ? 'R' : null;
    }

    /**
     * Método que elimina el DTE, y si no hay DTE posterior del mismo tipo,
     * restaura el folio para que se volver a utilizar.
     * Sólo se pueden eliminar DTE que estén rechazados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-19
     */
    public function delete()
    {
        if ($this->track_id and $this->getEstado()!='R') {
            return false;
        }
        $this->db->beginTransaction(true);
        $DteFolio = new \website\Dte\Admin\Model_DteFolio($this->emisor, $this->dte, (int)$this->certificacion);
        if ($DteFolio->siguiente == ($this->folio+1)) {
            $DteFolio->siguiente--;
            $DteFolio->disponibles++;
            try {
                if (!$DteFolio->save(false)) {
                    $this->db->rollback();
                    return false;
                }
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                $this->db->rollback();
                return false;
            }
        }
        debug('delete');
        if (!parent::delete()) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que envía el DTE emitido al SII, básicamente lo saca del sobre y
     * lo pone en uno nuevo con el RUT del SII
     * @param user ID del usuari oque hace el envío
     * @param timbrar Si se desea volver a timbrar antes de enviar (sólo aplica si se puede reenviar)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function enviar($user = null, $timbrar = false)
    {
        $Emisor = $this->getEmisor();
        // boletas no se envían
        if (in_array($this->dte, [39, 41])) {
            return false; // no hay excepción para hacerlo "silenciosamente"
        }
        // si hay track_id y el DTE no está rechazado entonces no se permite
        // volver a enviar al SII (ya que estaría aceptado, aceptado con reparos
        // o aun no se sabe su estado)
        if ($this->track_id and $this->getEstado()!='R') {
            $msg = 'DTE no puede ser reenviado ya que ';
            if (!$this->revision_estado)
                $msg .= 'aun no se ha verificado su estado';
            else if ($this->getEstado()!='R')
                $msg .= 'no está rechazado';
            throw new \Exception($msg);
        }
        // obtener firma
        $Firma = $Emisor->getFirma($user);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar)');
        }
        // preparar datos que se usarán
        $datos = $this->getDatos();
        unset($datos['TmstFirma']);
        if ($timbrar) {
            unset($datos['TED']);
            // corregir datos
            $datos['Encabezado']['Receptor']['RUTRecep'] = strtoupper($datos['Encabezado']['Receptor']['RUTRecep']);
            $datos['Encabezado']['Receptor']['DirRecep'] = substr($datos['Encabezado']['Receptor']['DirRecep'], 0, 70);
        }
        // crear XML EnvioDte
        $Dte = new \sasco\LibreDTE\Sii\Dte($datos, false);
        if ($timbrar) {
            $Caf = $Emisor->getCaf($Dte->getTipo(), $Dte->getFolio());
            if (!$Caf or !$Dte->timbrar($Caf)) {
                throw new \Exception('No fue posible timbrar el DTE que se quiere enviar al SII');
            }
        }
        if (!$Dte->firmar($Firma)) {
            throw new \Exception('No fue posible firmar el DTE que se quiere enviar al SII');
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($Dte);
        $EnvioDte->setFirma($Firma);
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma ? $Firma->getID() : false,
            'RutReceptor' => '60803000-K',
            'FchResol' => $this->certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $this->certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
        ]);
        $xml = $EnvioDte->generar();
        // obtener token
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // enviar XML
        $result = \sasco\LibreDTE\Sii::enviar($Firma->getID(), $Emisor->rut.'-'.$Emisor->dv, $xml, $token);
        if ($result===false or $result->STATUS!='0') {
            throw new \Exception('No fue posible enviar el DTE al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        $this->track_id = (int)$result->TRACKID;
        $this->revision_estado = null;
        $this->revision_detalle = null;
        $this->save();
        return $this->track_id;
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII, en realidad
     * es un wrapper para las verdaderas llamadas
     * @param usarWebservice =true se consultará vía servicio web = false vía email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    public function actualizarEstado($user = null, $usarWebservice = true)
    {
        if (!$this->track_id) {
            throw new \Exception('DTE no tiene Track ID, primero debe enviarlo al SII');
        }
        return $usarWebservice ? $this->actualizarEstadoWebservice($user) : $this->actualizarEstadoEmail();
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII a través del
     * servicio web que dispone el SII para esta consulta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-03
     */
    private function actualizarEstadoWebservice($user = null)
    {
        // crear DTE (se debe crear de esta forma y no usar getDatos() ya que se
        // requiere la firma)
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(base64_decode($this->xml));
        $Dte = $EnvioDte->getDocumentos()[0];
        // obtener firma
        $Firma = $this->getEmisor()->getFirma($user);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar)');
        }
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        // solicitar token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token');
        }
        // consultar estado enviado
        $estado_up = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$this->getEmisor()->rut, $this->getEmisor()->dv, $this->track_id, $token]);
        // si el estado no se pudo recuperar error
        if ($estado_up===false) {
            throw new \Exception('No fue posible obtener el estado del DTE');
        }
        // armar estado del dte
        $estado = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0];
        if (isset($estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0]))
            $glosa = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
        else
            $glosa = null;
        $this->revision_estado = $glosa ? ($estado.' - '.$glosa) : $estado;
        $this->revision_detalle = null;
        if ($estado=='EPR') {
            $resultado = (array)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_BODY')[0];
            // DTE aceptado
            if ($resultado['ACEPTADOS']) {
                $this->revision_detalle = 'DTE aceptado';
            }
            // DTE rechazado
            else if ($resultado['RECHAZADOS']) {
                $this->revision_estado = 'RCH - DTE Rechazado';
            }
            // DTE con reparos
            else  {
                $this->revision_estado = 'RLV - DTE Aceptado con Reparos Leves';
            }
        }
        // guardar estado del dte
        try {
            $this->save();
            return [
                'track_id' => $this->track_id,
                'revision_estado' => $this->revision_estado,
                'revision_detalle' => $this->revision_detalle,
            ];
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
        }
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII a través del
     * email que es recibido desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    private function actualizarEstadoEmail()
    {
        // buscar correo con respuesta
        $Imap = $this->getEmisor()->getEmailImap('sii');
        if (!$Imap) {
            throw new \Exception('No fue posible conectar mediante IMAP a '.$this->getEmisor()->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()));
        }
        $asunto = 'Resultado de Revision Envio '.$this->track_id.' - '.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            if (str_replace('-', '', $this->fecha)<date('Ymd')) {
                $this->solicitarRevision();
                throw new \Exception('No se encontró respuesta de envío del DTE, se solicitó nueva revisión.');
            } else {
                throw new \Exception('No se encontró respuesta de envío del DTE, espere unos segundos o solicite nueva revisión.');
            }
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
                    if ($xml->REVISIONENVIO->REVISIONDTE->TIPODTE==$this->dte and $xml->REVISIONENVIO->REVISIONDTE->FOLIO==$this->folio) {
                        $estado = (string)$xml->REVISIONENVIO->REVISIONDTE->ESTADO;
                        $detalle = (string)$xml->REVISIONENVIO->REVISIONDTE->DETALLE;
                    }
                } else {
                    $estado = (string)$xml->IDENTIFICACION->ESTADO;
                    $detalle = (int)$xml->ESTADISTICA->SUBTOTAL->ACEPTA ? 'DTE aceptado' : 'DTE no aceptado';
                }
            }
            if (isset($estado)) {
                $this->revision_estado = $estado;
                $this->revision_detalle = $detalle;
                try {
                    $this->save();
                    $Imap->setSeen($uid);
                    return [
                        'track_id' => $this->track_id,
                        'revision_estado' => $estado,
                        'revision_detalle' => $detalle
                    ];
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
                }
            }
        }
    }

    /**
     * Método que propone una referencia para el documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-17
     */
    public function getPropuestaReferencia()
    {
        // si es factura o boleta se anula con nota crédito
        if (in_array($this->dte, [33, 34, 39, 41])) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 61,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es nota de crédito se anula con nota de débito
        else if ($this->dte==61) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 56,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es guía de despacho se factura
        else if ($this->dte==52) {
            return [
                'titulo' => 'Facturar guía',
                'color' => 'success',
                'dte' => 33,
                'codigo' => 0,
                'razon' => 'Se factura',
            ];
        }
    }

}
