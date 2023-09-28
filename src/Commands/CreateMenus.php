<?php

namespace Juanfv2\BaseCms\Commands;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CreateMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base-cms:menus {paths*} {--t|truncate} {--a|admin} {--p|parents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(-t): truncate permissions table, (-a): add all permissions to role admin';

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
        $paths = $this->argument('paths');
        $truncate = $this->option('truncate');
        $admin = $this->option('admin');
        $sub_permissons = $this->option('parents');

        if ($truncate) {
            Schema::disableForeignKeyConstraints();
            DB::table('auth_permissions')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        // var_dump($paths);
        // Read File
        $results[] = [];

        foreach ($paths as $path) {
            $jsonString = file_get_contents(base_path($path));

            $permissions = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);

            // echo $path;

            foreach ($permissions as $value) {
                $results[] = $this->createMenus($value);
            }
        }

        $r = Permission::count();

        if ($admin) {
            $role = Role::find(1);
            $permissions = [];
            for ($i = 1; $i <= $r; $i++) {
                $permissions[] = $i;
            }
            $role->permissions()->sync($permissions);
        }

        if ($sub_permissons) {
            Schema::disableForeignKeyConstraints();
            DB::table('auth_permission_permission')->truncate();
            Schema::enableForeignKeyConstraints();

            $q = database_path('data/auth/auth_permission_permission_names.json');
            $qq = File::exists($q);

            if ($qq) {
                $qString = File::get($q);
                $json = json_decode($qString, null, 512, JSON_THROW_ON_ERROR);

                foreach ($json as $pc) {
                    $result = Permission::savePermissionParentChild($pc->_urlParent, $pc->_urlChild);
                }
            } else {
                $this->error("File not found: $q");
            }
        }

        $this->info("Menus creados: {$r}");

        return count($results);
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
