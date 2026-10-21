<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', ['user' => auth()->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'department'  => ['nullable', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateMenu(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403, 'Unauthorized action. Super Admin only.');

        $menus = [
            'MENU_VDI_ACCESS'         => $request->boolean('vdi_access'),
            'MENU_TICKETING'          => $request->boolean('ticketing'),
            'MENU_LICENSE_MANAGEMENT' => $request->boolean('license_management'),
            'MENU_VM_MONITORING'      => $request->boolean('vm_monitoring'),
            'MENU_VM_MANAGEMENT'      => $request->boolean('vm_management'),
            'MENU_STORAGE_MONITOR'    => $request->boolean('storage_monitor'),
            'MENU_ANALYTICS'          => $request->boolean('analytics'),
            'MENU_GROUP_MANAGEMENT'   => $request->boolean('group_management'),
            'MENU_USER_MANAGEMENT'    => $request->boolean('user_management'),
            'MENU_ROLE_MANAGEMENT'    => $request->boolean('role_management'),
        ];

        $this->updateEnvFile($menus);

        // Clear config cache so .env changes are picked up immediately
        Artisan::call('config:clear');

        return back()->with('success', 'Menu visibility updated successfully.');
    }

    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $valueString = $value ? 'true' : 'false';

            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$valueString}", $envContent);
            } else {
                $envContent .= PHP_EOL . "{$key}={$valueString}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
