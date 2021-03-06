//<?php

use Aksara\ModuleRegistry\Interactor;
use Aksara\AdminNotif\AdminNotifRequest;
use Aksara\AdminNotif\AdminNotifHandler;
use Aksara\Application\ApplicationInterface;
use Faker\Factory as Faker;
use Illuminate\Filesystem\Filesystem;

class ModuleRegistryInteractorTest extends PHPUnit\Framework\TestCase
{
    private $faker;

    private $basePath;
    private $moduleRoot;
    private $activePath;
    private $app;

    protected function setup()
    {
        $this->faker = Faker::create();

        $this->basePath = $this->generateDir($this->faker->slug);
        $this->moduleRoot = $this->basePath."/aksara-modules";
        $this->activePath = $this->moduleRoot."/active_manifest.php";

        $this->app = $this->getMockBuilder(ApplicationInterface::class)
            ->getMock();

        $this->app->expects($this->once())
            ->method('basePath')
            ->with('aksara-modules')
            ->willReturn($this->moduleRoot);
    }

    private function generateDir($lastDir)
    {
        return $this->faker->word.'/'.$this->faker->word.'/'.$lastDir;
    }

    private function getRegisteredPluginNames()
    {
        $module_1 = $this->faker->slug;
        $module_2 = $this->faker->slug;
        $module_3 = $this->faker->slug;

        return array($module_1, $module_2, $module_3);
    }

    /** @test */
    public function shouldCheckIsRegistered()
    {
        $registered = $this->getRegisteredPluginNames();
        $directories = $this->getMockDirectories($registered);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->exactly(2))
            ->method('directories')
            ->with($this->moduleRoot)
            ->willReturn($directories);

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $this->assertTrue($interactor->isRegistered(
            $this->faker->randomElement($registered)
        ));

