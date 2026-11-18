<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CityCorporation;
use App\Models\District;
use App\Models\Division;
use App\Models\Education;
use App\Models\EducationYear;
use App\Models\Pourashava;
use App\Models\PourashavaWard;
use App\Models\ReporterArea;
use App\Models\ReporterType;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LookupController extends Controller
{
    /**
     * Registry that maps a "table key" → model, columns, validation rules,
     * and relations needed for the Vue form (e.g. division → districts).
     */
    protected function registry(): array
    {
        return [
            'divisions' => [
                'model'      => Division::class,
                'label'      => 'Division',
                'manualId'   => true,
                'columns'    => ['id', 'name', 'bn_name', 'url'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'id'      => 'nullable|integer|min:1',
                    'name'    => 'required|string|max:25',
                    'bn_name' => 'required|string|max:25',
                    'url'     => 'nullable|string|max:50',
                ],
                'relations'  => [],
            ],
            'districts' => [
                'model'      => District::class,
                'label'      => 'District',
                'manualId'   => true,
                'columns'    => ['id', 'division_id', 'name', 'bn_name', 'url'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'id'          => 'nullable|integer|min:1',
                    'division_id' => 'required|integer|exists:divisions,id',
                    'name'        => 'required|string|max:25',
                    'bn_name'     => 'required|string|max:25',
                    'url'         => 'nullable|string|max:50',
                ],
                'relations'  => ['division_id' => 'divisions'],
            ],
            'upazilas' => [
                'model'      => Upazila::class,
                'label'      => 'Upazila',
                'manualId'   => true,
                'columns'    => ['id', 'district_id', 'name', 'bn_name', 'url'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'id'          => 'nullable|integer|min:1',
                    'district_id' => 'required|integer|exists:districts,id',
                    'name'        => 'required|string|max:25',
                    'bn_name'     => 'required|string|max:25',
                    'url'         => 'nullable|string|max:50',
                ],
                'relations'  => ['district_id' => 'districts'],
            ],
            'unions' => [
                'model'      => Union::class,
                'label'      => 'Union',
                'manualId'   => true,
                'columns'    => ['id', 'upazilla_id', 'name', 'bn_name', 'url'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'id'          => 'nullable|integer|min:1',
                    'upazilla_id' => 'required|integer|exists:upazilas,id',
                    'name'        => 'required|string|max:25',
                    'bn_name'     => 'required|string|max:25',
                    'url'         => 'nullable|string|max:50',
                ],
                'relations'  => ['upazilla_id' => 'upazilas'],
            ],
            'city_corporations' => [
                'model'      => CityCorporation::class,
                'label'      => 'City Corporation',
                'manualId'   => false,
                'columns'    => ['id', 'district_id', 'name', 'bn_name'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'district_id' => 'required|integer|exists:districts,id',
                    'name'        => 'required|string|max:100',
                    'bn_name'     => 'nullable|string|max:100',
                ],
                'relations'  => ['district_id' => 'districts'],
            ],
            'wards' => [
                'model'      => Ward::class,
                'label'      => 'Ward',
                'manualId'   => false,
                'columns'    => ['id', 'city_corporation_id', 'name', 'bn_name'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'city_corporation_id' => 'required|integer|exists:city_corporations,id',
                    'name'                => 'required|string|max:100',
                    'bn_name'             => 'nullable|string|max:100',
                ],
                'relations'  => ['city_corporation_id' => 'city_corporations'],
            ],
            'pourashavas' => [
                'model'      => Pourashava::class,
                'label'      => 'Pourashava',
                'manualId'   => false,
                'columns'    => ['id', 'district_id', 'name', 'bn_name', 'category'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'district_id' => 'required|integer|exists:districts,id',
                    'name'        => 'required|string|max:100',
                    'bn_name'     => 'nullable|string|max:100',
                    'category'    => 'nullable|in:A,B,C',
                ],
                'relations'  => ['district_id' => 'districts'],
            ],
            'pourashava_wards' => [
                'model'      => PourashavaWard::class,
                'label'      => 'Pourashava Ward',
                'manualId'   => false,
                'columns'    => ['id', 'pourashava_id', 'name', 'bn_name'],
                'searchable' => ['name', 'bn_name'],
                'rules'      => [
                    'pourashava_id' => 'required|integer|exists:pourashavas,id',
                    'name'          => 'required|string|max:100',
                    'bn_name'       => 'required|string|max:100',
                ],
                'relations'  => ['pourashava_id' => 'pourashavas'],
            ],
            'education' => [
                'model'      => Education::class,
                'label'      => 'Education',
                'manualId'   => false,
                'columns'    => ['id', 'name'],
                'searchable' => ['name'],
                'rules'      => [
                    'name' => 'required|string|max:255',
                ],
                'relations'  => [],
            ],
            'education_years' => [
                'model'      => EducationYear::class,
                'label'      => 'Education Year',
                'manualId'   => false,
                'columns'    => ['id', 'year'],
                'searchable' => ['year'],
                'rules'      => [
                    'year' => 'required|string|max:255',
                ],
                'relations'  => [],
            ],
            'reporter_types' => [
                'model'      => ReporterType::class,
                'label'      => 'Reporter Type',
                'manualId'   => false,
                'columns'    => ['id', 'name'],
                'searchable' => ['name'],
                'rules'      => [
                    'name' => 'required|string|max:255',
                ],
                'relations'  => [],
            ],
            'reporter_areas' => [
                'model'      => ReporterArea::class,
                'label'      => 'Reporter Area',
                'manualId'   => false,
                'columns'    => ['id', 'name'],
                'searchable' => ['name'],
                'rules'      => [
                    'name' => 'required|string|max:255',
                ],
                'relations'  => [],
            ],
        ];
    }

    /* ==================== RENDER PAGE ==================== */
    public function index()
    {
        return Inertia::render('Admin/Lookups/Index');
    }

    /* ==================== LIST + SEARCH ==================== */
    public function list(Request $request, string $table)
    {
        $meta = $this->meta($table);

        $query = $meta['model']::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($meta, $search) {
                foreach ($meta['searchable'] as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        $rows = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json($rows);
    }

    /* ==================== OPTIONS FOR SELECTS ==================== */
    public function options(string $table)
    {
        $meta = $this->meta($table);

        // Only select columns that actually exist on the model's table.
        // We need `id` always; `name`, `bn_name`, `year` are optional labels.
        $available = Schema::getColumnListing((new $meta['model'])->getTable());

        $cols = ['id'];
        foreach (['name', 'bn_name', 'year'] as $c) {
            if (in_array($c, $available, true)) {
                $cols[] = $c;
            }
        }

        $rows = $meta['model']::query()
            ->select($cols)
            ->orderBy('id')
            ->get();

        return response()->json($rows);
    }

    /* ==================== STORE ==================== */
    public function store(Request $request, string $table)
    {
        $meta = $this->meta($table);

        $data = Validator::make($request->all(), $meta['rules'])->validate();

        if (! $meta['manualId']) {
            unset($data['id']);
        }

        $row = $meta['model']::create($data);

        return response()->json([
            'success' => true,
            'message' => "{$meta['label']} created successfully.",
            'row'     => $row,
        ]);
    }

    /* ==================== UPDATE ==================== */
    public function update(Request $request, string $table, $id)
    {
        $meta = $this->meta($table);
        $row = $meta['model']::findOrFail($id);

        $rules = $meta['rules'];
        // On update, ID is immutable
        if (isset($rules['id'])) {
            unset($rules['id']);
        }

        $data = Validator::make($request->all(), $rules)->validate();

        if (! $meta['manualId']) {
            unset($data['id']);
        }

        $row->update($data);

        return response()->json([
            'success' => true,
            'message' => "{$meta['label']} updated successfully.",
            'row'     => $row->fresh(),
        ]);
    }

    /* ==================== DELETE ==================== */
    public function destroy(string $table, $id)
    {
        $meta = $this->meta($table);
        $row = $meta['model']::findOrFail($id);

        try {
            $row->delete();
            return response()->json([
                'success' => true,
                'message' => "{$meta['label']} deleted successfully.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — this record is referenced by other data.',
            ], 409);
        }
    }

    /* ==================== HELPER ==================== */
    protected function meta(string $table): array
    {
        $all = $this->registry();
        abort_unless(isset($all[$table]), 404, 'Unknown table.');
        return $all[$table];
    }
}
