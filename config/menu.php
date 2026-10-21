<?php

return [
    'vdi_access'         => filter_var(env('MENU_VDI_ACCESS', true), FILTER_VALIDATE_BOOLEAN),
    'ticketing'          => filter_var(env('MENU_TICKETING', true), FILTER_VALIDATE_BOOLEAN),
    'license_management' => filter_var(env('MENU_LICENSE_MANAGEMENT', true), FILTER_VALIDATE_BOOLEAN),
    'vm_monitoring'      => filter_var(env('MENU_VM_MONITORING', true), FILTER_VALIDATE_BOOLEAN),
    'vm_management'      => filter_var(env('MENU_VM_MANAGEMENT', true), FILTER_VALIDATE_BOOLEAN),
    'storage_monitor'    => filter_var(env('MENU_STORAGE_MONITOR', true), FILTER_VALIDATE_BOOLEAN),
    'analytics'          => filter_var(env('MENU_ANALYTICS', true), FILTER_VALIDATE_BOOLEAN),
    'group_management'   => filter_var(env('MENU_GROUP_MANAGEMENT', true), FILTER_VALIDATE_BOOLEAN),
    'user_management'    => filter_var(env('MENU_USER_MANAGEMENT', true), FILTER_VALIDATE_BOOLEAN),
    'role_management'    => filter_var(env('MENU_ROLE_MANAGEMENT', true), FILTER_VALIDATE_BOOLEAN),
];
