<?php

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreSubjectRequest;
use App\Http\Requests\Workload\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Subject::all());
    }

    public function show($id)
    {
        try {
            return response()->json(Subject::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->validated());

        return response()->json($subject, 201);
    }

    public function update(UpdateSubjectRequest $request, $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->update($request->validated());

            return response()->json($subject);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            return response()->json(['message' => 'Subject deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }
}
