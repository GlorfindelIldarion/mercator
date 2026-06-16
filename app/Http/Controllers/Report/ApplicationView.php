<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Cartographer;
use App\Models\Database;
use Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ApplicationView extends Controller
{
    /**
     * Prepare data for the applications report view based on the requested application block and application.
     *
     * Persists selected `applicationBlock` and `application` values in session, applies those selections to filter
     * application-related collections (blocks, applications, services, modules, databases, and fluxes), and returns
     * the view used to render the applications report. Access is denied with a 403 response when the current user
     * lacks the `reports_access` permission.
     *
     * @param  \Illuminate\Http\Request  $request  Request that may contain `applicationBlock` and `application` parameters used to filter results; values are stored in session when present.
     * @return \Illuminate\View\View A view for 'admin/reports/applications' populated with the following keys: `all_applicationBlocks`, `all_applications`, `applicationBlocks`, `applications`, `applicationServices`, `applicationModules`, `databases`, and `fluxes`.
     */
    public function generate(Request $request): View
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([ApplicationBlock::class, Application::class, ApplicationService::class, ApplicationModule::class, Database::class, ApplicationFlow::class]);
        abort_if(!$allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->applicationBlock == null) {
            $request->session()->put('applicationBlock', null);
            $applicationBlock = null;
            $request->session()->put('application', null);
            $application = null;
        } else {
            $applicationBlock = intval($request->applicationBlock);
            $request->session()->put('applicationBlock', $applicationBlock);

            if ($request->application == null) {
                $request->session()->put('application', null);
                $application = null;
            } else {
                $application = intval($request->application);
                $request->session()->put('application', $application);
            }
        }

        $all_applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())->orderBy('name')->get();

        if ($applicationBlock !== null) {
            $applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())
                ->where('id', $applicationBlock)
                ->get();

            $all_applications = Cartographer::scopedQuery(Application::query())
                ->where('application_block_id', $applicationBlock)
                ->orderBy('name')
                ->get();

            $applications = Cartographer::scopedQuery(Application::query())
                ->with(['services', 'databases'])
                ->when($application !== null, fn ($q) => $q->where('id', $application))
                ->when($application === null, fn ($q) => $q->where('application_block_id', $applicationBlock))
                ->orderBy('name')
                ->get();

            $serviceIds = $applications->flatMap(fn ($app) => $app->services->pluck('id'))->unique();

            $applicationServices = Cartographer::scopedQuery(ApplicationService::query())
                ->with(['modules'])
                ->whereIn('id', $serviceIds)
                ->orderBy('name')
                ->get();

            $moduleIds = $applicationServices->flatMap(fn ($svc) => $svc->modules->pluck('id'))->unique();

            $applicationModules = Cartographer::scopedQuery(ApplicationModule::query())
                ->whereIn('id', $moduleIds)
                ->orderBy('name')
                ->get();

            $databaseIds = $applications->flatMap(fn ($app) => $app->databases->pluck('id'))->unique();

            $databases = Cartographer::scopedQuery(Database::query())
                ->whereIn('id', $databaseIds)
                ->orderBy('name')
                ->get();

            $appIds = $applications->pluck('id');
            $flows = Cartographer::scopedQuery(ApplicationFlow::query())
                ->where(function ($q) use ($appIds, $moduleIds, $databaseIds) {
                    $q->whereIn('application_source_id', $appIds)
                        ->orWhereIn('application_dest_id', $appIds)
                        ->orWhereIn('module_source_id', $moduleIds)
                        ->orWhereIn('module_dest_id', $moduleIds)
                        ->orWhereIn('database_source_id', $databaseIds)
                        ->orWhereIn('database_dest_id', $databaseIds);
                })
                ->orderBy('name')
                ->get();
        } else {
            $applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())->orderBy('name')->get();
            $applications = Cartographer::scopedQuery(Application::query())->with(['services', 'databases'])->orderBy('name')->get();
            $applicationServices = Cartographer::scopedQuery(ApplicationService::query())->with(['modules'])->orderBy('name')->get();
            $applicationModules = Cartographer::scopedQuery(ApplicationModule::query())->orderBy('name')->get();
            $databases = Cartographer::scopedQuery(Database::query())->orderBy('name')->get();
            $flows = Cartographer::scopedQuery(ApplicationFlow::query())->orderBy('name')->get();
            $all_applications = null;
        }

        return view('admin/reports/applications')
            ->with('all_applicationBlocks', $all_applicationBlocks)
            ->with('all_applications', $all_applications)
            ->with('applicationBlocks', $applicationBlocks)
            ->with('applications', $applications)
            ->with('applicationServices', $applicationServices)
            ->with('applicationModules', $applicationModules)
            ->with('databases', $databases)
            ->with('flows', $flows);
    }
}
