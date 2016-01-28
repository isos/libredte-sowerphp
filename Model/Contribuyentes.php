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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla contribuyente de la base de datos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-19
 */
class Model_Contribuyentes extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'contribuyente'; ///< Tabla del modelo

    /**
     * Método que entrega el listado de contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-21
     */
    public function getList($all = false)
    {
        if ($all) {
            return $this->db->getTable('
                SELECT rut, razon_social
                FROM contribuyente
                ORDER BY razon_social
            ');
        } else {
            return $this->db->getTable('
                SELECT rut, razon_social
                FROM contribuyente
                WHERE usuario IS NOT NULL
                ORDER BY razon_social
            ');
        }
    }

    /**
     * Método que entrega una tabla con los contribuyentes que cierto usuario
     * está autorizado a operar
     * @param usuario ID del usuario que se quiere obtener el listado de contribuyentes con los que está autorizado a operar
     * @return Tabla con los usuarios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-27
     */
    public function getByUsuario($usuario)
    {
        return $this->db->getTable('
            (
                SELECT c.rut, c.dv, c.razon_social, c.giro, a.valor AS certificacion, true AS administrador
                FROM contribuyente AS c JOIN contribuyente_config AS a ON c.rut = a.contribuyente
                WHERE
                    usuario = :usuario
                    AND a.configuracion = \'ambiente\'
                    AND a.variable = \'en_certificacion\'
            ) UNION (
                SELECT c.rut, c.dv, c.razon_social, c.giro, a.valor AS certificacion, false AS administrador
                FROM contribuyente AS c JOIN contribuyente_config AS a ON c.rut = a.contribuyente
                WHERE
                    rut IN (SELECT contribuyente FROM contribuyente_usuario WHERE usuario = :usuario)
                    AND a.configuracion = \'ambiente\'
                    AND a.variable = \'en_certificacion\'
            )
            ORDER BY administrador DESC, razon_social
        ', [':usuario'=>$usuario]);
    }

    /**
     * Método que entrega una tabla con los movimientos de los contribuyentes
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @param certificacion Ambiente por el que se está consultando
     * @param detalle Si se debe incluir o no el detalle
     * @return Tabla con los contribuyentes y sus movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-07
     */
    public function getConMovimientos($desde = 1, $hasta = null, $certificacion = false, $detalle = true)
    {
        $vars = [':certificacion' => (int)$certificacion];
        // definir desde
        if (is_numeric($desde)) {
            $desde = $this->db->config['type']=='PostgreSQL' ? ('NOW () - INTERVAL \''.(int)$desde.' months\'') : ('DATE_SUB(NOW(), INTERVAL '.(int)$desde.' MONTH)');
        } else {
            $vars[':desde'] = $desde;
            $desde = ':desde';
        }
        // definir hasta
        if ($hasta) {
            $vars[':hasta'] = $hasta;
        }
        // realizar consulta
        $method = $detalle ? 'getTable' : 'getCol';
        return $this->db->$method('
            SELECT c.razon_social '.($detalle?', e.emitidos, r.recibidos':'').'
            FROM
                contribuyente AS c
                LEFT JOIN (
                    SELECT c.rut, COUNT(*) AS emitidos
                    FROM contribuyente AS c, dte_emitido AS e
                    WHERE
                        c.rut = e.emisor
                        AND c.usuario IS NOT NULL
                        AND e.certificacion = :certificacion
                        AND e.fecha >= '.$desde.' '.(!empty($hasta)?'AND e.fecha <= :hasta':'').'
                    GROUP BY c.rut
                ) AS e ON c.rut = e.rut
                LEFT JOIN (
                    SELECT c.rut, COUNT(*) AS recibidos
                    FROM contribuyente AS c, dte_recibido AS r
                    WHERE
                        c.rut = r.receptor
                        AND c.usuario IS NOT NULL
                        AND r.certificacion = :certificacion
                        AND r.fecha >= '.$desde.' '.(!empty($hasta)?'AND r.fecha <= :hasta':'').'
                    GROUP BY c.rut
                ) AS r ON c.rut = r.rut
            WHERE e.emitidos > 0 OR r.recibidos > 0
            ORDER BY c.razon_social
        ', $vars);
    }

    /**
     * Método que entrega la cantidad de contribuyentes registrados
     * @param certificacion =true sólo certificación, =false sólo producción, =null todos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-07
     */
    public function countRegistrados($certificacion = null)
    {
        if ($certificacion===null) {
            return $this->db->getValue(
                'SELECT COUNT(*) FROM contribuyente WHERE usuario IS NOT NULL'
            );
        } else {
            return $this->db->getValue('
                SELECT COUNT(*)
                FROM contribuyente AS c JOIN contribuyente_config AS e ON c.rut = e.contribuyente
                WHERE c.usuario IS NOT NULL AND e.configuracion = \'ambiente\' AND e.variable = \'en_certificacion\' AND e.valor = :certificacion
            ', [':certificacion' => (int)$certificacion]);
        }
    }

}
