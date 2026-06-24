<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMacroProcessusRequest;
use App\Http\Requests\StoreMacroProcessusRequest;
use App\Http\Requests\UpdateMacroProcessusRequest;
use App\Models\Cartographer;
use App\Models\MacroProcessus;
use App\Models\Process;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MacroProcessusController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('macro_processus_access') ? null : Cartographer::allowedIdsFor($user, MacroProcessus::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        // $macroProcessuses = MacroProcessus::orderBy('name')->get();
        $macroProcessuses = MacroProcessus::with('processes')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (MacroProcessus::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.macroProcessuses.index', compact('macroProcessuses'));
    }

    public function create()
    {
        abort_if(Gate::denies('macro_processus_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::orderBy('name')->pluck('name', 'id');
        // lists
        $owner_list = MacroProcessus::select('owner')->where('owner', '<>', null)->distinct()->orderBy('owner')->pluck('owner');

        return view('admin.macroProcessuses.create', compact('processes', 'owner_list'));
    }

    public function store(StoreMacroProcessusRequest $request)
    {
        $macroProcessus = MacroProcessus::create($request->all());

        Process::where('macroprocess_id', $macroProcessus->id)
            ->update(['macroprocess_id' => null]);

        Process::whereIn('id', $request->input('processes', []))
            ->update(['macroprocess_id' => $macroProcessus->id]);

        return redirect()->route('admin.macro-processuses.index');
    }

    public function edit(MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('edit-object', $macroProcessus), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::orderBy('name')->pluck('name', 'id');
        // lists
        $owner_list = MacroProcessus::select('owner')->where('owner', '<>', null)->distinct()->orderBy('owner')->pluck('owner');

        $macroProcessus->load('processes');

        return view(
            'admin.macroProcessuses.edit',
            compact('processes', 'macroProcessus', 'owner_list')
        );
    }

    public function update(UpdateMacroProcessusRequest $request, MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('edit-object', $macroProcessus), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $macroProcessus->update($request->all());

        // $macroProcessus->processes()->sync($request->input('processes', []));
        Process::where('macroprocess_id', $macroProcessus->id)
            ->update(['macroprocess_id' => null]);

        Process::whereIn('id', $request->input('processes', []))
            ->update(['macroprocess_id' => $macroProcessus->id]);

        return redirect()->route('admin.macro-processuses.index');
    }

    public function show(MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('show-object', $macroProcessus), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $macroProcessus->load('processes');

        return view('admin.macroProcessuses.show', compact('macroProcessus'));
    }

    public function destroy(MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('macro_processus_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $macroProcessus->delete();

        return redirect()->route('admin.macro-processuses.index');
    }

    public function massDestroy(MassDestroyMacroProcessusRequest $request)
    {
        MacroProcessus::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
