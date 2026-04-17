<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poh;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PohController extends Controller
{
    public function index(Request $request)
    {
        $query = Poh::query();
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where('name', 'like', "%$q%")
                  ->orWhere('code', 'like', "%$q%")
                  ->orWhere('address', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%");
        }
        return JsonResource::collection($query->orderBy('name')->paginate(20));
    }

    public function show(Poh $poh)
    {
        return new JsonResource($poh);
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
        $poh = Poh::create($data);
        return new JsonResource($poh);
    }

    public function update(Request $request, Poh $poh)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:190',
            'code' => 'sometimes|required|string|max:50|unique:pohs,code,' . $poh->id,
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $poh->update($data);
        return new JsonResource($poh);
    }

    public function destroy(Poh $poh)
    {
        $poh->delete();
        return response()->json(['success' => true]);
    }
}
