<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyApplicationFlowRequest;
use App\Http\Requests\MassStoreApplicationFlowRequest;
use App\Http\Requests\MassUpdateApplicationFlowRequest;
use App\Http\Requests\StoreApplicationFlowRequest;
use App\Http\Requests\UpdateApplicationFlowRequest;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ApplicationFlow;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFlowController extends APIController
{
    protected string $modelClass = ApplicationFlow::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('application_flow_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreApplicationFlowRequest $request)
    {
        abort_if(Gate::denies('application_flow_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $flow = ApplicationFlow::query()->create($request->all());

        return response()->json($flow, 201);
    }

    public function show(ApplicationFlow $flow)
    {
        abort_if(Gate::denies('show-object', $flow), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($flow);
    }

    public function update(UpdateApplicationFlowRequest $request, ApplicationFlow $flow)
    {
        abort_if(Gate::denies('edit-object', $flow), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $flow->update($request->all());

        return response()->json();
    }

    public function destroy(ApplicationFlow $flow)
    {
        abort_if(Gate::denies('application_flow_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $flow->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyApplicationFlowRequest $request)
    {
        abort_if(Gate::denies('application_flow_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ApplicationFlow::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreApplicationFlowRequest $request)
    {

        $createdIds = [];
        $model      = new ApplicationFlow();
        $fillable   = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Filtrer uniquement les colonnes du modèle
            $attributes = collect($item)->only($fillable)->toArray();

            /** @var ApplicationFlow $flow */
            $flow = ApplicationFlow::query()->create($attributes);

            $createdIds[] = $flow->id;
        }

        return response()->json([
            'status' => 'ok',
            'count'  => count($createdIds),
            'ids'    => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateApplicationFlowRequest $request)
    {
        $model    = new ApplicationFlow();
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var ApplicationFlow $flow */
            $flow = ApplicationFlow::query()->findOrFail($id);

            // Exclure l'id
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $flow->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
