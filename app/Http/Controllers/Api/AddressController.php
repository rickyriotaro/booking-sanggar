<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Get all addresses for authenticated user
     * GET /api/addresses
     */
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * Get primary address
     * GET /api/addresses/primary
     */
    public function getPrimary()
    {
        $address = Address::where('user_id', Auth::id())
            ->where('is_primary', true)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat utama belum diatur'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    /**
     * Store new address
     * POST /api/addresses
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // Jika ini alamat pertama user, set sebagai primary
        $addressCount = Address::where('user_id', Auth::id())->count();
        if ($addressCount == 0) {
            $validated['is_primary'] = true;
        }

        $address = Address::create($validated);

        // Jika set sebagai primary, update alamat lain
        if ($address->is_primary) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_primary' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan',
            'data' => $address
        ], 201);
    }

    /**
     * Show specific address
     * GET /api/addresses/{id}
     */
    public function show($id)
    {
        $address = Address::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    /**
     * Update address
     * PUT /api/addresses/{id}
     */
    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'label' => 'sometimes|string|max:50',
            'recipient_name' => 'sometimes|string|max:100',
            'phone_number' => 'sometimes|string|max:20',
            'full_address' => 'sometimes|string',
            'province' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
        ]);

        $address->update($validated);

        // Jika set sebagai primary, update alamat lain
        if (isset($validated['is_primary']) && $validated['is_primary']) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_primary' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui',
            'data' => $address->fresh()
        ]);
    }

    /**
     * Delete address
     * DELETE /api/addresses/{id}
     */
    public function destroy($id)
    {
        $address = Address::where('user_id', Auth::id())
            ->findOrFail($id);

        $wasPrimary = $address->is_primary;
        $address->delete();

        // Jika alamat yang dihapus adalah primary, set alamat pertama sebagai primary
        if ($wasPrimary) {
            $firstAddress = Address::where('user_id', Auth::id())->first();
            if ($firstAddress) {
                $firstAddress->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus'
        ]);
    }

    /**
     * Set address as primary
     * POST /api/addresses/{id}/set-primary
     */
    public function setPrimary($id)
    {
        $address = Address::where('user_id', Auth::id())
            ->findOrFail($id);

        // Set semua alamat jadi non-primary
        Address::where('user_id', Auth::id())->update(['is_primary' => false]);
        
        // Set alamat ini sebagai primary
        $address->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diubah',
            'data' => $address
        ]);
    }
}
