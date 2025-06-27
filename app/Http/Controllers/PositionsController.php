<?php

namespace App\Http\Controllers;

use App\Models\Positions;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = Positions::paginate(5);
        return view('positions.index', compact('positions'));
        // --- IGNORE ---
        // return view('index', ['positions' => $positions]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',

        ]);
        Positions::create([
            'name' => $request->name,
            'description' => $request->description,

        ]);
        return redirect()->route('positions.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Positions $positions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Positions $positions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',

        ]);
        $positions = Positions::findOrFail($id);
        $positions->update([
            'name' => $request->name,
            'description' => $request->description,

        ]);
        return redirect()->route('positions.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $positions = Positions::findOrFail($id);
        $positions->delete();
        return redirect()->route('positions.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        // --- IGNORE ---
        // return redirect()->route('index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
