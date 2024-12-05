<?php

declare(strict_types=1);

namespace Chopin\Users\Service;

//use Chopin\LaminasDb\DB;
use Illuminate\Support\Facades\DB;

class UsersService
{

    public function getUserAllowedPermission(int $usersId, bool $isShowName = false): array
    {
        // $PT = AbstractTableGateway::$prefixTable;
        $usersPermissionResultSet = DB::table('users')->select(['permission.uri', 'permission.name', 'permission.is_no_upgrade_use'])
            ->join('users_has_roles', 'users.id', '=', 'users_has_roles.users_id')
            ->join('roles', 'users_has_roles.roles_id', '=', 'roles.id')
            ->join('roles_has_permission', 'roles.id', '=', 'roles_has_permission.roles_id')
            ->join('permission', 'roles_has_permission.permission_id', '=', 'permission.id')
            ->where('users.id', '=', $usersId)
            ->whereNull(['users.deleted_at', 'permission.deleted_at'])
            ->get();
        if ($isShowName) {
            return $usersPermissionResultSet->toArray();
        }
        $user = [];
        foreach ($usersPermissionResultSet as $row) {
            $user[] = $row->uri;
        }
        unset($usersPermissionResultSet);

        return $user;
    }

    public function getDenyPermission(int $usersId): array
    {
        
        $allPermissionsResultset = DB::table('permission')->select('uri')->whereNull(['deleted_at'])->get();
        $all = [];
        $user = $this->getUserAllowedPermission($usersId);

        foreach ($allPermissionsResultset as $row) {
            $all[] = $row->uri;
        }
        unset($allPermissionsResultset);

        return array_diff($all, $user);
    }
}
