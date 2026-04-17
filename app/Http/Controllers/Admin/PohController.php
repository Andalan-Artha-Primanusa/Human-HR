<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PohController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $pohs = Poh::when($q, function ($query, $q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('code', 'like', "%$q%")
                      ->orWhere('address', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%")
                      ;
            })
            ->orderBy('name')
            ->paginate(20);
        return view('admin.pohs.index', compact('pohs', 'q'));
    }

    public function create()
    {
        return view('admin.pohs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:190',
            'code' => 'required|string|max:50|unique:pohs,code',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        Poh::create($data);
        return redirect()->route('admin.pohs.index')->with('success', 'POH berhasil ditambahkan.');
    }

    public function edit(Poh $poh)
    {
        return view('admin.pohs.edit', compact('poh'));
    }

    public function update(Request $request, Poh $poh)
    {
        $data = $request->validate([
            'name' => 'required|string|max:190',
            'code' => ['required','string','max:50',Rule::unique('pohs','code')->ignore($poh->id)],
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $poh->update($data);
        return redirect()->route('admin.pohs.index')->with('success', 'POH berhasil diupdate.');
    }

    public function destroy(Poh $poh)
    {
        $poh->delete();
        return redirect()->route('admin.pohs.index')->with('success', 'POH berhasil dihapus.');
    }
}
