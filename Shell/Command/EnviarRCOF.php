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

namespace website\Dte;

/**
 * Comando para enviar el reporte de consumo de folios de las boletas
 * electrónicas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-14
 */
class Shell_Command_EnviarRCOF extends \Shell_App
{

    public function main($grupo = null)
    {
        $from_unix_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $day_before = strtotime('yesterday', $from_unix_time);
        $dia = date('Y-m-d', $day_before);
        $contribuyentes = $this->getContribuyentes($grupo);
        foreach ($contribuyentes as $rut) {
            $this->enviar($rut, $dia);
        }
        $this->showStats();
        return 0;
    }

    private function enviar($rut, $dia)
    {
        $Contribuyente = new Model_Contribuyente($rut);
        if (!$Contribuyente->exists()) {
            return false;
        }
        if ($this->verbose) {
            $this->out('Enviando RCOF del contribuyente '.$Contribuyente->razon_social);
        }
        if (!defined('_LibreDTE_CERTIFICACION_') and $Contribuyente->config_ambiente_en_certificacion) {
            define('_LibreDTE_CERTIFICACION_', true);
        }
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Contribuyente->rut, $dia, (int)$Contribuyente->config_ambiente_en_certificacion);
        try {
            $track_id = $DteBoletaConsumo->enviar();
            if (!$track_id and $this->verbose) {
                $this->out('  No fue posible enviar el reporte');
            }
        } catch (\Exception $e) {
            if ($this->verbose) {
                $this->out('  '.$e->getMessage());
            }
        }
    }

    private function getContribuyentes($grupo = null)
    {
        if (is_numeric($grupo))
            return [$grupo];
        $db = \sowerphp\core\Model_Datasource_Database::get();
        if ($grupo) {
            return $db->getCol('
                SELECT c.rut
                FROM
                    contribuyente AS c
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                    JOIN contribuyente_dte AS cd ON cd.contribuyente = c.rut
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                WHERE
                    g.grupo = :grupo
                    AND cc.configuracion = \'ambiente\'
                    AND cc.variable = \'en_certificacion\'
                    AND cc.valor = \'0\'
                    AND cd.dte IN (39, 41)
            ', [':grupo' => $grupo]);
        } else {
            return $db->getCol('
                SELECT DISTINCT cd.contribuyente
                FROM
                    contribuyente_dte AS cd
                    JOIN contribuyente_config AS cc ON cc.contribuyente = cd.contribuyente
                WHERE
                    cc.configuracion = \'ambiente\'
                    AND cc.variable = \'en_certificacion\'
                    AND cc.valor = \'0\'
                    AND cd.dte IN (39, 41)
            ');
        }
    }

}
