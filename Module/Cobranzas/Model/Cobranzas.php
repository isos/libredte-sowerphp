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
namespace website\Dte\Cobranzas;

/**
 * Clase para mapear la tabla cobranza de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla cobranza
 * @author SowerPHP Code Generator
 * @version 2016-02-28 18:10:55
 */
class Model_Cobranzas extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'cobranza'; ///< Tabla del

    /**
     * Método que entrega los pagos programados pendientes de pago (pagos por
     * cobrar)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-17
     */
    public function getPendientes($emisor, $certificacion, $desde = null, $hasta = null, $receptor = null)
    {
        $where = [];
        $vars = [':emisor'=>$emisor, ':certificacion'=>$certificacion];
        if (!empty($desde)) {
            $where[] = 'c.fecha >= :desde';
            $vars[':desde'] = $desde;
        }
        if (!empty($hasta)) {
            $where[] = 'c.fecha <= :hasta';
            $vars[':hasta'] = $hasta;
        }
        if (!empty($receptor)) {
            $where[] = 'd.receptor = :receptor';
            $vars[':receptor'] = strpos($receptor,'-') ? \sowerphp\app\Utility_Rut::normalizar($receptor) : $receptor;
        }
        return $this->db->getTable('
            SELECT
                r.razon_social,
                r.rut,
                d.fecha AS fecha_emision,
                t.tipo,
                d.dte,
                d.folio,
                d.total,
                c.fecha AS fecha_pago,
                c.monto AS monto_pago,
                c.glosa,
                c.pagado
            FROM
                cobranza AS c
                JOIN dte_emitido AS d ON
                    d.emisor = c.emisor
                    AND d.dte = c.dte
                    AND d.folio = c.folio
                    AND d.certificacion = c.certificacion
                JOIN dte_tipo AS t ON
                    t.codigo = d.dte
                JOIN contribuyente AS r ON
                    r.rut = d.receptor
                LEFT JOIN usuario AS u ON
                    c.usuario = u.id
            WHERE
                c.emisor = :emisor
                AND c.certificacion = :certificacion
                '.(!empty($where)?('AND '.implode(' AND ', $where)):'').'
                AND (c.pagado IS NULL OR c.monto != c.pagado)
            ORDER BY c.fecha, r.razon_social
        ', $vars);
    }

}
