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

// título del módulo
\sowerphp\core\Configure::write('module.title', 'Facturación electrónica');

// Menú para el módulo
\sowerphp\core\Configure::write('nav.module', [
    '/documentos/emitir' => [
        'name' => 'Emitir documento',
        'desc' => 'Emitir documento tributario electrónico (DTE)',
        'icon' => 'fa fa-file-text',
    ],
    '/dte_tmps' => [
        'name' => 'Documentos temporales',
        'desc' => 'Revisar documentos temporales',
        'icon' => 'fa fa-file-o',
    ],
    '/dte_emitidos/listar' => [
        'name' => 'Documentos emitidos',
        'desc' => 'Revisar documentos emitidos',
        'icon' => 'fa fa-sign-out',
    ],
    '/dte_recibidos/listar' => [
        'name' => 'Documentos recibidos',
        'desc' => 'Revisar documentos recibidos',
        'icon' => 'fa fa-sign-in',
    ],
    '/dte_intercambios' => [
        'name' => 'Bandeja de intercambio',
        'desc' => 'Menú de intercambio de DTE entre contribuyentes',
        'icon' => 'fa fa-exchange',
    ],
    '/dte_compras' => [
        'name' => 'Libro de compras',
        'desc' => 'Acceder al Libro de Compras',
        'icon' => 'fa fa-book',
    ],
    '/dte_ventas' => [
        'name' => 'Libro de ventas',
        'desc' => 'Acceder al Libro de Ventas',
        'icon' => 'fa fa-book',
    ],
    '/dte_guias' => [
        'name' => 'Libro de guías',
        'desc' => 'Acceder al Libro de Guías de despacho',
        'icon' => 'fa fa-book',
    ],
    '/dte_boletas' => [
        'name' => 'Libro de boletas',
        'desc' => 'Acceder al Libro de Boletas',
        'icon' => 'fa fa-book',
    ],
    '/dte_boleta_consumos/listar/1/dia/D' => [
        'name' => 'Consumo de folios',
        'desc' => 'Reporte de consumo de folios de boletas',
        'icon' => 'fa fa-archive',
    ],
    '/admin' => [
        'name' => 'Administración',
        'desc' => 'Administración del módulo DTE',
        'icon' => 'fa fa-cogs',
    ],
]);
