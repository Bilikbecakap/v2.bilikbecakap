<?php

namespace App\Http\Controllers;

use App\Models\DatasetTranslate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DatasetTranslateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view dataset', only: ['index']),
            new Middleware('permission:create dataset', only: ['create', 'store']),
            new Middleware('permission:edit dataset', only: ['edit', 'update']),
            new Middleware('permission:delete dataset', only: ['destroy', 'bulkDelete']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DatasetTranslate::query();

        // Filter berdasarkan search (bahasa_belitung atau bahasa_indonesia)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('bahasa_belitung', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('bahasa_indonesia', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Sorting berdasarkan parameter
        if ($request->has('sort')) {
            $sortField = $request->sort;
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            
            if (in_array($sortField, ['bahasa_belitung', 'bahasa_indonesia', 'created_at'])) {
                $query->orderBy($sortField, $direction);
            }
        } else {
            // Default sorting - alfabetis
            $query->orderBy('bahasa_belitung', 'asc');
        }

        $datasets = $query->paginate(15)->appends($request->query());

        return Inertia::render('DatasetTranslate/Index', [
            'datasets' => $datasets,
            'search' => $request->search,
            'sort' => $request->sort,
            'direction' => $request->direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('DatasetTranslate/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bahasa_belitung' => 'required|string|max:255',
            'bahasa_indonesia' => 'required|string|max:255',
        ], [
            'bahasa_belitung.required' => 'Bahasa Belitung wajib diisi.',
            'bahasa_belitung.max' => 'Bahasa Belitung maksimal 255 karakter.',
            'bahasa_indonesia.required' => 'Bahasa Indonesia wajib diisi.',
            'bahasa_indonesia.max' => 'Bahasa Indonesia maksimal 255 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'bahasa_belitung' => $request->bahasa_belitung,
                'bahasa_indonesia' => $request->bahasa_indonesia,
            ];

            $dataset = DatasetTranslate::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($dataset)
                ->log('Created new dataset translate entry');

            DB::commit();

            return redirect()->route('dataset-translate.index')
                ->with('success', 'Dataset translate berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Gagal membuat dataset translate: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DatasetTranslate $datasetTranslate)
    {
        return Inertia::render('DatasetTranslate/Edit', [
            'dataset' => $datasetTranslate
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DatasetTranslate $datasetTranslate)
    {
        $request->validate([
            'bahasa_belitung' => 'required|string|max:255',
            'bahasa_indonesia' => 'required|string|max:255',
        ], [
            'bahasa_belitung.required' => 'Bahasa Belitung wajib diisi.',
            'bahasa_belitung.max' => 'Bahasa Belitung maksimal 255 karakter.',
            'bahasa_indonesia.required' => 'Bahasa Indonesia wajib diisi.',
            'bahasa_indonesia.max' => 'Bahasa Indonesia maksimal 255 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'bahasa_belitung' => $request->bahasa_belitung,
                'bahasa_indonesia' => $request->bahasa_indonesia,
            ];

            $datasetTranslate->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($datasetTranslate)
                ->log('Updated dataset translate entry');

            DB::commit();

            return redirect()->route('dataset-translate.index')
                ->with('success', 'Dataset translate berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Gagal mengupdate dataset translate: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DatasetTranslate $datasetTranslate)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($datasetTranslate)
                ->withProperties([
                    'deleted_dataset' => $datasetTranslate->toArray()
                ])
                ->log('Deleted dataset translate entry');

            $datasetTranslate->delete();

            DB::commit();

            return redirect()->route('dataset-translate.index')
                ->with('success', 'Dataset translate berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus dataset translate: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete dataset translate entries
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:dataset_translate,id'
        ]);

        DB::beginTransaction();
        try {
            $datasetRecords = DatasetTranslate::whereIn('id', $request->ids)->get();
            
            $deletedCount = 0;
            $errors = [];

            foreach ($datasetRecords as $dataset) {
                try {
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($dataset)
                        ->withProperties([
                            'deleted_dataset' => $dataset->toArray(),
                            'bulk_action' => true
                        ])
                        ->log('Bulk deleted dataset translate entry');

                    $dataset->delete();
                    $deletedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Gagal menghapus '{$dataset->bahasa_belitung}': " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deletedCount} dataset translate.";
            if (!empty($errors) && count($errors) <= 5) {
                $message .= " Error: " . implode(', ', $errors);
            } elseif (!empty($errors)) {
                $message .= " Ada " . count($errors) . " error saat menghapus.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus dataset translate: ' . $e->getMessage()]);
        }
    }

    /**
     * Import dataset from CSV/Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ], [
            'file.required' => 'File wajib diupload.',
            'file.mimes' => 'Format file harus CSV atau Excel (xlsx, xls).',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        DB::beginTransaction();
        try {
            // TODO: Implementasi import logic
            // Bisa menggunakan package seperti maatwebsite/excel
            
            DB::commit();
            return back()->with('success', 'Dataset berhasil diimport.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal import dataset: ' . $e->getMessage()]);
        }
    }

    /**
     * Export dataset to CSV/Excel
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $search = $request->get('search');

        $query = DatasetTranslate::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('bahasa_belitung', 'LIKE', '%' . $search . '%')
                  ->orWhere('bahasa_indonesia', 'LIKE', '%' . $search . '%');
            });
        }

        $query->orderBy('bahasa_belitung', 'asc');
        $datasets = $query->get(['id', 'bahasa_belitung', 'bahasa_indonesia', 'created_at']);

        $filename = 'dataset-translate-' . now()->format('Ymd_His');

        if ($format === 'xlsx') {
            return $this->exportXlsx($datasets, $filename);
        }

        return $this->exportCsv($datasets, $filename);
    }

    private function exportCsv($datasets, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($datasets) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No', 'Bahasa Belitung', 'Bahasa Indonesia', 'Tanggal Dibuat']);

            foreach ($datasets as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->bahasa_belitung,
                    $item->bahasa_indonesia,
                    $item->created_at?->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportXlsx($datasets, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dataset Translate');

        // Header row
        $headers = ['No', 'Bahasa Belitung', 'Bahasa Indonesia', 'Tanggal Dibuat'];
        foreach ($headers as $col => $label) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $label);
        }

        // Style header
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);

        // Data rows
        foreach ($datasets as $index => $item) {
            $row = $index + 2;
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $item->bahasa_belitung);
            $sheet->setCellValue("C{$row}", $item->bahasa_indonesia);
            $sheet->setCellValue("D{$row}", $item->created_at?->format('d/m/Y H:i'));
        }

        // Auto width
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xlsx\"",
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $headers);
    }

    /**
     * Get statistics about dataset
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => DatasetTranslate::count(),
                'recent' => DatasetTranslate::where('created_at', '>=', now()->subDays(7))->count(),
                'today' => DatasetTranslate::whereDate('created_at', today())->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }
}