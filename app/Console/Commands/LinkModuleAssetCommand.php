<?php

namespace App\Console\Commands;

use Aksara\Entities\Module;
use Aksara\ModuleRegistry\ModuleRegistryHandler;
use Illuminate\Console\Command;
use Illuminate\FileSystem\FileSystem;
use Illuminate\Config\Repository as Config;

class LinkModuleAssetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aksara:storage:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate asset symlink for loaded aksara modules';

    private $fileSystem;
    private $config;
    private $moduleRegistry;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        FileSystem $fileSystem,
        Config $config,
        ModuleRegistryHandler $moduleRegistry
    ){
        parent::__construct();
        $this->fileSystem = $fileSystem;
        $this->config = $config;
        $this->moduleRegistry = $moduleRegistry;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (windows_os()) {
            $this->error("Symlink is not available in this operating system, please run aksara:storage:copy instead");
        } else{
            $this->linkModulesV1();
            $this->linkModulesV2();
        }
    }

    private function linkModulesV2()
    {
        $modules = $this->moduleRegistry->getRegisteredModules();

        $modulesAssetRoot = public_path("assets/modules-v2/");

        $this->fileSystem->deleteDirectory($modulesAssetRoot);
        $this->fileSystem->makeDirectory($modulesAssetRoot);

        foreach ($modules as $module) {
            $assetPath = $module->getModulePath()->asset();

            if (!$this->fileSystem->exists($assetPath)) {
                $this->info("$assetPath does not exist");
                continue;
            }

            $publicPath = $modulesAssetRoot.$module->getName();

            if ($this->fileSystem->exists($publicPath)) {
                $this->info("$publicPath already exists.");
                continue;
            }

            $this->fileSystem->link(
                $assetPath,
                $publicPath
            );

            $this->info("Linked $assetPath to $publicPath\n");
        }
    }

    private function linkModulesV1()
    {
        $modulesArray = $this->config->get('aksara.modules');
        $modules = Module::fromConfigArray($modulesArray);

        $modulesAssetRoot = public_path("assets/modules/");
        $this->fileSystem->deleteDirectory($modulesAssetRoot);

        foreach ($modules as $module) {
            switch ($module->getType()) {
            case Module::PLUGIN_TYPE:
                $this->linkPluginAssets($module);
                break;
            case Module::FRONTEND_TYPE:
                $this->linkFrontEndAssets($module);
                break;
            case Module::ADMIN_TYPE:
                $this->linkAdminAssets($module);
            case Module::CORE_TYPE:
                $this->linkCoreAssets($module);
            default: continue;
            }
        }
    }

    private function linkCoreAssets(Module $module)
    {
        $publicAssetRoot = public_path("assets/modules/Core/");
        $this->createSymLink($module, $publicAssetRoot);
    }

    private function linkAdminAssets(Module $module)
    {
        $publicAssetRoot = public_path("assets/modules/Admin/");
        $this->createSymLink($module, $publicAssetRoot);
    }

    private function linkFrontEndAssets(Module $module)
    {
        $publicAssetRoot = public_path("assets/modules/FrontEnd/");
        $this->createSymLink($module, $publicAssetRoot);
    }

    private function linkPluginAssets(Module $module)
    {
        $publicAssetRoot = public_path("assets/modules/Plugins/");
        $this->createSymLink($module, $publicAssetRoot);
    }

    private function createSymLink(Module $module, $publicAssetRoot)
    {
        $assetPath = $module->getAssetPath();

        if (!$this->fileSystem->exists($assetPath)) {
            $this->info("$assetPath does not exist");
            return false;
        }

        if (!$this->fileSystem->exists($publicAssetRoot)) {
            $this->fileSystem->makeDirectory($publicAssetRoot, 0755, true);
        }

        $publicPath = $publicAssetRoot . aksara_unslugify($module->getKey(), true);

        if ($this->fileSystem->exists($publicPath)) {
            $this->info("$publicPath already exists.");
            return false;
        }

        $this->fileSystem->link(
            $assetPath,
            $publicPath
        );

        $this->info("Linked $assetPath to $publicPath\n");
    }
}
