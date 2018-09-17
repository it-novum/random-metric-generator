<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, version 3 of the License.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// 2.
//  If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//  under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//  License agreement and license key will be shipped with the order
//  confirmation.

namespace itnovum\metrics\generator;


class FakeHost {

    /**
     * @var string
     */
    private $hostname;

    /**
     * FakeHost constructor.
     * @param $hostname
     */
    public function __construct($hostname) {
        $this->hostname = $hostname;
    }

    public function getMetrics() {
        return [
            [
                'hostname'            => $this->hostname,
                'service_description' => 'CPU Load',
                'label'               => 'load1',
                'unit'                => null,
                'value'               => rand()
            ],
            [
                'hostname'            => $this->hostname,
                'service_description' => 'CPU Load',
                'label'               => 'load5',
                'unit'                => null,
                'value'               => rand()
            ],
            [
                'hostname'            => $this->hostname,
                'service_description' => 'CPU Load',
                'label'               => 'load15',
                'unit'                => null,
                'value'               => rand()
            ],

            [
                'hostname'            => $this->hostname,
                'service_description' => 'Ping',
                'label'               => 'rta',
                'unit'                => 'ms',
                'value'               => rand(1, 500)
            ],
            [
                'hostname'            => $this->hostname,
                'service_description' => 'Ping',
                'label'               => 'pl',
                'unit'                => '%s',
                'value'               => rand(0, 100)
            ],

            [
                'hostname'            => $this->hostname,
                'service_description' => 'Disk /',
                'label'               => '/',
                'unit'                => 'MB',
                'value'               => rand(1300, 5000)
            ]
        ];
    }


}