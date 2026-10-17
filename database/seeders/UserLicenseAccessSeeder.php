<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class UserLicenseAccessSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@petrotech.id')->first();
        $adminId = $admin?->id;

        // Ensure vendors exist
        $vendorSchlumb = Vendor::where('name', 'schlumb')->first();
        $vendorLandmark = Vendor::where('name', 'landmark')->first();

        // 1. Update existing default user with Company A and assign to schlumb
        $defaultUser = User::where('email', 'user@petrotech.id')->first();
        if ($defaultUser) {
            $defaultUser->update(['company' => 'Company A']);
            if ($vendorSchlumb) {
                $defaultUser->accessibleVendors()->syncWithoutDetaching([
                    $vendorSchlumb->id => ['granted_by' => $adminId]
                ]);
            }
        }

        // 2. Demo User for Company A (Only accesses schlumb / Company A licenses)
        $userA = User::updateOrCreate(
            ['email' => 'user.companya@petrotech.id'],
            [
                'name'        => 'Rian Company A',
                'password'    => Hash::make('password'),
                'employee_id' => 'EMP-101',
                'department'  => 'Geology & Exploration',
                'company'     => 'Company A',
                'phone'       => '+62-21-5550101',
                'is_active'   => true,
            ]
        );
        $userA->syncRoles(['user']);
        if ($vendorSchlumb) {
            $userA->accessibleVendors()->syncWithPivotValues([$vendorSchlumb->id], [
                'granted_by' => $adminId,
            ]);
        }

        // 3. Demo User for Company B (Only accesses landmark / Company B licenses)
        $userB = User::updateOrCreate(
            ['email' => 'user.companyb@petrotech.id'],
            [
                'name'        => 'Dewi Company B',
                'password'    => Hash::make('password'),
                'employee_id' => 'EMP-102',
                'department'  => 'Reservoir Modeling',
                'company'     => 'Company B',
                'phone'       => '+62-21-5550102',
                'is_active'   => true,
            ]
        );
        $userB->syncRoles(['user']);
        if ($vendorLandmark) {
            $userB->accessibleVendors()->syncWithPivotValues([$vendorLandmark->id], [
                'granted_by' => $adminId,
            ]);
        }

        // 4. Demo User with Multi-Vendor Access (Company A & Company B)
        $userMulti = User::updateOrCreate(
            ['email' => 'user.multi@petrotech.id'],
            [
                'name'        => 'Hendra Multi Vendor',
                'password'    => Hash::make('password'),
                'employee_id' => 'EMP-103',
                'department'  => 'Integrated Operations',
                'company'     => 'Petrotech Consortium',
                'phone'       => '+62-21-5550103',
                'is_active'   => true,
            ]
        );
        $userMulti->syncRoles(['user']);
        $multiVendorIds = array_filter([$vendorSchlumb?->id, $vendorLandmark?->id]);
        if (!empty($multiVendorIds)) {
            $userMulti->accessibleVendors()->syncWithPivotValues($multiVendorIds, [
                'granted_by' => $adminId,
            ]);
        }

        // 5. Demo User with NO License Access (Accessing /admin/licenses is 403 Forbidden)
        $userNoAccess = User::updateOrCreate(
            ['email' => 'user.noaccess@petrotech.id'],
            [
                'name'        => 'Bambang No License',
                'password'    => Hash::make('password'),
                'employee_id' => 'EMP-104',
                'department'  => 'General Services',
                'company'     => 'Third Party Vendor',
                'phone'       => '+62-21-5550104',
                'is_active'   => true,
            ]
        );
        $userNoAccess->syncRoles(['user']);
        $userNoAccess->accessibleVendors()->detach();
    }
}
