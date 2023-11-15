<?php

namespace Juanfv2\BaseCms\Commands;

use App\Models\Auth\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CreateMenus extends Command
{
    public string $separator = '# ---------------------------------------------------------------------------- #';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base-cms:menus {--country=} {--c|create} {--t|truncate} {--p|permissions} {--s|sub_permissions} {--a|admin} {--j|json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(-c): create permissions from json files "data/_.menus/p.*.json"
                              (-t): truncate permissions table
                              (-a): add permissions to roles   from "z_base_cms_menus_roles"
                              (-s): add/update sub-permissions from "z_base_cms_menus_sub_permissions"
                              (-p): add/update permissions     from "z_base_cms_menus_permissions"
                              (-j): create permissions file    to   "z_base_cms_menus_permissions"
                              (--country):
                              ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $results = [];
        $country = $this->option('country');
        $truncate = $this->option('truncate');
        $paths = $this->option('create');
        $permissons = $this->option('permissions');
        $sub_permissons = $this->option('sub_permissions');
        $admin = $this->option('admin');
        $json = $this->option('json');

        if ($country) {
            config()->set('database.default', config('base-cms.default_prefix').$country);
        }

        if ($truncate) {
            $this->truncatePermissions();
        }

        if ($paths) {
            $this->createPermissions();
        }
        if ($permissons) {
            $this->savePermissions();
        }

        if ($sub_permissons) {
            $this->saveSubPermissions();
        }

        if ($admin) {
            $this->addPermissions2Roles();
        }

        if ($json) {
            $this->createPermissionsFile();
        }

        return count($results);
    }

    public function createPermissions()
    {
        $this->truncatePermissions();

        $strLocationAndFileNamePrefix = database_path('data/_.menus/p.*.json');
        $paths = glob($strLocationAndFileNamePrefix);
        $results[] = [];

        foreach ($paths as $path) {
            $jsonString = file_get_contents($path);

            $permissions = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);

            foreach ($permissions as $value) {
                $results[] = $this->createMenus($value);
            }
        }

        $r = Permission::count();

        $this->info($this->separator);
        $this->info("MENUS CREADOS: {$r}");
        $this->info($this->separator);
    }

    public function truncatePermissions()
    {
        Schema::disableForeignKeyConstraints();
        Permission::truncate();
        Schema::enableForeignKeyConstraints();

        $this->info($this->separator);
        $this->info('TRUNCATE');
        $this->info($this->separator);
    }

    public function savePermissions()
    {
        $q = database_path('data/auth/z_base_cms_menus_permissions.json');
        $qq = File::exists($q);

        if ($qq) {
            $qString = File::get($q);
            $json = json_decode($qString, true, 512, JSON_THROW_ON_ERROR);
            $result = 0;

            foreach ($json as $p) {
                $r = Permission::savePermission($p) ? 1 : 0;

                $result += $r;

                if ($r) {
                    $this->info("{$p['urlBackEnd']}");
                }
            }

            $this->info($this->separator);
            $this->info("PERMISSIONS: $result");
            $this->info($this->separator);
        } else {
            $this->error("File not found: $q");
        }
    }

    public function saveSubPermissions()
    {
        $q = database_path('data/auth/z_base_cms_menus_sub_permissions.json');
        $qq = File::exists($q);

        if ($qq) {

            Schema::disableForeignKeyConstraints();
            DB::table('auth_permission_permission')->truncate();
            Schema::enableForeignKeyConstraints();

            $qString = File::get($q);
            $json = json_decode($qString, null, 512, JSON_THROW_ON_ERROR);
            $result = 0;

            foreach ($json as $pc) {
                $r = Permission::savePermissionParentChild($pc->_urlParent, $pc->_urlChild) ? 1 : 0;
                $result += $r;
            }

            $this->info($this->separator);
            $this->info("SUB-PERMISSIONS: $result");
            $this->info($this->separator);
        } else {
            $this->error("File not found: $q");
        }
    }

    public function addPermissions2Roles()
    {
        $q = database_path('data/auth/z_base_cms_menus_roles.json');
        $qq = File::exists($q);

        if ($qq) {

            Schema::disableForeignKeyConstraints();
            DB::table('auth_permission_role')->truncate();
            Schema::enableForeignKeyConstraints();

            $qString = File::get($q);
            $json = json_decode($qString, null, 512, JSON_THROW_ON_ERROR);

            foreach ($json->data->content as $role) {

                $result = 0;
                foreach ($role->_urlBackEnd_ as $key) {
                    $r1 = Permission::savePermission2Role($key, $role->id);
                    $result += $r1;
                }

                $this->info($this->separator);
                $this->info("PERMISSIONS TO {$role->id}:{$role->name} ($result)");
                $this->info($this->separator);
            }
        } else {
            $this->error("File not found: $q");
        }
    }

    public function createPermissionsFile()
    {
        $path = database_path('data/auth/z_base_cms_menus_permissions.json');

        $permissions = Permission::select([
            'icon',
            'name',
            'urlBackEnd',
            'urlFrontEnd',
            'isSection',
            'isVisible',
            'orderInMenu',

        ])
            ->orderBy('urlBackEnd')
            ->orderBy('orderInMenu')
            ->orderByDesc('isSection')
            ->get();

        $lString = json_encode($permissions, JSON_PRETTY_PRINT);

        File::put($path, $lString);

        $this->info($this->separator);
        $this->info('PERMISSIONS FILE CREATED');
        $this->info($this->separator);
    }

    public function createMenus($request)
    {
        if (isset($request['individual'])) {
            Permission::create($request);

            return 'ok';
        }

        $id = $request['id'] ?? null;
        $isGroup = isset($request['isGroup']);
        $nameSingular = $request['name'];
        $namePlural = $request['namePlural'];
        $icon = $request['icon'];
        $namePluralBackEnd = $request['namePluralBackEnd'];
        $isSection = $request['isSection'];
        $isVisible = $request['isVisible'];
        $permission_id = $request['permission_id'];
        $orderInMenu = $request['orderInMenu'];

        $permissionIndex = new Permission();
        if ($id) {
            $permissionIndex->id = $id;
        }
        $permissionIndex->name = $namePlural;
        $permissionIndex->icon = $icon;
        $permissionIndex->urlBackEnd = 'api.'.$namePluralBackEnd.'.index';
        $permissionIndex->urlFrontEnd = '/'.$namePluralBackEnd;
        $permissionIndex->isSection = $isSection;
        $permissionIndex->isVisible = $isVisible;
        $permissionIndex->permission_id = $permission_id;
        $permissionIndex->orderInMenu = $orderInMenu;
        $permissionIndex->save();

        if ($isGroup) {
            $permissionShow = new Permission();
            $permissionShow->name = 'Mostrar '.$nameSingular;
            $permissionShow->icon = $icon;
            $permissionShow->urlBackEnd = 'api.'.$namePluralBackEnd.'.show';
            $permissionShow->urlFrontEnd = '/'.$namePluralBackEnd.'/show';
            $permissionShow->isSection = 0;
            $permissionShow->isVisible = 0;
            $permissionShow->permission_id = $permissionIndex->id;
            $permissionShow->orderInMenu = 0;
            $permissionShow->save();

            $permissionCreate = new Permission();
            $permissionCreate->name = 'Crear '.$nameSingular;
            $permissionCreate->icon = $icon;
            $permissionCreate->urlBackEnd = 'api.'.$namePluralBackEnd.'.store';
            $permissionCreate->urlFrontEnd = '/'.$namePluralBackEnd.'/new';
            $permissionCreate->isSection = 0;
            $permissionCreate->isVisible = 0;
            $permissionCreate->permission_id = $permissionIndex->id;
            $permissionCreate->orderInMenu = 1;
            $permissionCreate->save();

            $permissionUpdate = new Permission();
            $permissionUpdate->name = 'Actualizar '.$nameSingular;
            $permissionUpdate->icon = $icon;
            $permissionUpdate->urlBackEnd = 'api.'.$namePluralBackEnd.'.update';
            $permissionUpdate->urlFrontEnd = '/'.$namePluralBackEnd.'/edit';
            $permissionUpdate->isSection = 0;
            $permissionUpdate->isVisible = 0;
            $permissionUpdate->permission_id = $permissionIndex->id;
            $permissionUpdate->orderInMenu = 2;
            $permissionUpdate->save();

            $permissionDelete = new Permission();
            $permissionDelete->name = 'Borrar '.$nameSingular;
            $permissionDelete->icon = $icon;
            $permissionDelete->urlBackEnd = 'api.'.$namePluralBackEnd.'.destroy';
            $permissionDelete->urlFrontEnd = '/'.$namePluralBackEnd.'/delete';
            $permissionDelete->isSection = 0;
            $permissionDelete->isVisible = 0;
            $permissionDelete->permission_id = $permissionIndex->id;
            $permissionDelete->orderInMenu = 3;
            $permissionDelete->save();
        }

        return 'ok';
    }
}
