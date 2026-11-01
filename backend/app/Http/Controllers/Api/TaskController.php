<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar tugas berhasil diambil',
            'data' => $tasks,
        ], 200);
    }

    // POST /api/tasks
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            $this->rules()
        );

        if ($validator->fails()) {
            // LOG 1: Validasi gagal
            Log::warning('TASK_VALIDATION_FAILED', [
                'action' => 'create',
                'user_id' => $request->user()->id,
                'invalid_fields' => array_keys($validator->errors()->toArray()),
            ]);

            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $task = Task::create([
                ...$validator->validated(),
                'user_id' => $request->user()->id,
            ]);

            // LOG 2: Data berhasil disimpan
            Log::info('TASK_CREATED', [
                'action' => 'create',
                'user_id' => $request->user()->id,
                'task_id' => $task->id,
                'result' => 'success',
            ]);

            return response()->json([
                'message' => 'Tugas berhasil ditambahkan',
                'data' => $task,
            ], 201);
        } catch (\Throwable $e) {
            // LOG 3: Kesalahan penyimpanan/server
            Log::error('TASK_CREATE_FAILED', [
                'action' => 'create',
                'user_id' => $request->user()->id,
                'error_type' => get_class($e),
                'result' => 'failed',
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan tugas.',
            ], 500);
        }
    }

    // GET /api/tasks/{task}
    public function show(Request $request, string $task): JsonResponse
    {
        $item = $this->findOwnedTask($request, $task);

        return response()->json([
            'message' => 'Detail tugas berhasil diambil',
            'data' => $item,
        ], 200);
    }

    // PUT /api/tasks/{task}
    public function update(Request $request, string $task): JsonResponse
    {
        $item = $this->findOwnedTask($request, $task);

        $validator = Validator::make(
            $request->all(),
            $this->rules()
        );

        if ($validator->fails()) {
            Log::warning('TASK_VALIDATION_FAILED', [
                'action' => 'update',
                'user_id' => $request->user()->id,
                'task_id' => $item->id,
                'invalid_fields' => array_keys($validator->errors()->toArray()),
            ]);

            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->update($validator->validated());

        Log::info('TASK_UPDATED', [
            'action' => 'update',
            'user_id' => $request->user()->id,
            'task_id' => $item->id,
            'result' => 'success',
        ]);

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
            'data' => $item->fresh(),
        ], 200);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Request $request, string $task): JsonResponse
    {
        $item = $this->findOwnedTask($request, $task);
        $taskId = $item->id;

        $item->delete();

        Log::info('TASK_DELETED', [
            'action' => 'delete',
            'user_id' => $request->user()->id,
            'task_id' => $taskId,
            'result' => 'success',
        ]);

        return response()->json([
            'message' => 'Tugas berhasil dihapus',
        ], 200);
    }

    private function findOwnedTask(Request $request, string $id): Task
    {
        return Task::where('user_id', $request->user()->id)
            ->findOrFail($id);
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => [
                'required',
                Rule::in(['task', 'meeting', 'event', 'study', 'personal']),
            ],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'deadline' => ['nullable', 'date'],
            'priority' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'required',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
        ];
    }
}