        $this->assertFalse($interactor->isRegistered(
            $this->faker->randomElement($registered).'_fake'
        ));

    }

    /** @test */
    public function shouldCheckIsActive()
    {
        $activePlugins = $this->getRegisteredPluginNames();
        $directories = $this->getMockDirectories($activePlugins);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->activePath)
            ->willReturn(true);

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use ($activePlugins) {
                return $activePlugins;
            });

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $this->assertTrue($interactor->isActive(
            $this->faker->randomElement($activePlugins)
        ));

        $this->assertFalse($interactor->isActive(
            $this->faker->randomElement($activePlugins).'_fake'
        ));

    }

    /** @test */
    public function shouldHandleManifestNotFound()
    {
        $directories = $this->getMockDirectories();

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->once())
            ->method('directories')
            ->with($this->moduleRoot)
            ->willReturn($directories);

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->logicalOr(
                $manifest1 = $directories[0].'/module.php',
                $manifest2 = $directories[1].'/module.php',
                $manifest3 = $directories[2].'/module.php',
                $this->activePath
            ))
            ->willReturn(false);

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $this->expectException(\Exception::class);
        $interactor->getRegisteredModules();
    }

    /** @test */
    public function shouldGetRegisteredPlugins()
    {
        $directories = $this->getMockDirectories();

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->once())
            ->method('directories')
            ->with($this->moduleRoot)
            ->willReturn($directories);

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->logicalOr(
                $manifest1 = $directories[0].'/module.php',
                $manifest2 = $directories[1].'/module.php',
                $manifest3 = $directories[2].'/module.php',
                $this->activePath
            ))
            ->willReturnCallback(function ($path) {
                if ($path == $this->activePath) {
                    return false;
                }
                return true;
            });

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use (
                $manifest1, $manifest2, $manifest3,
                &$name_1, &$name_2, &$name_3,
                &$description_1, &$description_2, &$description_3,
                &$type_1, &$type_2, &$type_3
            ) {
                switch ($manifestPath) {
                case $manifest1: return [
                        'name' => $name_1 = $this->faker->slug,
                        'description' => $description_1 = $this->faker->sentence,
                        'type' => $type_1 = 'plugin',
                    ];
                case $manifest2: return [
                        'name' => $name_2 = $this->faker->slug,
                        'description' => $description_2 = $this->faker->sentence,
                        'type' => $type_2 = 'plugin',
                    ];
                case $manifest3: return [
                        'name' => $name_3 = $this->faker->slug,
                        'description' => $description_3 = $this->faker->sentence,
                        'type' => $type_3 = 'frontend',
                    ];
                default: return null;
                }
            });

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $modules = $interactor->getRegisteredModules();

        $this->assertEquals($name_1, $modules[0]->getName());
        $this->assertEquals($description_1, $modules[0]->getDescription());
        $this->assertEquals($type_1, $modules[0]->getType());

        $this->assertEquals($name_2, $modules[1]->getName());
        $this->assertEquals($description_2, $modules[1]->getDescription());
        $this->assertEquals($type_2, $modules[1]->getType());

        $this->assertEquals($name_3, $modules[2]->getName());
        $this->assertEquals($description_3, $modules[2]->getDescription());
        $this->assertEquals($type_3, $modules[2]->getType());
    }

    /** @test */
    public function shouldGetRegisteredModulesGrouped()
    {
        $directories = $this->getMockDirectories();

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->once())
            ->method('directories')
            ->with($this->moduleRoot)
            ->willReturn($directories);

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->logicalOr(
                $manifest1 = $directories[0].'/module.php',
                $manifest2 = $directories[1].'/module.php',
                $manifest3 = $directories[2].'/module.php',
                $this->activePath
            ))
            ->willReturnCallback(function ($path) {
                if ($path == $this->activePath) {
                    return false;
                }
                return true;
            });

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use (
                $manifest1, $manifest2, $manifest3,
                &$name_1, &$name_2, &$name_3,
                &$description_1, &$description_2, &$description_3,
                &$type_1, &$type_2, &$type_3
            ) {
                switch ($manifestPath) {
                case $manifest1: return [
                        'name' => $name_1 = $this->faker->slug,
                        'description' => $description_1 = $this->faker->sentence,
                        'type' => $type_1 = 'plugin',
                    ];
                case $manifest2: return [
                        'name' => $name_2 = $this->faker->slug,
                        'description' => $description_2 = $this->faker->sentence,
                        'type' => $type_2 = 'plugin',
                    ];
                case $manifest3: return [
                        'name' => $name_3 = $this->faker->slug,
                        'description' => $description_3 = $this->faker->sentence,
                        'type' => $type_3 = 'frontend',
                    ];
                default: return null;
                }
            });

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $grouped = $interactor->getRegisteredModulesGrouped();

        $this->assertCount(2, $grouped['plugin']);
        $this->assertCount(1, $grouped['frontend']);

        $plugins = $grouped['plugin'];

        $this->assertEquals($name_1, $plugins[0]->getName());
        $this->assertEquals($description_1, $plugins[0]->getDescription());
        $this->assertEquals($type_1, $plugins[0]->getType());

        $this->assertEquals($name_2, $plugins[1]->getName());
        $this->assertEquals($description_2, $plugins[1]->getDescription());
        $this->assertEquals($type_2, $plugins[1]->getType());

        $frontends = $grouped['frontend'];

        $this->assertEquals($name_3, $frontends[0]->getName());
        $this->assertEquals($description_3, $frontends[0]->getDescription());
        $this->assertEquals($type_3, $frontends[0]->getType());

    }

    private function getMockDirectories($modules = [])
    {
        list ($module_1, $module_2, $module_3) = empty($modules) ?
            $this->getRegisteredPluginNames()
            : $modules;

        $directories = [
            $moduleDir1 = $this->moduleRoot."/$module_1",
            $moduleDir2 = $this->moduleRoot."/$module_2",
            $moduleDir3 = $this->moduleRoot."/$module_3",
        ];
        return $directories;
    }

    /** @test */
    public function shouldGetActivePlugins()
    {
        $activePlugins = $this->getRegisteredPluginNames();
        $directories = $this->getMockDirectories($activePlugins);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->logicalOr(
                $manifest1 = $directories[0].'/module.php',
                $manifest2 = $directories[1].'/module.php',
                $manifest3 = $directories[2].'/module.php',
                $this->activePath
            ))
            ->willReturn(true);

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use (
                $manifest1, $manifest2, $manifest3, $activePlugins) {
                switch ($manifestPath) {
                case $manifest1: return [
                        'name' => $name_1 = $this->faker->slug,
                        'description' => $description_1 = $this->faker->sentence,
                    ];
                case $manifest2: return [
                        'name' => $name_2 = $this->faker->slug,
                        'description' => $description_2 = $this->faker->sentence,
                    ];
                case $manifest3: return [
                        'name' => $name_3 = $this->faker->slug,
                        'description' => $description_3 = $this->faker->sentence,
                    ];
                case $this->activePath: return $activePlugins;
                default: return null;
                }
            });

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $interactor->getActiveModules();
    }

    /** @test */
    public function shouldActivatePlugin()
    {
        $registeredPlugins = $this->getRegisteredPluginNames();
        $directories = $this->getMockDirectories($registeredPlugins);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->logicalOr(
                $manifest1 = $directories[0].'/module.php',
                $manifest2 = $directories[1].'/module.php',
                $manifest3 = $directories[2].'/module.php',
                $this->activePath
            ))
            ->willReturn(true);

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use (
                $manifest1, $manifest2, $manifest3, $registeredPlugins) {
                switch ($manifestPath) {
                case $manifest1: return [
                        'name' => $name_1 = $this->faker->slug,
                        'description' => $description_1 = $this->faker->sentence,
                    ];
                case $manifest2: return [
                        'name' => $name_2 = $this->faker->slug,
                        'description' => $description_2 = $this->faker->sentence,
                    ];
                case $manifest3: return [
                        'name' => $name_3 = $this->faker->slug,
                        'description' => $description_3 = $this->faker->sentence,
                    ];
                case $this->activePath: return $registeredPlugins;
                default: return null;
                }
            });

        $filesystem->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);

        $activated = $this->faker->slug;

        $manifestDump = var_export(array_merge($registeredPlugins, [ $activated ]), true);

        $filesystem->expects($this->once())
            ->method('put')
            ->with($this->activePath, '<?php return '.$manifestDump.';');

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $notifHandler->expects($this->once())
            ->method('handle');

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $interactor->activateModule($activated);

    }

    /** @test */
    public function shouldDeactivateModule()
    {
        $activePlugins = $this->getRegisteredPluginNames();
        $directories = $this->getMockDirectories($activePlugins);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->activePath)
            ->willReturn(true);

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use ($activePlugins) {
                return $activePlugins;
            });

        $filesystem->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);

        $deactivated = $this->faker->randomElement($activePlugins);

        $manifestDump = var_export(
                array_diff($activePlugins, [ $deactivated ]), true);

        $filesystem->expects($this->once())
            ->method('put')
            ->with($this->activePath, '<?php return '.$manifestDump.';');

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $notifHandler->expects($this->once())
            ->method('handle');

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $interactor->deactivateModule($deactivated);
    }

    /** @test */
    public function shouldHandleDirectoryAccessDenied()
    {
        $activePlugins = $this->getRegisteredPluginNames();
        $directories = $this->getMockDirectories($activePlugins);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->any())
            ->method('exists')
            ->with($this->activePath)
            ->willReturn(true);

        $filesystem->expects($this->any())
            ->method('getRequire')
            ->willReturnCallback(function ($manifestPath) use ($activePlugins) {
                return $activePlugins;
            });

        $filesystem->expects($this->any())
            ->method('isWritable')
            ->willReturn(false);

        $deactivated = $this->faker->randomElement($activePlugins);

        $notifHandler = $this->getMockBuilder(AdminNotifHandler::class)
            ->getMock();

        $interactor = new Interactor($this->app, $filesystem, $notifHandler);

        $this->expectException(\Exception::class);
        $interactor->deactivateModule($deactivated);
    }
}
