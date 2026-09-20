<?php

namespace App\Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Account\Http\Requests\StoreAddressRequest;
use App\Modules\Account\Http\Requests\UpdateAddressRequest;
use App\Modules\Account\Models\UserAddress;
use App\Modules\Account\Services\AccountProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private readonly AccountProfileService $accountProfileService) {}

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->accountProfileService->storeAddress(
            $request->user(),
            $request->safe()->only(['label', 'full_address', 'pincode', 'district', 'state', 'is_default']),
        );

        return redirect()
            ->route('account.addresses')
            ->with('success', 'Address added.');
    }

    public function update(UpdateAddressRequest $request, UserAddress $address): RedirectResponse
    {
        $this->accountProfileService->updateAddress(
            $request->user(),
            $address,
            $request->safe()->only(['label', 'full_address', 'pincode', 'district', 'state', 'is_default']),
        );

        return redirect()
            ->route('account.addresses')
            ->with('success', 'Address updated.');
    }

    public function destroy(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless(
            $request->user() !== null && (int) $address->user_id === (int) $request->user()->id,
            403,
        );

        $this->accountProfileService->deleteAddress($request->user(), $address);

        return redirect()
            ->route('account.addresses')
            ->with('success', 'Address removed.');
    }
}
