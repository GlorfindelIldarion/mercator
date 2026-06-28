<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cartographer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    private $models = [
        'Entity' => 'cruds.entity.title',
        'Relation' => 'cruds.relation.title',
        'Process' => 'cruds.process.title',
        'Operation' => 'cruds.operation.title',
        'Actor' => 'cruds.actor.title',
        'Activity' => 'cruds.activity.title',
        'Task' => 'cruds.task.title',
        'Information' => 'cruds.information.title',
        'ApplicationBlock' => 'cruds.applicationBlock.title',
        'Application' => 'cruds.application.title',
        'ApplicationService' => 'cruds.applicationService.title',
        'Database' => 'cruds.database.title',
        'ApplicationFlow' => 'cruds.flux.title',
        'ZoneAdmin' => 'cruds.zoneAdmin.title',
        'Annuaire' => 'cruds.annuaire.title',
        'ForestAd' => 'cruds.forestAd.title',
        'Domain' => 'cruds.domaine.title',
        'Network' => 'cruds.network.title',
        'Subnetwork' => 'cruds.subnetwork.title',
        'Gateway' => 'cruds.gateway.title',
        'ExternalConnectedEntity' => 'cruds.externalConnectedEntity.title',
        'NetworkSwitch' => 'cruds.networkSwitch.title',
        'Router' => 'cruds.router.title',
        'SecurityDevice' => 'cruds.securityDevice.title',
        'DhcpServer' => 'cruds.dhcpServer.title',
        'Dnsserver' => 'cruds.dnsserver.title',
        'LogicalServer' => 'cruds.logicalServer.title',
        'Site' => 'cruds.site.title',
        'Building' => 'cruds.building.title',
        'Bay' => 'cruds.bay.title',
        'PhysicalServer' => 'cruds.physicalServer.title',
        'Workstation' => 'cruds.workstation.title',
        'StorageDevice' => 'cruds.storageDevice.title',
        'Peripheral' => 'cruds.peripheral.title',
        'Phone' => 'cruds.phone.title',
        'PhysicalSwitch' => 'cruds.physicalSwitch.title',
        'PhysicalRouter' => 'cruds.physicalRouter.title',
        'WifiTerminal' => 'cruds.wifiTerminal.title',
        'PhysicalSecurityDevice' => 'cruds.physicalSecurityDevice.title',
        'Wan' => 'cruds.wan.title',
        'Man' => 'cruds.man.title',
        'Lan' => 'cruds.lan.title',
        'Vlan' => 'cruds.vlan.title',
        'ApplicationModule' => 'cruds.applicationModule.title',
        'MacroProcessus' => 'cruds.macroProcessus.title',
        'Certificate' => 'cruds.certificate.title',
        'DataProcessing' => 'cruds.dataProcessing.title',
        'SecurityControl' => 'cruds.securityControl.title',
        'LogicalFlow' => 'cruds.logicalFlow.title',
        'Graph' => 'cruds.graph.title',
        'Container' => 'cruds.container.title',
        'Cluster' => 'cruds.cluster.title',
    ];

    public function search(Request $request)
    {
        $term = $request->input('search');

        $searchableData = [];

        if (($term === null) || (mb_strlen($term) < 3)) {
            return view('admin.search', compact('searchableData'));
        }

        $driver = DB::connection()->getDriverName();

        foreach ($this->models as $model => $translation) {
            $modelClass = 'App\\Models\\'.$model;

            $fields = property_exists($modelClass, 'searchable') ? $modelClass::$searchable : [];

            if (empty($fields)) {
                continue;
            }

            $escaped = mb_strtolower($this->escapeLike($term));
            $results = Cartographer::scopedQueryByClass($modelClass)
                ->where(function ($q) use ($fields, $escaped, $driver) {
                    foreach ($fields as $field) {
                        // SQLite n'a aucun caractère d'échappement LIKE par défaut :
                        // il faut le déclarer. MySQL/MariaDB/PostgreSQL utilisent '\'
                        // par défaut, inutile de le répéter.
                        $sql = 'LOWER('.$field.') LIKE ?';
                        if ($driver === 'sqlite') {
                            $sql .= " ESCAPE '\\'";
                        }
                        $q->orWhereRaw($sql, ['%'.$escaped.'%']);
                    }
                })
                ->take(100)
                ->get();

            $formattedFields = [];
            foreach ($fields as $field) {
                $formattedFields[$field] = Str::title(str_replace('_', ' ', $field));
            }

            foreach ($results as $result) {
                $searchableData[] = [
                    'instance' => $result,
                    'data' => $result->only($fields),
                    'model' => $model,
                    'name' => trans($translation),
                    'fields' => $fields,
                    'fields_formated' => $formattedFields,
                    'url' => '/admin/'.Str::plural(Str::snake($model, '-')).'/'.$result->getKey(),
                ];
            }
        }

        return view('admin.search', compact('searchableData'));
    }

    private function escapeLike(string $value): string
    {
        // '=' comme caractère d'échappement (neutre dans les littéraux SQL des 4 moteurs).
        // Le caractère d'échappement lui-même doit être échappé en premier.
        return str_replace(['=', '%', '_'], ['==', '=%', '=_'], $value);
    }
}
