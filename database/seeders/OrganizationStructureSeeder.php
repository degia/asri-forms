<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Directorate;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\SubDepartement;
use Illuminate\Database\Seeder;

class OrganizationStructureSeeder extends Seeder
{
    public function run(): void
    {
        $directorates = [
            'Direktorat Operasional' => [
                'code' => 'DO',
                'divisis' => [
                    'Divisi Operasional Bisnis' => [
                        'code' => 'DOB',
                        'departements' => [
                            'Departemen Operasional Site' => ['code' => 'DOS', 'subs' => [
                                'Sub Departemen Operasional Harian' => 'SOH',
                            ]],
                            'Departemen Logistik' => ['code' => 'DLG', 'subs' => []],
                        ],
                    ],
                    'Divisi Support Operasional' => [
                        'code' => 'DSO',
                        'departements' => [
                            'Departemen Maintenance' => ['code' => 'DMN', 'subs' => [
                                'Sub Departemen Perbaikan' => 'SPB',
                            ]],
                        ],
                    ],
                ],
            ],
            'Direktorat Keuangan' => [
                'code' => 'DF',
                'divisis' => [
                    'Divisi Akuntansi' => [
                        'code' => 'DAK',
                        'departements' => [
                            'Departemen Finance' => ['code' => 'DFN', 'subs' => [
                                'Sub Departemen Treasury' => 'STR',
                            ]],
                            'Departemen Pajak' => ['code' => 'DPJ', 'subs' => []],
                        ],
                    ],
                ],
            ],
            'Direktorat IT' => [
                'code' => 'DI',
                'divisis' => [
                    'Divisi Infrastruktur IT' => [
                        'code' => 'DII',
                        'departements' => [
                            'Departemen Infrastruktur Jaringan' => ['code' => 'DIJ', 'subs' => []],
                            'Departemen IT Support' => ['code' => 'DIS', 'subs' => [
                                'Sub Departemen Helpdesk' => 'SHD',
                                'Sub Departemen Asset IT' => 'SAT',
                            ]],
                        ],
                    ],
                    'Divisi Aplikasi IT' => [
                        'code' => 'DAI',
                        'departements' => [
                            'Departemen Aplikasi & Database' => ['code' => 'DAD', 'subs' => []],
                            'Departemen Development' => ['code' => 'DDE', 'subs' => [
                                'Sub Departemen Mobile Development' => 'SMD',
                            ]],
                        ],
                    ],
                ],
            ],
            'Direktorat Marketing' => [
                'code' => 'DM',
                'divisis' => [
                    'Divisi Marketing' => [
                        'code' => 'DMK',
                        'departements' => [
                            'Departemen Brand Marketing' => ['code' => 'DBM', 'subs' => []],
                        ],
                    ],
                    'Divisi Sales' => [
                        'code' => 'DSL',
                        'departements' => [
                            'Departemen Sales Area' => ['code' => 'DSA', 'subs' => []],
                        ],
                    ],
                ],
            ],
            'Direktorat HR & GA' => [
                'code' => 'DH',
                'divisis' => [
                    'Divisi Human Resource' => [
                        'code' => 'DHR',
                        'departements' => [
                            'Departemen Recruitment' => ['code' => 'DRC', 'subs' => []],
                            'Departemen Training' => ['code' => 'DTR', 'subs' => []],
                        ],
                    ],
                    'Divisi General Affairs' => [
                        'code' => 'DGA',
                        'departements' => [
                            'Departemen Facility' => ['code' => 'DFC', 'subs' => []],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($directorates as $directorateName => $directorateData) {
            $directorate = Directorate::firstOrCreate(
                ['name' => $directorateName],
                ['code' => $directorateData['code']]
            );

            foreach ($directorateData['divisis'] as $divisiName => $divisiData) {
                $divisi = Divisi::firstOrCreate(
                    ['directorate_id' => $directorate->id, 'name' => $divisiName],
                    ['code' => $divisiData['code']]
                );

                foreach ($divisiData['departements'] as $departementName => $departementData) {
                    $departement = Departement::firstOrCreate(
                        ['divisi_id' => $divisi->id, 'name' => $departementName],
                        ['code' => $departementData['code']]
                    );

                    foreach ($departementData['subs'] as $subName => $subCode) {
                        SubDepartement::firstOrCreate(
                            ['departement_id' => $departement->id, 'name' => $subName],
                            ['code' => $subCode]
                        );
                    }
                }
            }
        }

        $positions = [
            ['name' => 'Direktur', 'code' => 'DIR', 'sort_order' => 1],
            ['name' => 'General Manager', 'code' => 'GM', 'sort_order' => 2],
            ['name' => 'Manager', 'code' => 'MGR', 'sort_order' => 3],
            ['name' => 'Supervisor', 'code' => 'SPV', 'sort_order' => 4],
            ['name' => 'Staff', 'code' => 'STF', 'sort_order' => 5],
            ['name' => 'Teknisi IT', 'code' => 'TIT', 'sort_order' => 6],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['name' => $position['name']], $position);
        }
    }
}
